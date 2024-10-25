<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Rules\CheckWalletBalance;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Validator, Session;
use App\Models\{Wallet,
    PayoutSetting,
    PaymentMethod,
    Transaction,
    Withdrawal,
    FeesLimit,
    Country,
    Currency
};

class WithdrawalController extends Controller
{
    protected $helper;
    protected $withdrawal;
    protected $email;

    public function __construct()
    {
        $this->helper     = new Common();
        $this->email      = new EmailController();
        $this->withdrawal = new Withdrawal();
    }

    //Payout Setting starts
    public function payouts()
    {
        setActionSession();
        $data['menu']    = 'payout';
        $data['payouts'] = Withdrawal::with(['payment_method:id,name', 'withdrawal_detail:id,withdrawal_id,account_name,account_number,bank_name', 'currency:id,code'])
            ->where(['user_id' => auth()->user()->id])->orderBy('withdrawals.created_at', 'desc')
            ->select('id', 'created_at', 'payment_method_id', 'amount', 'subtotal', 'currency_id', 'status', 'payment_method_info')
            ->paginate(10);

        // if no payout setting
        $data['payoutSettings'] = PayoutSetting::where(['user_id' => auth()->user()->id])->get(['id']);
        return view('user.withdrawal.index', $data);
    }

    public function payoutSetting()
    {
        $currencies = Currency::whereHas('wallet', function ($q) {
            $q->where('user_id', auth()->id());
        })->whereHas('fees_limit', function ($query) {
            $query->where([
                'has_transaction' => 'yes', 
                'transaction_type_id' => Withdrawal
            ]);
        })->where([
            'status' => 'Active', 
            'type' => 'crypto'
        ])->get(['id', 'code']);

        $paymentMethod = isset(request()->payment_method) ? request()->payment_method : 'all';

        $data = [
            'menu' => 'payout',
            'paymentMethod' => $paymentMethod,
            'currencies' => $currencies,
            'countries' => Country::get(['id', 'name']),
            'payoutSettings' => (new PayoutSetting())->getPayoutSettings($paymentMethod)->paginate(10),
            'paymentMethods' => PaymentMethod::whereIn('id', getPaymoneySettings('payment_methods')['web']['withdrawal'])->whereStatus('Active')->get(['id', 'name'])
        ];

        if (config('mobilemoney.is_active')) {
            $data['networks'] = \App\Models\MobileMoney::pluck('mobilemoney_name', 'id');
        }

        return view('user.withdrawal.setting', $data);
    }

    public function payoutSettingStore(Request $request)
    {
        $type                   = $request->type;
        $payoutSetting          = new PayoutSetting();
        $payoutSetting->type    = $type;
        $payoutSetting->user_id = auth()->user()->id;

        if ($type == Bank) {
            $payoutSetting->account_name        = $request->account_name;
            $payoutSetting->account_number      = $request->account_number;
            $payoutSetting->swift_code          = $request->swift_code;
            $payoutSetting->bank_name           = $request->bank_name;
            $payoutSetting->bank_branch_name    = $request->branch_name;
            $payoutSetting->bank_branch_city    = $request->branch_city;
            $payoutSetting->bank_branch_address = $request->branch_address;
            $payoutSetting->country             = $request->country;
        } elseif ($type == Paypal) {
            $payoutDuplicateEmailCheck = PayoutSetting::where(['user_id' => auth()->user()->id, 'email' => $request->email])->exists();
            
            if ($payoutDuplicateEmailCheck) {
                $this->helper->one_time_message('error', __('You can not add same email again as payout settings!'));
                return back();
            }
            $payoutSetting->email = $request->email;
        } elseif ($type == Crypto) {
            if (empty($request->crypto_address)) {
                $this->helper->one_time_message('error', __('Please provide a crypto address'));
                return redirect('payout/setting');
            } elseif (empty($request->currency)) {
                $this->helper->one_time_message('error', __('Please select a currency.'));
                return redirect('payout/setting');
            }

            $duplicateAddressCheck = PayoutSetting::where(['user_id' => auth()->user()->id, 'crypto_address' => $request->crypto_address])->exists();

            if ($duplicateAddressCheck) {
                $this->helper->one_time_message('error', __('Crypto address is already exist.'));
                return back();
            }
            $payoutSetting->currency_id = $request->currency;
            $payoutSetting->crypto_address = $request->crypto_address;
        } elseif (config('mobilemoney.is_active') && $type == (defined('MobileMoney') ? MobileMoney : '')) {
            if (empty($request->mobilemoney_id)) {
                $this->helper->one_time_message('error', __('Please select a network.'));
                return redirect('payout/setting');
            } elseif (empty($request->mobile_number)) {
                $this->helper->one_time_message('error', __('Please provide a mobile number.'));
                return redirect('payout/setting');
            }
            $payoutSetting->mobilemoney_id = $request->mobilemoney_id;
            $payoutSetting->mobile_number = $request->mobile_number;
        } 
        $payoutSetting->save();

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal settings')]));
        return back();
    }

