<?php

namespace App\Http\Controllers\Api;

use App\Repositories\{StripeRepository, CoinPaymentRepository};
use App\Http\Controllers\Controller;
use DB, Validator, Exception;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{PaymentMethod,
    CurrencyPaymentMethod,
    Transaction,
    FeesLimit,
    Currency,
    Setting,
    Deposit,
    Wallet,
    Bank,
    File,
    User
};
use App\Services\Mail\Deposit\NotifyAdminOnDepositMailService;

class DepositMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    protected $helper;
    protected $stripeRepository;

    public function __construct()
    {
        $this->helper = new Common();
        $this->stripeRepository = new StripeRepository();
    }

    //Deposit Money Starts here
    public function getDepositCurrencyList()
    {
        $activeCurrency = Currency::where(['status' => 'Active'])->get(['id', 'code', 'type', 'status']);
        $feesLimitCurrency = FeesLimit::where(['transaction_type_id' => Deposit, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);

        //Set default wallet as selected - starts
        $defaultWallet                      = Wallet::where(['user_id' => request('user_id'), 'is_default' => 'Yes'])->first(['currency_id']);
        $success['defaultWalletCurrencyId'] = $defaultWallet->currency_id;
        //Set default wallet as selected - ends

        $success['currencies']              = $this->currencyList($activeCurrency, $feesLimitCurrency);
        $success['status']                  = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Extended function - 1
    public function currencyList($activeCurrency, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($activeCurrency as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $aCurrency->status == 'Active' && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$aCurrency->id]['id']   = $aCurrency->id;
                    $selectedCurrency[$aCurrency->id]['code'] = $aCurrency->code;
                    $selectedCurrency[$aCurrency->id]['type'] = $aCurrency->type;
                }
            }
        }
        return $selectedCurrency;
    }

    //getMatchedFeesLimitsCurrencyPaymentMethodsSettingsPaymentMethods
    public function getDepositMatchedFeesLimitsCurrencyPaymentMethodsSettingsPaymentMethods(Request $request)
    {
        $condition = ($request->currencyType == 'fiat') ? getPaymoneySettings('payment_methods')['mobile']['fiat']['deposit'] : getPaymoneySettings('payment_methods')['mobile']['crypto']['deposit'];

        $feesLimits = FeesLimit::whereHas('currency', function($q)
        {
            $q->where('status','=','Active');
        })
        ->whereHas('payment_method', function($q) use ($condition)
        {
            $q->whereIn('id', $condition)->where('status','=','Active');
        })
        ->where(['transaction_type_id' => $request->transaction_type_id, 'has_transaction' => 'Yes', 'currency_id' => $request->currency_id])
        ->get(['payment_method_id']);

        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('activated_for', 'like', "%deposit%")->get(['method_id']);
        $currencyPaymentMethodFeesLimitCurrenciesList = $this->currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods);
        $success['paymentMethods'] = $currencyPaymentMethodFeesLimitCurrenciesList;
        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Extended function - 2
    public function currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods)
    {
        $selectedCurrencies = [];
        foreach ($feesLimits as $feesLimit)
        {
            foreach ($currencyPaymentMethods as $currencyPaymentMethod)
            {
                if ($feesLimit->payment_method_id == $currencyPaymentMethod->method_id)
                {
                    $selectedCurrencies[$feesLimit->payment_method_id]['id']   = $feesLimit->payment_method_id;
                    $selectedCurrencies[$feesLimit->payment_method_id]['name'] = $feesLimit->payment_method->name;
                }
            }
        }
        return $selectedCurrencies;
    }

    public function getDepositDetailsWithAmountLimitCheck()
    {
        $user_id = (int) request('user_id');
        $amount = (double) request('amount');
        $currency_id = request('currency_id');
        $paymentMethodId = (int) request('paymentMethodId');
        $success['paymentMethodName'] = PaymentMethod::where('id', $paymentMethodId)->first(['name'])->name;
        $wallets = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['balance']);

        $feesDetails = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currency_id, 'payment_method_id' => $paymentMethodId])
            ->first(['charge_percentage', 'charge_fixed', 'min_limit', 'max_limit', 'currency_id']);

        if (@$feesDetails->max_limit == null) {
            $success['status'] = 200;
            if ((@$amount < @$feesDetails->min_limit)) {
                $success['reason']   = 'minLimit';
                $success['minLimit'] = @$feesDetails->min_limit;
                $success['message']  = __('Minimum amount :x', ['x' => formatNumber(@$feesDetails->min_limit)]);
                $success['status']   = '401';
                return response()->json(['success' => $success]);
            }
        } else {
            $success['status'] = 200;
            if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit)) {
                $success['reason']   = 'minMaxLimit';
                $success['minLimit'] = @$feesDetails->min_limit;
                $success['maxLimit'] = @$feesDetails->max_limit;
                $success['message']  = __('Minimum amount :x and Maximum amount :y', ['x' => formatNumber(@$feesDetails->min_limit),  'y' => formatNumber(@$feesDetails->max_limit)]);
                $success['status']   = '401';
                return response()->json(['success' => $success]);
            }
        }
        //Code for Amount Limit ends here

        //Code for Fees Limit Starts here
        if (empty($feesDetails)) {
            $success['message'] = "ERROR";
            $success['status']  = 401;
        } else {
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['amount']         = $amount;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['currency_id']    = $feesDetails->currency_id;
            $success['currSymbol']     = $feesDetails->currency->symbol;
            $success['currCode']       = $feesDetails->currency->code;
            $success['currType']       = $feesDetails->currency->type;
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = @$wallets->balance ? @$wallets->balance : 0;
            $success['status']         = 200;
        }
        return response()->json(['success' => $success]);
    }

    public function stripeMakePayment(Request $request)
    {
        $data = [];
        $data['status']  = 200;
        $data['message'] = "Success";
        $validation = Validator::make($request->all(), [
            'cardNumber'  => 'required',
            'month'       => 'required|digits_between:1,12|numeric',
            'year'        => 'required|numeric',
            'cvc'         => 'required|numeric',
            'amount'      => 'required|numeric',
            'totalAmount' => 'required|numeric',
            'currency_id' => 'required',
            'payment_method_id' => 'required',
        ]);
        if ($validation->fails()) {
            $data['message'] = $validation->errors()->first();
            $data['status']  = 401;
            return response()->json(['success' => $data]);
        }
        $sessionValue['totalAmount'] = (double) request('totalAmount');
        $sessionValue['amount']      = (double) request('amount');
        $amount            = (double) $sessionValue['totalAmount'];
        $payment_method_id = $method_id = (int) request('payment_method_id');
        $currencyId        = (int) request('currency_id');
        $currency          = Currency::find($currencyId, ["id", "code"]);
        $currencyPaymentMethod     = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
        $methodData        = json_decode($currencyPaymentMethod->method_data);
        $secretKey         = $methodData->secret_key;
        if (!isset($secretKey)) {
            $data['message']  = __("Payment gateway credentials not found!");
            $data['status']  = 401;
            return response()->json(['success' => $data]);
        }
        $response = $this->stripeRepository->makePayment($secretKey, round($amount, 2), strtolower($currency->code), $request->cardNumber, $request->month, $request->year, $request->cvc);
        if ($response->getData()->status != 200) {
            $data['status']  = $response->getData()->status;
            $data['message'] = $response->getData()->message;
        } else {
            $data['paymentIntendId'] = $response->getData()->paymentIntendId;
            $data['paymentMethodId'] = $response->getData()->paymentMethodId;
        }
        return response()->json(['success' => $data]);
    }

    public function stripeConfirm(Request $request)
    {
        $data = [];
        $data['status']  = 401;
        $data['message'] = "Fail";
        try {
            DB::beginTransaction();
            $validation = Validator::make($request->all(), [
                'paymentIntendId'   => 'required',
                'paymentMethodId'   => 'required',
                'amount'            => 'required',
                'totalAmount'       => 'required',
                'currency_id'       => 'required',
                'payment_method_id' => 'required',
            ]);
            if ($validation->fails()) {
                $data['message'] = $validation->errors()->first();
                return response()->json(['success' => $data]);
            }
            $sessionValue['totalAmount'] = (double) request('totalAmount');
            $sessionValue['amount']      = (double) request('amount');
            $amount            = (double) $sessionValue['totalAmount'];
            $payment_method_id = $method_id = (int) request('payment_method_id');
            $currencyId        = (int) request('currency_id');
            $currency          = Currency::find($currencyId, ["id", "code"]);
            $currencyPaymentMethod     = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
            $methodData        = json_decode($currencyPaymentMethod->method_data);
            $secretKey         = $methodData->secret_key;
            if (!isset($secretKey)) {
                $data['message']  = __("Payment gateway credentials not found!");
                return response()->json([
                    'data' => $data
                ]);
            }
            $response = $this->stripeRepository->paymentConfirm($secretKey, $request->paymentIntendId, $request->paymentMethodId);
            if ($response->getData()->status != 200) {
                $data['message'] = $response->getData()->message;
                return response()->json([
                    'data' => $data
                ]);
            }
            $user_id           = request('user_id');
            $wallet            = Wallet::where(['currency_id' => $currencyId, 'user_id' => $user_id])->first(['id', 'currency_id']);
            if (empty($wallet)) {
                $walletInstance = Wallet::createWallet($user_id, $sessionValue['currency_id']);
            }
            $currencyId = isset($wallet->currency_id) ? $wallet->currency_id : $walletInstance->currency_id;
            $currency   = Currency::find($currencyId, ['id', 'code']);

            $depositConfirm      = Deposit::success($currencyId, $payment_method_id, $user_id, $sessionValue);
            DB::commit();
            (new NotifyAdminOnDepositMailService)->send($depositConfirm['deposit'], ['type' => 'deposit', 'medium' => 'email']);
            $data['status']      = 200;
            $data['message']     = "Success";
            return response()->json(['success' => $data]);
        } catch (Exception $e) {
            DB::rollBack();
            $data['message'] =  $e->getMessage();
            return response()->json(['success' => $data]);
        }
    }
    /**
     * Stripe Ends
     * @return [type] [description]
     */

    /**
     * Paypal Starts
     * @return [type] [description]
     */
    //Get Paypal Info
    public function getPeypalInfo()
    {
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => request('currency_id'), 'method_id' => request('method_id')])
            ->where('activated_for', 'like', "%deposit%")
            ->first(['method_data']);

        if (empty($currencyPaymentMethod))
        {
            $success['message'] = __('Payment gateway credentials not found!');
            $success['status']  = 401;
        }
        else
        {
            $success['method_info'] = json_decode($currencyPaymentMethod->method_data);
            $success['status']      = 200;
            return response()->json(['success' => $success]);
        }
    }

    public function paypalSetup()
    {
        $numarr = func_num_args();
        if ($numarr > 0)
        {
            $clientID   = func_get_arg(0);
            $secret     = func_get_arg(1);
            $mode       = func_get_arg(2);
            $apicontext = new ApiContext(new OAuthTokenCredential($clientID, $secret));
            $apicontext->setConfig([
                'mode' => $mode,
            ]);
        }
        else
        {
            $credentials = Setting::where(['type' => 'PayPal'])->get();
            $clientID    = $credentials[0]->value;
            $secret      = $credentials[1]->value;
            $apicontext  = new ApiContext(new OAuthTokenCredential($clientID, $secret));
            $apicontext->setConfig([
                'mode' => $credentials[3]->value,
            ]);
        }

        return $apicontext;
    }

    //Deposit Confirm Post via Paypal
    public function paypalPaymentStore()
    {
        try {
            DB::beginTransaction();
            if (request('details')['status'] != "COMPLETED") {
                $success['status']  = 401;
                $success['message'] = __('Unsuccessful Transaction');
                return response()->json(['success' => $success]);
            }
            $amount            = (double) request('amount');
            $currency_id       = (int) request('currency_id');
            $payment_method_id = (int) request('paymentMethodId');
            $user_id           = (int) request('user_id');
            $uuid              = unique_code();
            $wallet            = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['id', 'balance']);
            if (empty($wallet)) {
                $walletInstance = Wallet::createWallet($user_id, $currency_id);
            }
            $calculatedFee = $this->getDepositDetailsWithAmountLimitCheck();
            $sessionValue['amount']      = $amount;
            $sessionValue['totalAmount'] = $amount + $calculatedFee->getData()->success->totalFees;
            $depositConfirm              = Deposit::success($currency_id, $payment_method_id, $user_id, $sessionValue);
            DB::commit();

            (new NotifyAdminOnDepositMailService)->send($depositConfirm["deposit"], ['type' => 'deposit', 'medium' => 'email']);

            $success['transaction'] = $depositConfirm['transaction'];
            $success['status']      = 200;
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    /**
     * Paypal Ends
     * @return [type] [description]
     */

    /**
     * Bank Starts
     * @return [type] [description]
     */
    public function getDepositBankList()
    {
        $banks                  = Bank::where(['currency_id' => request('currency_id')])->get(['id', 'bank_name', 'is_default', 'account_name', 'account_number']);
        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', request('currency_id'))
            ->where('activated_for', 'like', "%deposit%")
            ->where('method_data', 'like', "%bank_id%")
            ->get(['method_data']);

        $bankList = $this->bankList($banks, $currencyPaymentMethods);
        if (empty($bankList))
        {
            $success['status']  = 401;
            $success['message'] = __('Banks Does Not Exist For Selected Currency!');
        }
        else
        {
            $success['status'] = $this->successStatus;
            $success['banks']  = $bankList;
        }
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function bankList($banks, $currencyPaymentMethods)
    {
        $selectedBanks = [];
        $i             = 0;
        foreach ($banks as $bank)
        {
            foreach ($currencyPaymentMethods as $cpm)
            {
                if ($bank->id == json_decode($cpm->method_data)->bank_id)
                {
                    $selectedBanks[$i]['id']             = $bank->id;
                    $selectedBanks[$i]['bank_name']      = $bank->bank_name;
                    $selectedBanks[$i]['is_default']     = $bank->is_default;
                    $selectedBanks[$i]['account_name']   = $bank->account_name;
                    $selectedBanks[$i]['account_number'] = $bank->account_number;
                    $i++;
                }
            }
        }
        return $selectedBanks;
    }

    public function getBankDetails()
    {
        $bank = Bank::with('file:id,filename')->where(['id' => request('bank')])->first(['account_name', 'account_number', 'bank_name', 'file_id']);
        if ($bank)
        {
            $success['status'] = 200;
            $success['bank']   = $bank;
            if (!empty($bank->file_id))
            {
                $success['bank_logo'] = $bank->file?->filename;
            }
        }
        else
        {
            $success['status'] = 401;
            $success['bank']   = __("Bank Not Found!");
        }
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Deposit Confirm Post via Bank
    public function bankPaymentStore()
    {
        try {
            DB::beginTransaction();
            $uid                  = (int)request('user_id');
            $uuid                 = unique_code();
            $deposit_payment_id   = (int) request('deposit_payment_id');
            $deposit_payment_name = request('deposit_payment_name');
            $currency_id          = (int) request('currency_id');
            $amount               = $sessionValue['amount'] = (double) request('amount');
            $bank_id              = (int) request('bank_id');
            $totalAmount          = $sessionValue['totalAmount'] = (double) request('amount') + (double) request('totalFees');
            $feeInfo              = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currency_id, 'payment_method_id' => $deposit_payment_id])->first(['charge_percentage', 'charge_fixed']);
            $feePercentage        = $amount * ($feeInfo->charge_percentage / 100);
            if ($deposit_payment_name == 'Bank') {
                if (request()->hasFile('file')) {
                    $fileName     = request()->file('file');
                    $originalName = $fileName->getClientOriginalName();
                    $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                    $file_extn    = strtolower($fileName->getClientOriginalExtension());
                    $path         = 'uploads/files/bank_attached_files';
                    $uploadPath   = public_path($path);
                    $fileName->move($uploadPath, $uniqueName);

                    $file               = new File();
                    $file->user_id      = $uid;
                    $file->filename     = $uniqueName;
                    $file->originalname = $originalName;
                    $file->type         = $file_extn;
                    $file->save();
                }
            }
            $depositConfirm = Deposit::success($currency_id, $deposit_payment_id, $uid, $sessionValue, "Pending", "bank", $file->id, $bank_id);
            DB::commit();

            (new NotifyAdminOnDepositMailService)->send($depositConfirm["deposit"], ['type' => 'deposit', 'medium' => 'email']);

            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        } catch (Exception $e) {
            DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }
    /**
     * Bank Ends
     * @return [type] [description]
     */

    //Deposit Confirm Post via Coinpayments
    public function coinpaymentsPaymentStore()
    {
        // Request value
        $userId       = (int)request('user_id');
        $user         = User::find($userId, ['id', 'first_name', 'last_name', 'email']);
        $currencyId   = (int) request('currency_id');
        $currencyCode = Currency::where('id', $currencyId)->value('code');
        $amount       = (double) request('amount');
        $currencyType = request('currency_type');
        $totalAmount  = (double) request('amount') + (double) request('totalFees');
        $uuid         = unique_code();
        $depositPaymentMethodId = (int) request('deposit_payment_id');

        // Fees Details
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $depositPaymentMethodId])->first(['charge_percentage', 'charge_fixed']);
        $feePercentage = $amount * ($feeInfo->charge_percentage / 100);

        // Currency payment Method (Coinpayments)
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $depositPaymentMethodId])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
        $methodData = json_decode($currencyPaymentMethod->method_data);

        // Coinpayment Deposit start
        $coinPayment = new CoinPaymentRepository();
        $coinPayment->Setup($methodData->private_key, $methodData->public_key);

        $rates                 = $coinPayment->GetRates(0)['result'];
        $rateofFiatCurrency    = $rates[$currencyCode]['rate_btc'];
        $rateAmount            = $rateofFiatCurrency * $totalAmount;
        $formattedCurrencyList = getFormatedCurrencyList($rates, $rateAmount);

        if ($currencyType == 'crypto') {

            $acceptedCoin = $formattedCurrencyList['coins_accept'];
            $acceptedCoinIso = array_column( $acceptedCoin, 'iso');

            if (!empty($currencyCode) && in_array($currencyCode, $acceptedCoinIso)) {

                $transactionData = [
                    'amount' => $totalAmount,
                    'currency1' => $currencyCode,
                    'currency2' => $currencyCode,
                    'buyer_email' => $user->email,
                    'buyer_name' => getColumnValue($user),
                    'item_name' => __('Deposit via coinpayment with mobile app'),
                    'custom' => $uuid,
                    'ipn_url' => url("coinpayment/check"),
                ];

                try {
                    $makeTransaction =  $coinPayment->CreateTransaction($transactionData);
                    if ($makeTransaction['error'] !== 'ok') {
                        $success['status'] = $this->unauthorisedStatus;
                        $success['reason'] = 'transaction-fail';
                        $success['message'] = __('Coinpayment transaction fail.');
                        return response()->json(['success' => $success], $this->unauthorisedStatus);
                    }
                } catch (Exception $e) {
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = $e->getMessage();
                    return response()->json(['success' => $success], $this->unauthorisedStatus);
                }

                if (!empty($user)) {

                    $payload = ['type' => 'deposit', 'currency' => $currencyCode];
                    $makeTransaction['payload'] = $payload;
                    $transactionInfo = $coinPayment->getTransactionInfo(['txid' => $makeTransaction['result']['txn_id']]);

                    if ($transactionInfo['error'] === 'ok') {

                        $transactionInfoData = $transactionInfo['result'];

                        $coinpaymentLogTrxes = [
                            'payment_id'         => $makeTransaction['result']['txn_id'],
                            'payment_address'    => $transactionInfoData['payment_address'],
                            'coin'               => $transactionInfoData['coin'],
                            'fiat'               => $payload['currency'],
                            'status_text'        => $transactionInfoData['status_text'],
                            'status'             => $transactionInfoData['status'],
                            'payment_created_at' => date('Y-m-d H:i:s', $transactionInfoData['time_created']),
                            'expired'            => date('Y-m-d H:i:s', $transactionInfoData['time_expires']),
                            'amount'             => $transactionInfoData['amountf'],
                            'confirms_needed'    => empty($makeTransaction['result']['confirms_needed']) ? 0 : $makeTransaction['result']['confirms_needed'],
                            'qrcode_url'         => empty($makeTransaction['result']['qrcode_url']) ? '' : $makeTransaction['result']['qrcode_url'],
                            'status_url'         => empty($makeTransaction['result']['status_url']) ? '' : $makeTransaction['result']['status_url'],
                        ];

                        if (isset($makeTransaction['payload']['type']) && $makeTransaction['payload']['type'] == "deposit") {

                            try {
                                DB::beginTransaction();

                                // Deposit
                                $deposit                    = new Deposit();
                                $deposit->uuid              = $uuid;
                                $deposit->charge_percentage = @$feeInfo->charge_percentage ? $feePercentage : 0;
                                $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                                $deposit->amount            = $amount;
                                $deposit->status            = 'Pending';
                                $deposit->user_id           = $user->id;
                                $deposit->currency_id       = $currencyId;
                                $deposit->payment_method_id = $depositPaymentMethodId;
                                $deposit->save();

                                // Transaction
                                $transaction                           = new Transaction();
                                $transaction->user_id                  = $user->id;
                                $transaction->currency_id              = $currencyId;
                                $transaction->payment_method_id        = $depositPaymentMethodId;
                                $transaction->uuid                     = $uuid;
                                $transaction->transaction_reference_id = $deposit->id;
                                $transaction->transaction_type_id      = Deposit;
                                $transaction->subtotal                 = $amount;
                                $transaction->percentage               = @$feeInfo->charge_percentage;
                                $transaction->charge_percentage        = $deposit->charge_percentage;
                                $transaction->charge_fixed             = $deposit->charge_fixed;
                                $transaction->total                    = $amount + $deposit->charge_percentage + $deposit->charge_fixed;
                                $transaction->status                   = 'Pending';
                                $transaction->save();

                                // Wallet creation if request currency wallet does not exist
                                $wallet = Wallet::where(['user_id' => $user->id, 'currency_id' => $currencyId])->first(['id']);

                                if (empty($wallet)) {
                                    $wallet              = new Wallet();
                                    $wallet->user_id     = $user->id;
                                    $wallet->currency_id = $currencyId;
                                    $wallet->balance     = 0;
                                    $wallet->is_default  = 'No';
                                    $wallet->save();
                                }

                                $payload['deposit_id']     = $deposit->id;
                                $payload['transaction_id'] = $transaction->id;
                                $payload['uuid']           = $uuid;
                                $payload['receivedf']      = $transactionInfoData['receivedf'];
                                $payload['time_expires']   = $transactionInfoData['time_expires'];
                                $payload                   = json_encode($payload);

                                $coinpaymentLogTrxes['payload'] = $payload;
                                $user->coinpayment_transactions()->create($coinpaymentLogTrxes);
                                DB::commit();

                                // Mail To Admin
                                (new NotifyAdminOnDepositMailService)->send($deposit, ['type' => 'deposit', 'medium' => 'email']);

                                $success['qrcode_url'] = $makeTransaction['result']['qrcode_url'];
                                $success['payment_address'] = $transactionInfo['result']['payment_address'];
                                $success['time_created'] = $transactionInfo['result']['time_created'];
                                $success['time_expires'] = $transactionInfo['result']['time_expires'];
                                $success['status'] = $this->successStatus;

                                return response()->json(['success' => $success], $this->successStatus);

                            } catch (Exception $e) {
                                $success['status']  = $this->unauthorisedStatus;
                                $success['message'] = $e->getMessage();
                                return response()->json(['success' => $success], $this->unauthorisedStatus);
                            }
                        }
                    } else {
                        $success['status']  = $this->unauthorisedStatus;
                        $success['reason'] = 'transaction-id';
                        $success['message'] = __('Coinpayment transaction ID not found.');
                        return response()->json(['success' => $success], $this->unauthorisedStatus);
                    }
                }

            } else {
                $success['status']  = $this->unauthorisedStatus;
                $success['reason'] = 'currency-code';
                $success['message'] = __('Currency code is not listed.');
                return response()->json(['success' => $success], $this->unauthorisedStatus);
            }
        }
    }
}
