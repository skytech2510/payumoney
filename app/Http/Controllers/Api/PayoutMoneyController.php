<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{FeesLimit,
    WithdrawalDetail,
    PayoutSetting,
    Transaction,
    Withdrawal,
    Wallet
};

class PayoutMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    protected $helper;
    protected $withdrawal;

    public function __construct()
    {
        $this->helper     = new Common();
        $this->withdrawal = new Withdrawal();
    }

    //Check User Payout Settings
    public function checkPayoutSettingsApi()
    {
        $payoutSettings = PayoutSetting::where(['user_id' => request('user_id')])->get(['id']);
        return response()->json([
            'status'         => $this->successStatus,
            'payoutSettings' => $payoutSettings,
        ]);
    }

    // Withdrawal Money Starts here
    public function getWithdrawalPaymentMethod()
    {
        $pm = [];
        $paymentMethod = PayoutSetting::where(['user_id' => request('user_id')])->get(['id', 'user_id', 'type', 'currency_id', 'crypto_address', 'email', 'account_name']);

        for ($i = 0; $i < count($paymentMethod); $i++) {
            $pm[$i]['id']                      = $paymentMethod[$i]->id;
            $pm[$i]['user_id']                 = $paymentMethod[$i]->user_id;
            $pm[$i]['paymentMethod']           = optional($paymentMethod[$i]->paymentMethod)->name;
            $pm[$i]['paymentMethodId']         = $paymentMethod[$i]->type;
            
            if ($paymentMethod[$i]->type == Paypal) {
                $pm[$i]['paymentMethodCredential'] = $paymentMethod[$i]->email;
            } elseif ($paymentMethod[$i]->type == Bank) {
                $pm[$i]['paymentMethodCredential'] = $paymentMethod[$i]->account_name;
            } elseif ($paymentMethod[$i]->type == Crypto) {
                $pm[$i]['cryptoAddress'] = $paymentMethod[$i]->crypto_address;
                $pm[$i]['currencyId'] = $paymentMethod[$i]->currency_id;
                $pm[$i]['currencyCode'] = optional($paymentMethod[$i]->currency)->code;
            } 
            $pm[$i]['withdrawalType'] = $paymentMethod[$i]->type;
        }
        $success['status'] = $this->successStatus;
        $success['paymentmethod'] = $pm;

        return response()->json(['success' => $success], $this->successStatus);
    }

    public function getWithdrawalCurrencyBasedOnPaymentMethod()
    {
        $payment_met_id = request('paymentMethodId');
        $cryptoCurrencyId = request('currencyId');

        $wallets = Wallet::where(['user_id' => request('user_id')])->whereHas('active_currency', function ($q) use ($payment_met_id)
        {
            $q->whereHas('fees_limit', function ($query) use ($payment_met_id)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', Withdrawal)->where('payment_method_id', $payment_met_id);
            });
        })
        ->with(['active_currency:id,code,type', 'active_currency.fees_limit:id,currency_id'])
        ->get(['currency_id', 'is_default']);

        if (!empty($cryptoCurrencyId)) {
            $wallets = Wallet::where(['user_id' => request('user_id'), 'currency_id' => $cryptoCurrencyId])
            ->whereHas('active_currency', function ($q) use ($payment_met_id)
            {
                $q->whereHas('fees_limit', function ($query) use ($payment_met_id)
                {
                    $query->where('has_transaction', 'Yes')->where('transaction_type_id', Withdrawal)->where('payment_method_id', $payment_met_id);
                });
            })
            ->with(['active_currency:id,code,type', 'active_currency.fees_limit:id,currency_id'])
            ->get(['currency_id', 'is_default']);
        }

        //map wallets
        $arr = [];
        $currencies = $wallets->map(function ($wallet)
        {
            $arr['id']             = optional($wallet->active_currency)->id;
            $arr['code']           = optional($wallet->active_currency)->code;
            $arr['default_wallet'] = $wallet->is_default;
            $arr['currencyType'] = optional($wallet->active_currency)->type;
            return $arr;
        });
        
        $success['currencies'] = $currencies;
        $success['status']     = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function getWithdrawDetailsWithAmountLimitCheck()
    {
        $user_id         = request('user_id');
        $amount          = request('amount');
        $currency_id     = request('currency_id');
        $payoutSettingId = request('payoutSetId');
        $paymentMethodId = request('paymentMethodId');

        $payoutSetting             = PayoutSetting::with(['paymentMethod:id,name'])->where(['id' => $payoutSettingId])->first(['account_name', 'account_number', 'type', 'swift_code', 'bank_name']);
        $success['account_name']   = $payoutSetting->account_name;
        $success['account_number'] = $payoutSetting->account_number;
        $success['type']           = $payoutSetting->paymentMethod->name;
        $success['swift_code']     = $payoutSetting->swift_code;
        $success['bank_name']      = $payoutSetting->bank_name;

        $wallets     = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['balance']);
        $feesDetails = FeesLimit::with('currency:id,symbol,code')->where(['transaction_type_id' => Withdrawal, 'currency_id' => $currency_id, 'payment_method_id' => $paymentMethodId])
            ->first(['charge_percentage', 'charge_fixed', 'min_limit', 'max_limit', 'currency_id']);
        //Wallet Balance Limit Check Starts here
        $checkAmount = $amount + $feesDetails->charge_fixed + (@$feesDetails->charge_percentage * $amount / 100);
        if (@$wallets)
        {
            //if((@$wallets->balance) < (@$amount)){
            if ((@$checkAmount) > (@$wallets->balance) || (@$wallets->balance < 0))
            {
                $success['reason']  = 'insufficientBalance';
                $success['message'] = "Sorry, not enough funds to perform the operation.";
                $success['status']  = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }
        //Wallet Balance Limit Check Ends here

        //Amount Limit Check Starts here
        if (isset($feesDetails)) {
            $totalFess                    = ($feesDetails->charge_percentage * $amount / 100) + ($feesDetails->charge_fixed);
            $success['amount']            = $amount;
            $success['totalFees']         = $totalFess;
            $success['totalHtml']         = formatNumber($totalFess, optional($feesDetails->currency)->id);
            $success['currency_id']       = $feesDetails->currency_id;
            $success['payout_setting_id'] = $payoutSettingId;
            $success['currSymbol']        = optional($feesDetails->currency)->symbol;
            $success['currCode']          = optional($feesDetails->currency)->code;
            $success['totalAmount']       = $amount + $totalFess;

            $success['status'] = $this->successStatus;

            if (empty($feesDetails->max_limit)) {
                if (isset($amount) && $amount < $feesDetails->min_limit) {
                    $success['reason']   = 'minLimit';
                    $success['minLimit'] = $feesDetails->min_limit;
                    $success['message']  = 'Minimum amount ' . formatNumber($feesDetails->min_limit, optional($feesDetails->currency)->id);
                    $success['status']   = '401';
                } else {
                    $success['status'] = $this->successStatus;
                }
            } else {
                if (isset($amount) && ($amount < $feesDetails->min_limit || $amount > $feesDetails->max_limit)) {
                    $success['reason']   = 'minMaxLimit';
                    $success['minLimit'] = $feesDetails->min_limit;
                    $success['maxLimit'] = $feesDetails->max_limit;
                    $success['message']  = 'Minimum amount ' . formatNumber($feesDetails->min_limit, optional($feesDetails->currency)->id) . ' and Maximum amount ' . formatNumber($feesDetails->max_limit, optional($feesDetails->currency)->id);
                    $success['status']   = '401';
                } else {
                    $success['status'] = $this->successStatus;
                }
            }
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }

        //Code for Fees Limit Starts here
        if (empty($feesDetails)) {
            $feesPercentage               = 0;
            $feesFixed                    = 0;
            $totalFess                    = ($feesPercentage * $amount / 100) + ($feesFixed);
            $success['amount']            = $amount;
            $success['totalFees']         = $totalFess;
            $success['totalHtml']         = formatNumber($totalFess, optional($feesDetails->currency)->id);
            $success['currency_id']       = $feesDetails->currency_id;
            $success['payout_setting_id'] = $payoutSettingId;
            $success['currSymbol']        = optional($feesDetails->currency)->symbol;
            $success['currCode']          = optional($feesDetails->currency)->code;
            $success['totalAmount']       = $amount + $totalFess;
            $success['status']            = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        //Amount Limit Check Ends here
    }

    public function withdrawMoneyConfirm()
    {
        $user_id             = request('user_id');
        $uuid                = unique_code();
        $currency_id         = request('currency_id');
        $amount              = request('amount');
        $totalAmount         = request('amount') + request('totalFees');
        $payout_setting_id   = request('payout_setting_id');
        $payoutSetting       = $this->helper->getPayoutSettingObject(['paymentMethod:id,name'], ['id' => $payout_setting_id], ['*']);
        $payment_method_info = $payoutSetting->email ? $payoutSetting->email : $payoutSetting->paymentMethod->name;
        $wallet              = $this->helper->getUserWallet(['currency:id,symbol'], ['user_id' => $user_id, 'currency_id' => $currency_id], ['id', 'currency_id', 'balance']);
        $feeInfo             = $this->helper->getFeesLimitObject([], Withdrawal, $currency_id, $payoutSetting->type, null, ['charge_percentage', 'charge_fixed']);
        $feePercentage       = $amount * ($feeInfo->charge_percentage / 100);
        $arr                 = [
            'user_id'             => $user_id,
            'wallet'              => $wallet,
            'currency_id'         => $wallet->currency_id,
            'payment_method_id'   => $payoutSetting->paymentMethod->id,
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
        //Get response
        $response = $this->withdrawal->processPayoutMoneyConfirmation($arr, 'mobile');
        if ($response['status'] != 200)
        {
            if (empty($response['withdrawalTransactionId']))
            {
                return response()->json([
                    'status'                           => false,
                    'withdrawalValidationErrorMessage' => $response['ex']['message'],
                ]);
            }
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => true,
        ]);
    }
    //Withdrawal Money Ends here
}
