<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use Validator, Session;
use App\Models\{Wallet,
    CurrencyExchange,
    FeesLimit,
    Currency,
    Transaction
};

class ExchangeController extends Controller
{
    protected $helper;
    protected $exchange;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->exchange = new CurrencyExchange();
    }

    public function exchange()
    {
        setActionSession();

        if (strcmp(route('user.exchange_money.store'), url()->previous())) {
            if (!empty(session('transInfo'))) {
                session()->forget('transInfo');
            }
        }

        $data['menu']          = 'exchange';
        $data['content_title'] = 'Exchange';
        $data['icon']          = 'money';

        $feesLimitCurrency = FeesLimit::with('currency:id')->where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        
        // Users Active, Has Transaction and Existing Currency Wallets/list
        $userCurrencyList = array_column(Wallet::with('currency')->where(['user_id' => auth()->user()->id])->get(['currency_id'])->toArray(), 'currency_id');
        $data['userCurrencyList'] = $userCurrencyList = Currency::whereIn('id', $userCurrencyList)->where(['status' => 'Active', 'type' => 'fiat'])->get(['id', 'code', 'type', 'status']);
        $data['activeHasTransactionUserCurrencyList'] = $this->activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency);

        $data['defaultWallet'] = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        return view('user.exchange-currency.create', $data);
    }

    public function amountLimitCheck(Request $request)
    {
        $amount      = $request->amount;
        $currency_id = $request->currency_id;
        $user_id     = auth()->user()->id;
        $wallet      = Wallet::with('currency:id,code')->where(['currency_id' => $request->currency_id, 'user_id' => $user_id])->first(['currency_id', 'balance']);
        $feesDetails = FeesLimit::with('currency:id,code')->where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $currency_id])
            ->first(['max_limit', 'min_limit', 'has_transaction', 'currency_id', 'charge_percentage', 'charge_fixed']);

        // Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['message']         = __('Minimum amount ') . formatNumber($feesDetails->min_limit);
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        else
        {
            if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
            {
                $success['message']         = __('Minimum amount ') . formatNumber($feesDetails->min_limit) . __(' and Maximum amount ') . formatNumber($feesDetails->max_limit);
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        //Code for Amount Limit ends here

        //Code for Fees Limit Starts here
        if (empty($feesDetails))
        {
            $curr               = Currency::find($request->currency_id);
            $success['message'] = __('Please check fees limit for the currency ') . $curr->code;
            $success['status']  = '401';
        }
        else
        {
            if ($feesDetails->has_transaction == 'No')
            {
                $success['message'] = __('The currency') . ' ' . $feesDetails->currency->code . ' ' . __('fees limit is inactive');
                $success['status']  = '401';
            }
            else
            {
                $feesPercentage             = $amount * ($feesDetails->charge_percentage / 100);
                $feesFixed                  = $feesDetails->charge_fixed;
                $totalFess                  = $feesPercentage + $feesFixed;
                $totalAmount                = $amount + $totalFess;
                $success['feesPercentage']  = $feesPercentage;
                $success['feesFixed']       = $feesFixed;
                $success['totalFees']       = $totalFess;
                $success['totalAmount']     = $totalAmount;
                $success['balance']         = @$wallet->balance ? (@$wallet->balance) : 0;
                $success['wallet_currency'] = $wallet->currency->code;
                $success['totalFeesHtml']   = formatNumber($totalFess);
                $success['pFeesHtml']       = formatNumber($feesDetails->charge_percentage);
                $success['fFeesHtml']       = formatNumber($feesDetails->charge_fixed);
            }
        }
        //Code for Fees Limit Ends here

        return response()->json([
            'success' => $success,
        ]);
    }

    public function getActiveHasTransactionExceptUsersExistingWalletsCurrencies(Request $request)
    {
        $feesLimitCurrency = FeesLimit::where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $activeCurrency = Currency::where('id', '!=', $request->currency_id)->where(['type' => 'fiat', 'status' => 'Active'])->get(['id', 'code', 'status', 'rate', 'exchange_from']);
        $currencyList = $this->currencyList($activeCurrency, $feesLimitCurrency);

        if ($currencyList)
        {
            return response()->json([
                'currencies' => $currencyList,
                'status'     => true,
            ]);
        }
        else
        {
            return response()->json([
                'currencies' => null,
                'status'     => false,
            ]);
        }
    }

    public function getCurrenciesExchangeRate(Request $request)
    {
        $toWalletCurrency = $this->helper->getCurrencyObject(['id' => $request->toWallet], ['id', 'exchange_from', 'code', 'rate', 'symbol']);
        
        if (!empty($toWalletCurrency)) {
            if ($toWalletCurrency->exchange_from == "api" && isEnabledExchangeApi()){
                $destinationCurrencyRate = getApiCurrencyRate($request->fromWalletCode, $toWalletCurrency->code);
                if ($destinationCurrencyRate == 'error') {
                    $fromWalletCurrency = $this->helper->getCurrencyObject(['id' => $request->fromWallet], ['rate']);
                    $destinationCurrencyRate = getLocalCurrencyRate($fromWalletCurrency->rate, $toWalletCurrency->rate);
                }
            } else {
                $fromWalletCurrency      = $this->helper->getCurrencyObject(['id' => $request->fromWallet], ['rate']);
                $destinationCurrencyRate = getLocalCurrencyRate($fromWalletCurrency->rate, $toWalletCurrency->rate);
            }
            $getAmountMoneyFormat             = $destinationCurrencyRate * $request->amount;
            $formattedDestinationCurrencyRate = number_format($destinationCurrencyRate, 8, '.', '');
            return response()->json([
                'status'                   => true,
                'destinationCurrencyRate'  => (float) $formattedDestinationCurrencyRate, // this will not be shown as formatted as it creates confusion - when multiplying amount * currency rate
                'destinationCurrencyCode'  => $toWalletCurrency->code,
                'getAmountMoneyFormatHtml' => moneyFormat($toWalletCurrency->code, formatNumber($getAmountMoneyFormat, $toWalletCurrency->id)), //just for show, not taken for further processing
            ]);
        } else {
            return response()->json([
                'status'                      => false,
                'destinationCurrencyRate'     => null,
                'destinationCurrencyRateHtml' => null,
                'destinationCurrencyCode'     => null,
                'getAmountMoneyFormat'        => null,
            ]);
        }
    }

    public function getBalanceOfToWallet(Request $request)
    {
        $wallet = Wallet::with('currency:id,code')->where(['currency_id' => $request->currency_id, 'user_id' => auth()->user()->id])->first(['balance', 'currency_id']); //added by parvez - for wallet balance check
        if (!empty($wallet))
        {
            return response()->json([
                'status'       => true,
                'balance'      => formatNumber($wallet->balance),
                'currencyCode' => $wallet->currency->code,
            ]);
        }
        else
        {
            return response()->json([
                'status'       => false,
                'balance'      => null,
                'currencyCode' => null,
            ]);
        }
    }

    public function exchangeOfCurrency(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'amount' => 'required|numeric', 
                'currency_id' => 'required', 
                'from_currency_id' => 'required'
            ]
            ;
            $fieldNames = [
                'amount' => 'Amount', 
                'currency_id' => 'To wallet Currency', 
                'from_currency_id' => 'From wallet currency'
            ];
            $validator  = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            //backend validation starts
            $from_currency_id = $request->from_currency_id;
            $to_currency_id   = $request->currency_id;

            //temporary swapping
            $request['currency_id']         = $from_currency_id;
            $request['transaction_type_id'] = Exchange_From;
            $amountLimitCheck               = $this->amountLimitCheck($request);
            $request['currency_id']         = $to_currency_id;
            if ($amountLimitCheck->getData()->success->status == 200) {
                if (!($amountLimitCheck->getData()->success->totalAmount < $amountLimitCheck->getData()->success->balance)) {
                    return back()->withErrors(__("Not have enough balance !"))->withInput();
                }
            }
            elseif ($amountLimitCheck->getData()->success->status == 401) {
                return back()->withErrors($amountLimitCheck->getData()->success->message)->withInput();
            }
            //backend validation ends
            
            $data['fromCurrency'] = $this->helper->getCurrencyObject(['id' => $request->from_currency_id], ['code', 'symbol']);

            $request['fromWalletCode'] = $data['fromCurrency']['code'];
            $request['fromWallet'] = $request->from_currency_id;
            $request['toWallet'] = $request->currency_id;
            $response =  $this->getCurrenciesExchangeRate($request);

            Session::put('destination_exchange_rate', $response->getData()->destinationCurrencyRate);

            $request['finalAmount'] = $request->amount * $response->getData()->destinationCurrencyRate;
            $request['fee'] = $amountLimitCheck->getData()->success->totalFees;

            $data['transInfo'] = $request->all();
            $data['transInfo']['dCurrencyRate'] = $response->getData()->destinationCurrencyRate;
            $data['transInfo']['currCode']      = $response->getData()->destinationCurrencyCode;
            $data['transInfo']['finalAmount']   = $data['transInfo']['finalAmount'];
            $data['transInfo']['defaultAmnt']   = $request->amount;
            $data['transInfo']['totalAmount']   = ($request->amount) + $request->fee;
            session(['transInfo' => $request->all()]);
            return view('user.exchange-currency.confirm', $data);
        }
    }

    public function exchangeOfCurrencyConfirm()
    {
        $sessionValue = session('transInfo');
        if (empty($sessionValue))
        {
            return redirect('exchange');
        }

        actionSessionCheck();

        $fromWalletCurrencyId      = $sessionValue['from_currency_id'];
        $toWalletCurrencyId        = $sessionValue['currency_id'];
        $finalAmount               = $sessionValue['finalAmount'];
        $destinationCurrencyExRate = session('destination_exchange_rate');
        $user_id                   = auth()->user()->id;
        $uuid                      = unique_code();
        $fromWallet                = $this->helper->getUserWallet(['currency:id,code,symbol'], ['user_id' => $user_id, 'currency_id' => $fromWalletCurrencyId], ['id', 'currency_id', 'balance']);
        $toWallet                  = $this->helper->getUserWallet(['currency:id,code,symbol'], ['user_id' => $user_id, 'currency_id' => $toWalletCurrencyId], ['id', 'currency_id', 'balance']);
        $feesDetails               = $this->helper->getFeesLimitObject([], Exchange_From, $fromWalletCurrencyId, null, null, ['charge_percentage', 'charge_fixed']);
        $arr                       = [
            'unauthorisedStatus'        => null,
            'user_id'                   => $user_id,
            'toWalletCurrencyId'        => $toWalletCurrencyId,
            'fromWallet'                => $fromWallet,
            'toWallet'                  => $toWallet,
            'finalAmount'               => $finalAmount,
            'uuid'                      => $uuid,
            'destinationCurrencyExRate' => $destinationCurrencyExRate,
            'amount'                    => $sessionValue['amount'],
            'fee'                       => $sessionValue['fee'],
            'charge_percentage'         => $feesDetails->charge_percentage,
            'charge_fixed'              => $feesDetails->charge_fixed,
            'formattedChargePercentage' => $sessionValue['amount'] * (@$feesDetails->charge_percentage / 100),
        ];

        //For success page
        $data['fromWallet']                 = $fromWallet;
        $data['transInfo']['defaultAmnt']   = $sessionValue['amount'];
        $data['transInfo']['finalAmount']   = $destinationCurrencyExRate * $sessionValue['amount'];
        $data['transInfo']['dCurrencyRate'] = $destinationCurrencyExRate;

        //Get response
        $response = $this->exchange->processExchangeMoneyConfirmation($arr);
        if ($response['status'] != 200) {
            if (empty($response['exchangeCurrencyId'])) {
                Session::forget('transInfo');
                $this->helper->one_time_message('error', $response['message']);
                return redirect('exchange');
            }
        }
        //For success page
        $getExchangeToWallet               = CurrencyExchange::with(['toWallet:id,currency_id', 'toWallet.currency:id,code,symbol'])->where(['id' => $response['exchangeCurrencyId']])->first(['id', 'to_wallet']);
        $data['transInfo']['currSymbol']   = $getExchangeToWallet->toWallet->currency->symbol;
        $data['transInfo']['currCode']     = $getExchangeToWallet->toWallet->currency->code;
        $data['transInfo']['currency_id']   = $sessionValue['currency_id'];
        $data['transInfo']['trans_ref_id'] = $response['exchangeCurrencyId'];

        Session::forget('transInfo');
        clearActionSession();
        return view('user.exchange-currency.success', $data);
    }

    public function exchangeOfPrintPdf($transactionId)
    {
        $data['currencyExchange'] = CurrencyExchange::with([
            'fromWallet:id,currency_id',
            'fromWallet.currency:id,code,symbol',
            'toWallet:id,currency_id',
            'toWallet.currency:id,code',
        ])->where(['id' => $transactionId])->first();

        generatePDF('user.exchange-currency.exchange-money-pdf', 'exchange_', $data);
    }

    //Extended Functions below
    public function activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($userCurrencyList as $aCurrency)
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

                    $wallet = Wallet::where(['currency_id' => $aCurrency->id, 'user_id' => auth()->user()->id])->first(['balance']);
                    if (!empty($wallet))
                    {
                        $selectedCurrency[$aCurrency->id]['balance'] = isset($wallet->balance) ? $wallet->balance : 0.00;
                    }
                }
            }
        }
        return $selectedCurrency;
    }

    public function userCurrencyList($userCurrencyList, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        $i                = 0;
        foreach ($userCurrencyList as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$i]['id']           = $aCurrency->id;
                    $selectedCurrency[$i]['name']         = $aCurrency->name;
                    $selectedCurrency[$i]['symbol']       = $aCurrency->symbol;
                    $selectedCurrency[$i]['code']         = $aCurrency->code;
                    $selectedCurrency[$i]['hundreds_one'] = $aCurrency->hundreds_one;
                    $selectedCurrency[$i]['rate']         = $aCurrency->rate;
                    $selectedCurrency[$i]['logo']         = $aCurrency->logo;
                    $selectedCurrency[$i]['status']       = $aCurrency->status;
                    $selectedCurrency[$i]['default']      = $aCurrency->default;
                    $i++;
                }
            }
        }

        return $selectedCurrency;
    }
}