    public function payoutSettingUpdate(Request $request)
    {
        $id = $request->setting_id;
        $setting = PayoutSetting::find($id);
        if (!$setting) {
            $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('withdrawal settings')]));
            return back();
        }
        if ($setting->type == Bank) {
            $setting->account_name        = $request->account_name;
            $setting->account_number      = $request->account_number;
            $setting->bank_branch_name    = $request->branch_name;
            $setting->bank_branch_city    = $request->branch_city;
            $setting->bank_branch_address = $request->branch_address;
            $setting->country             = $request->country;
            $setting->swift_code          = $request->swift_code;
            $setting->bank_name           = $request->bank_name;
        } elseif ($setting->type == Paypal) {
            $payoutDuplicateEmailCheck = PayoutSetting::where(['user_id' => auth()->user()->id, 'email' => $request->email])
                                            ->where(function($query) use ($id){
                                                $query->where('id', '!=', $id);
                                            })
                                            ->exists();
            
            if ($payoutDuplicateEmailCheck) {
                $this->helper->one_time_message('error', __('You can not add same email again as payout settings!'));
                return back();
            }
            $setting->email = $request->email;
        } elseif ($setting->type == Crypto) {

            if (empty($request->crypto_address)) {
                $this->helper->one_time_message('error', __('Please provide a crypto address'));
                return redirect('payout/setting');
            } elseif (empty($request->currency)) {
                $this->helper->one_time_message('error', __('Please select a currency.'));
                return redirect('payout/setting');
            }

            $duplicateAddressCheck = PayoutSetting::where(['user_id' => auth()->user()->id, 'crypto_address' => $request->crypto_address])->where(function($query) use ($id) {
                $query->where('id', '!=', $id);
            })
            ->exists();

            if ($duplicateAddressCheck) {
                $this->helper->one_time_message('error', __('Crypto address is already exist.'));
                return back();
            }
            $setting->currency_id = $request->currency;
            $setting->crypto_address = $request->crypto_address;
        } elseif (config('mobilemoney.is_active') && $setting->type == (defined('MobileMoney') ? MobileMoney : '')) {
            if (empty($request->mobilemoney_id)) {
                $this->helper->one_time_message('error', __('Please select a network.'));
                return redirect('payout/setting');
            } elseif (empty($request->mobile_number)) {
                $this->helper->one_time_message('error', __('Please provide a mobile number.'));
                return redirect('payout/setting');
            }
            $setting->mobilemoney_id = $request->mobilemoney_id;
            $setting->mobile_number = $request->mobile_number;
        }
        $setting->save();

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal settings')]));
        return back();
    }

    public function payoutSettingDestroy(Request $request)
    {
        $id = $request->id;
        //used auth to verify payout of auth user
        $payout = auth()->user()->payoutSettings->where('id', $id)->first();
        $payout->delete();

        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('withdrawal settings')]));
        return back();
    }
    //Payout Setting ends

    //Payout - starts
    public function withdrawalCreate(Request $request)
    {
        setActionSession();

        if (route('user.withdrawal.confirm') != url()->previous()) {
            if (!empty(session('withdrawalData'))) {
                session()->forget('withdrawalData');
            }
        }
        
        $data['menu'] = 'withdrawal';
        
        $data['withdrawalMethods'] = PayoutSetting::with(['paymentMethod:id,name'])
                ->where(['user_id' => auth()->user()->id])
                ->get(array_merge(['id', 'type', 'email','currency_id', 'crypto_address', 'account_name', 'account_number', 'bank_name'], config('mobilemoney.is_active') ? ['mobilemoney_id', 'mobile_number'] : []));

        $data['defaultCurrency'] = Wallet::where('user_id', auth()->user()->id)->where('is_default', 'Yes')->first(['id', 'currency_id']);

        return view('user.withdrawal.create', $data);
    }

    public function withdrawalConfirm(Request $request)
    {
        $rules = array(
            'amount'            => ['required','numeric', new CheckWalletBalance],
            'withdrawal_method_id' => 'required',
            'currency_id'       => 'required',
        );
        $fieldNames = array(
            'amount'            => 'Amount',
            'withdrawal_method_id' => 'Payment method',
            'currency_id'       => 'Currency',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            //backend validation starts
            $request['transaction_type_id'] = Withdrawal;
            $myResponse = $this->withdrawalAmountLimitCheck($request);

            if ($myResponse) {
                if ($myResponse->getData()->success->status == 200) {
                    if ($myResponse->getData()->success->totalAmount > $myResponse->getData()->success->balance) {
                        return back()->withErrors(__("Not have enough balance !"))->withInput();
                    }
                } elseif ($myResponse->getData()->success->status == 401) {
                    return back()->withErrors($myResponse->getData()->success->message)->withInput();
                }
            }
            //backend valdation ends

            $wallet = Wallet::with(['currency:id,symbol,code'])->where(['user_id' => auth()->user()->id, 'currency_id' => $request->currency_id])->first(['currency_id']);
            if ($wallet) {
                $data['transInfo']['currSymbol'] = $wallet->currency?->symbol;
                $data['transInfo']['currencyCode'] = $wallet->currency?->code;
                $data['transInfo']['currency_id'] = $wallet->currency?->id;
                $data['transInfo']['amount'] = $request->amount;

                $feesInfo = FeesLimit::where(['transaction_type_id' => Withdrawal, 'currency_id' => $wallet->currency_id, 'payment_method_id' => $request->payment_method_id])->first(['charge_percentage', 'charge_fixed']);

                $percentageCalc = $request->amount * ($feesInfo->charge_percentage / 100);
                $fee = $percentageCalc + $feesInfo->charge_fixed;

                $data['transInfo']['fee']            = $fee;
                $data['transInfo']['totalAmount']    = $request->amount + $fee;
                $data['transInfo']['payout_setting'] = PayoutSetting::find($request->withdrawal_method_id);

                //saving in sessions
                $withdrawalData['withdrawal_method_id']   = $request->withdrawal_method_id;
                $withdrawalData['currency_id']         = $request->currency_id;
                $withdrawalData['totalAmount']         = $request->amount + $fee;
                $withdrawalData['amount']              = $request->amount;
                $withdrawalData['payment_method_info'] = $request->payment_method_info;
                $withdrawalData['payment_method_id']   = $request->payment_method_id;
                session(['withdrawalData' => $withdrawalData]);

                return view('user.withdrawal.confirm', $data);
            }
        }
    }

    //get Withdrawal FeesLimits Active Currencies
    public function getWithdrawalFeesLimitsActiveCurrencies(Request $request)
    {
        $payment_met_id = $request->payment_method_id;
        $transaction_type_id = $request->transaction_type_id;
        $cryptoCurrencyId = $request->currencyId;

        $wallets = Wallet::where(['user_id' => auth()->user()->id])->whereHas('active_currency', function ($q) use ($payment_met_id, $transaction_type_id)
        {
            $q->whereHas('fees_limit', function ($query) use ($payment_met_id, $transaction_type_id)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', $transaction_type_id)->where('payment_method_id', $payment_met_id);
            });
        })
        ->with(['active_currency:id,code,type', 'active_currency.fees_limit:id,currency_id']) //Optimized
        ->get(['currency_id', 'is_default']);


        if (!empty($cryptoCurrencyId)) {
            $wallets = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $cryptoCurrencyId])->whereHas('active_currency', function ($q) use ($payment_met_id, $transaction_type_id)
            {
                $q->whereHas('fees_limit', function ($query) use ($payment_met_id, $transaction_type_id)
                {
                    $query->where('has_transaction', 'Yes')->where('transaction_type_id', $transaction_type_id)->where('payment_method_id', $payment_met_id);
                });
            })
            ->with(['active_currency:id,code,type', 'active_currency.fees_limit:id,currency_id']) //Optimized
            ->get(['currency_id', 'is_default']);
        }
        $currencies = $wallets->map(function ($wallet) //map acts as foreach but we can customize the index as preferred
            {
                $arr['id']             = $wallet->active_currency?->id;
                $arr['code']           = $wallet->active_currency?->code;
                $arr['type']           = $wallet->active_currency?->type;
                $arr['default_wallet'] = $wallet->is_default;
                return $arr;
            });
        $success['status'] = 'success';
        $success['currencies'] = $currencies;

        return response()->json(['success' => $success]);
    }

    //Code for withdrawal Amount Limit Check
    public function withdrawalAmountLimitCheck(Request $request)
    {
        $amount      = $request->amount;
        $user_id     = auth()->user()->id;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'payment_method_id' => $request->payment_method_id, 'currency_id' => $request->currency_id])
            ->first(['max_limit', 'min_limit', 'charge_percentage', 'charge_fixed']);

        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['message'] = __('Minimum amount ') . formatNumber($feesDetails->min_limit, $request->currency_id);
                $success['status']  = '401';
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
                $success['message'] = __('Minimum amount ') . formatNumber($feesDetails->min_limit, $request->currency_id) . __(' and Maximum amount ') . formatNumber($feesDetails->max_limit, $request->currency_id);
                $success['status']  = '401';
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
            $feesPercentage            = 0;
            $feesFixed                 = 0;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalHtml']      = formatNumber($totalFess, $request->currency_id);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesPercentage;
            $success['fFees']          = $feesFixed;
            $success['pFeesHtml']      = formatNumber($feesPercentage, $request->currency_id);
            $success['fFeesHtml']      = formatNumber($feesFixed, $request->currency_id);
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = 0;
        }
        else
        {
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalHtml']      = formatNumber($totalFess, $request->currency_id);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage, $request->currency_id);
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed, $request->currency_id);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $wallet                    = Wallet::where(['currency_id' => $request->currency_id, 'user_id' => $user_id])->first(['balance']);
            $success['balance']        = @$wallet->balance ? @$wallet->balance : 0;
        }
        return response()->json(['success' => $success]);
    }

    public function withdrawalSuccess()
    {
        $sessionValue = Session::get('withdrawalData');
        if (empty($sessionValue))
        {
            return redirect('payout');
        }

        actionSessionCheck();

        $user_id             = auth()->user()->id;
        $uuid                = unique_code();
        $withdrawal_method_id   = $sessionValue['withdrawal_method_id'];
        $currency_id         = $sessionValue['currency_id'];
        $totalAmount         = $sessionValue['totalAmount'];
        $amount              = $sessionValue['amount'];
        $payment_method_info = $sessionValue['payment_method_info'];
        $payment_method_id   = $sessionValue['payment_method_id'];
        $payoutSetting       = $this->helper->getPayoutSettingObject(['paymentMethod:id'], ['id' => $withdrawal_method_id], ['*']);
        $wallet              = $this->helper->getUserWallet(['currency:id,symbol,code'], ['user_id' => $user_id, 'currency_id' => $currency_id], ['id', 'balance', 'currency_id']);
        $feeInfo             = $this->helper->getFeesLimitObject([], Withdrawal, $wallet->currency_id, $payment_method_id, null, ['charge_percentage', 'charge_fixed']);
        $feePercentage       = $amount * (@$feeInfo->charge_percentage / 100); //correct calc
        $arr                 = [
            'user_id'             => $user_id,
            'wallet'              => $wallet,
            'currency_id'         => $wallet->currency_id,
            'payment_method_id'   => $payoutSetting->paymentMethod?->id,
            'payoutSetting'       => $payoutSetting,
            'uuid'                => $uuid,
            'percentage'          => $feeInfo->charge_percentage,
            'charge_percentage'   => $feePercentage,
            'charge_fixed'        => $feeInfo->charge_fixed,
            'amount'              => $amount,
            'totalAmount'         => $totalAmount,
            'subtotal'            => $amount - ($feePercentage + $feeInfo->charge_fixed),
            'payment_method_info' => $payment_method_info,
        ];
        $data['currencySymbol'] = $wallet->currency?->symbol;
        $data['currencyCode'] = $wallet->currency?->code;
        $data['amount'] = $arr['amount'];
        $data['currency_id'] = $sessionValue['currency_id'];

        //Get response
        $response = $this->withdrawal->processPayoutMoneyConfirmation($arr);
        if ($response['status'] != 200) {
            if (empty($response['withdrawalTransactionId'])) {
                Session::forget('withdrawalData');
                $this->helper->one_time_message('error', $response['message']);
                return redirect('payout');
            }
        }
        $data['transactionId'] = $response['withdrawalTransactionId'];

        //clear session
        Session::forget('withdrawalData');
        clearActionSession();
        return view('user.withdrawal.success', $data);
    }

    public function withdrawalPrintPdf($trans_id)
    {
        $data['transactionDetails'] = Transaction::with(['payment_method:id,name', 'currency:id,code,symbol'])
            ->where('id', $trans_id)
            ->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total', 'transaction_type_id', 'transaction_reference_id']);

        generatePDF('user.withdrawal.withdrawal-pdf', 'withdrawal_', $data);
    }
    //Payout - ends
}
