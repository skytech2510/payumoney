<?php

/**
 * @package MerchantPaymentService
 * @author tehcvillage <support@techvill.org>
 * @contributor Foisal Ahmed <[foisal.techvill@gmail.com]>
 * @created 22-06-2023
 */

namespace App\Services;

use App\Models\{FeesLimit,
    MerchantPayment,
    Transaction,
    Wallet
};

class MerchantPaymentService
{
    public function makeMerchantPayment($request, $merchant, $feesLimit, $currencyId, $uniqueCode, $token = null, $paymentMethod = Mts, $status = 'Success')
    {
        $merchantPayment                    = new MerchantPayment();
        $merchantPayment->merchant_id       = $request->merchant;
        $merchantPayment->currency_id       = $currencyId;
        $merchantPayment->payment_method_id = $paymentMethod;
        $merchantPayment->user_id           = auth()->id();
        $merchantPayment->gateway_reference = $token != null ? $token : $uniqueCode;
        $merchantPayment->order_no          = $request->order_no;
        $merchantPayment->item_name         = $request->item_name;
        $merchantPayment->uuid              = $uniqueCode;
        $merchantPayment->charge_fixed      = $feesLimit['chargeFixed'];
        $merchantPayment->status            = $status;
        $merchantPayment->fee_bearer        = $merchant?->merchant_group?->fee_bearer;
        $merchantPayment->percentage        = $merchant->fee + $feesLimit['chargePercentage'];
        $merchantPayment->charge_percentage = $feesLimit['depositPercent'] + $feesLimit['merchantPercentOrTotalFee'];
        $merchantPayment->amount = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                        ? $request->amount - $feesLimit['totalFee']
                                        : $request->amount;
        $merchantPayment->total = $merchantPayment->amount + $feesLimit['totalFee'];
        $merchantPayment->save();

        return $merchantPayment;
    }

    public function makeUserTransaction($request, $merchant, $feesLimit, $currencyId, $uniqueCode, $merchantPayment, $status = 'Success')
    {
        $userTransaction                           = new Transaction();
        $userTransaction->user_id                  = auth()->id();
        $userTransaction->end_user_id              = $merchant->user_id;
        $userTransaction->currency_id              = $currencyId;
        $userTransaction->payment_method_id        = 1;
        $userTransaction->merchant_id              = $request->merchant;
        $userTransaction->uuid                     = $uniqueCode;
        $userTransaction->transaction_reference_id = $merchantPayment->id;
        $userTransaction->transaction_type_id      = Payment_Sent;
        $userTransaction->subtotal                 = $request->amount;

        $userTransaction->percentage = $merchant?->merchant_group?->fee_bearer == 'User'
                                        ? $merchant->fee + $feesLimit['chargePercentage']
                                        : 0;

        $userTransaction->charge_percentage = $merchant?->merchant_group?->fee_bearer == 'User'
                                                ? $feesLimit['depositPercent'] + $feesLimit['merchantPercentOrTotalFee']
                                                : 0;

        $userTransaction->charge_fixed = $merchant?->merchant_group?->fee_bearer == 'User'
                                            ? $feesLimit['chargeFixed']
                                            : 0;

        $userTransaction->total = '-' . $userTransaction->charge_percentage + $userTransaction->charge_fixed + $userTransaction->subtotal;
        $userTransaction->status = $status;
        $userTransaction->save();
    }

    public function makeMerchantTransaction($request, $merchant, $feesLimit, $currencyId, $uniqueCode, $merchantPayment, $paymentMethod = Mts, $status = 'Success')
    {
        $merchantTransaction                           = new Transaction();
        $merchantTransaction->user_id                  = $merchant->user_id;
        $merchantTransaction->end_user_id              = auth()->id();
        $merchantTransaction->currency_id              = $currencyId;
        $merchantTransaction->payment_method_id        = $paymentMethod;
        $merchantTransaction->uuid                     = $uniqueCode;
        $merchantTransaction->transaction_reference_id = $merchantPayment->id;
        $merchantTransaction->transaction_type_id      = Payment_Received;

        $merchantTransaction->subtotal = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                            ? $request->amount - $feesLimit['totalFee']
                                            : $request->amount;

        $merchantTransaction->percentage = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                            ? $merchant->fee + $feesLimit['chargePercentage']
                                            : 0;

        $merchantTransaction->charge_percentage = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                                    ? $feesLimit['depositPercent'] + $feesLimit['merchantPercentOrTotalFee']
                                                    : 0;

        $merchantTransaction->charge_fixed = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                                ? $feesLimit['chargeFixed']
                                                : 0;

        $merchantTransaction->total = $merchantTransaction->charge_percentage + $merchantTransaction->charge_fixed + $merchantTransaction->subtotal;
        $merchantTransaction->payment_status = $status;
        $merchantTransaction->status = $status;
        $merchantTransaction->merchant_id = $request->merchant;
        $merchantTransaction->save();
        return $merchantTransaction;
    }

    public function createOrUpdateMerchantWallet($amount, $merchant, $currencyId, $totalFee, $merchantWallet)
    {
        if (empty($merchantWallet)) {
            $wallet              = new Wallet();
            $wallet->user_id     = $merchant->user_id;
            $wallet->currency_id = $currencyId;
            $wallet->balance     = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                    ? ($amount - $totalFee)
                                    : $amount;
            $wallet->is_default  = 'No';
            $wallet->save();
        } else {
            $merchantWallet->balance = $merchant?->merchant_group?->fee_bearer == 'Merchant'
                                        ? $merchantWallet->balance +  ($amount - $totalFee)
                                        : $merchantWallet->balance + $amount;
            $merchantWallet->save();
        }
    }

    public function updateSenderWallet($amount, $merchant, $senderWallet)
    {

        $senderWallet->balance = $senderWallet->balance - $amount;
        $senderWallet->save();

    }



    public function checkMerchantPaymentFeesLimit($currencyId, $paymentMethodId, $amount, $merchantFee)
    {
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $paymentMethodId])->first(['charge_percentage', 'charge_fixed', 'has_transaction']);

        if (!empty($feeInfo) && $feeInfo->has_transaction == "Yes") {
            $feeInfoChargePercentage          = $feeInfo->charge_percentage;
            $feeInfoChargeFixed               = $feeInfo->charge_fixed;
            $depositCalcPercentVal            = $amount * ($feeInfoChargePercentage / 100);
            $depositTotalFee                  = $depositCalcPercentVal + $feeInfoChargeFixed;
            $merchantCalcPercentValOrTotalFee = $amount * ($merchantFee / 100);
            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
        } else {
            $feeInfoChargePercentage          = 0;
            $feeInfoChargeFixed               = 0;
            $depositCalcPercentVal            = 0;
            $depositTotalFee                  = 0;
            $merchantCalcPercentValOrTotalFee = $amount * ($merchantFee / 100);
            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
        }

        return [
            'merchantPercentOrTotalFee' => $merchantCalcPercentValOrTotalFee,
            'chargePercentage' => $feeInfoChargePercentage,
            'depositPercent' => $depositCalcPercentVal,
            'depositTotalFee' => $depositTotalFee,
            'chargeFixed' => $feeInfoChargeFixed,
            'totalFee' => $totalFee,
        ];
    }
}
