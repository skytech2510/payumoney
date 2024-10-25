<?php

/**
 * @package WithdrawalService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 27-12-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\{
    WithdrawalException,
    PaymentFailedException
};
use App\Http\Helpers\Common;
use App\Http\Resources\V2\FeesResource;
use App\Models\{
    Wallet,
    Withdrawal
};
use Exception, DB;
use App\Services\Mail\Withdrawal\WithdrawalViaAdminMailService;

class WithdrawalMoneyService
{
    /**
     * @var Common;
     */
    protected $helper;


    /**
     * Construct the common helper class
     *
     * @param Common $helper
     *
     * @return void
     */
    public function __construct(Common $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get list of Withdrawal currencies
     *
     * @param int $paymentMethod & $cryptoCurrencyId
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws WithdrawalException
     */
    public function getCurrencies($paymentMethod, $cryptoCurrencyId)
    {
        $currencies = [];
        $condition = (!empty($cryptoCurrencyId)) ? ['user_id' => auth()->id(), 'currency_id' => $cryptoCurrencyId] : ['user_id' => auth()->id()];

        $wallets = Wallet::with(['active_currency:id,code,type', 'active_currency.fees_limit:id,currency_id'])
                    ->whereHas('active_currency', function ($q) use ($paymentMethod) {
                        $q->whereHas('fees_limit', function ($query) use ($paymentMethod) {
                            $query->hasTransaction()->transactionType(Withdrawal)->where('payment_method_id', $paymentMethod);
                        });
                    })
                    ->where($condition)
                    ->get(['currency_id', 'is_default'])
                    ->map(function($items) use (&$currencies) {
                        $currencies[optional($items->active_currency)->id] =[
                            'id' => optional($items->active_currency)->id,
                            'code' => optional($items->active_currency)->code,
                            'type' => optional($items->active_currency)->type,
                            'default_wallet' => $items->is_default,
                        ];
                        return $currencies;
                    });

        if (0 == count($currencies)) {
            throw new WithdrawalException(__("No :x found.", ["x" => __("withdrawal currency")]));
        }
        return collect(array_values($currencies));
    }

    /**
     * Validate withdrawal amount with currency id
     *
     * @param int $currencyId
     * @param int $paymentMethod
     * @param int $withdrawalSettingId
     * @param double $amount
     * @param int $userId
     * @return array
     * @throws WithdrawalException
     */

    public function validateAmountLimit($currencyId, $paymentMethod, $withdrawalSettingId, $amount, $userId)
    {
        $withdrawalSetting = $this->getWithdrawalSettings($paymentMethod, $withdrawalSettingId, $userId);

        $feesDetails = $this->helper->transactionFees($currencyId, $amount, Withdrawal, $paymentMethod);

        $this->helper->amountIsInLimit($feesDetails, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $feesDetails->total_amount);

        $feesArray = [
            'payout_setting_id' => $withdrawalSettingId,
            'payoutSetting' => $withdrawalSetting,
        ];

        return array_merge((new FeesResource($feesDetails))->toArray(request()), $feesArray) ;

    }

    /**
     * Store withdrawal money
     *
     * @param int $userId
     * @param int $currencyId
     * @param double $amount
     * @param double $totalAmount
     * @param int $withdrawalSettingId
     * @throws WithdrawalException
     * @throws PaymentFailedException
     */

    public function withdrawalConfirm($userId, $currencyId, $amount, $totalAmount, $withdrawalSettingId)
    {

        $uuid = unique_code();
        $withdrawalSetting = $this->helper->getPayoutSettingObject(
            ['paymentMethod:id,name'],
            ['id' => $withdrawalSettingId], ['*']
        );

        $paymentMethodInfo = $withdrawalSetting->email ?
                            $withdrawalSetting->email :
                            optional($withdrawalSetting->paymentMethod)->name;

        $wallet = $this->helper->getWallet($userId, $currencyId, ['id', 'currency_id', 'balance']);

        $feesDetails = $this->helper->transactionFees(
                            $currencyId,
                            $amount,
                            Withdrawal,
                            $withdrawalSetting->type
                        );

        $this->helper->amountIsInLimit($feesDetails, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $totalAmount);

        $arr = [
            'user_id'             => $userId,
            'wallet'              => $wallet,
            'currency_id'         => $wallet->currency_id,
            'payment_method_id'   => optional($withdrawalSetting->paymentMethod)->id,
            'payoutSetting'       => $withdrawalSetting,
            'uuid'                => $uuid,
            'percentage'          => $feesDetails->charge_percentage,
            'charge_percentage'   => $feesDetails->fees_percentage,
            'charge_fixed'        => $feesDetails->charge_fixed,
            'amount'              => $amount,
            'totalAmount'         => $feesDetails->total_amount,
            'subtotal'            => $amount - $feesDetails->total_fees,
            'payment_method_info' => $paymentMethodInfo,
        ];

        try {

            DB::beginTransaction();

            $withdrawal = new Withdrawal();
            $withdraw = $withdrawal->createWithdrawal($arr);

            $arr['transaction_reference_id'] = $withdraw->id;

            $withdrawal->createWithdrawalDetail($arr);

            $withdrawal->createWithdrawalTransaction($arr);

            $withdrawal->updateWallet($arr);

            DB::commit();

            (new WithdrawalViaAdminMailService)->send($withdraw, 'payout');

            return [
                'status' => true,
                'tr_ref_id' => $withdraw->id,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw new PaymentFailedException($e->getMessage());
        }


    }

    /**
     * get withdrawal setting
     *
     * @param int $paymentMethod
     * @param int $withdrawalSettingId
     * @throws WithdrawalException
     */

    public function getWithdrawalSettings($paymentMethod, $withdrawalSettingId, $userId)
    {
        $option = ($paymentMethod == Bank)
                    ? ['account_name', 'account_number', 'type', 'swift_code', 'bank_name']
                    : (($paymentMethod == Paypal)
                        ? ['email', 'type']
                        : ['crypto_address', 'type']);

        $withdrawalSetting = $this->helper->getPayoutSettingObject(
                ['paymentMethod:id,name'],
                ['id' => $withdrawalSettingId, 'user_id' => $userId],
                $option
            );

        if (!$withdrawalSetting) {
            throw new WithdrawalException(__("No :x found.", ["x" => __("withdrawal setting")]));
        }

        return $withdrawalSetting;
    }


}
