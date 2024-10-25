<?php

/**
 * @package WithdrawalSettingService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 11-12-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\WithdrawalSettingException;
use App\Http\Resources\V2\{
    WithdrawSettingCollection,
    WithdrawSettingResource,
};
use App\Models\{
    Currency,
    PaymentMethod,
    PayoutSetting
};

class WithdrawalSettingService
{
    /**
     * Get list of Withdrawal setting
     *
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws WithdrawalSettingException
     */
    public function list($user_id)
    {
        $WithdrawalSettings = PayoutSetting::with(['paymentMethod:id,name','currency:id,code'])
                                        ->where(['user_id' => $user_id])
                                        ->get();
        if (0 == count($WithdrawalSettings)) {
            throw new WithdrawalSettingException(__("No :x found.", ["x" => __("Withdrawal setting")]));
        }
        return new WithdrawSettingCollection($WithdrawalSettings);
    }

    /**
     * Store Withdrawal setting
     *
     * @param array $data
     * @return bool
     */
    public function store($data)
    {
        return PayoutSetting::insert($data);
    }

    /**
     * Update Withdrawal setting
     *
     * @param array $data
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws WithdrawalSettingException
     */
    public function update($data, $id, $user_id)
    {
        $record = PayoutSetting::where(['id'=> $id, 'user_id' => $user_id]);
        if ($record->exists()) {
            return $record->update($data);
        }
        throw new WithdrawalSettingException(__('The :x does not exist.', ['x' => __('Withdrawal setting')]));
    }

    /**
     * To show a Withdrawal setting based in id
     *
     * @param int $id
     * @param int $user_id
     * @return WithdrawSettingtResource
     * @throws WithdrawalSettingException
     */
    public function show($id, $user_id)
    {
        $record = PayoutSetting::with(['paymentMethod:id,name','currency:id,code'])
                                        ->where(['id' => $id, 'user_id' => $user_id])
                                        ->first();
        if (!$record) {
            throw new WithdrawalSettingException(__('The :x does not exist.', ['x' => __('Withdrawal setting')]));
        }
        return new WithdrawSettingResource($record);
    }

    /**
     * Delete Withdrawal setting by id
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws WithdrawalSettingException
     */
    public function delete($id, $user_id)
    {
        $record = PayoutSetting::where(['id' => $id, 'user_id' => $user_id])->first(['id']);
        if (!$record) {
            throw new WithdrawalSettingException(__('The :x does not exist.', ['x' => __('Withdrawal setting')]));
        }
        return $record->delete();
    }

    /**
     * Delete Withdrawal setting by id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws WithdrawalSettingException
     */
    public function paymentMethods()
    {
        $paymentMethods  = PaymentMethod::whereIn('id', getPaymoneySettings("payment_methods")['mobile']['withdrawal'])
                        ->active()
                        ->get(['id', 'name']);
        if (0 == count($paymentMethods)) {
            throw new WithdrawalSettingException(__("No :x found.", ["x" => __("Payment Method")]));
        }
        return $paymentMethods;
    }

    /**
     * List of crypto currencies
     *
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws WithdrawalSettingException
     */
    public function cyptoCurrencies($user_id)
    {
        $currencies = Currency::whereHas('wallet', function($q) use ($user_id) {
            $q->where(['user_id' => $user_id]);
        })
        ->whereHas('fees_limit', function($q) {
            $q->hasTransaction()->transactionType(Withdrawal);
        })
        ->active()->type("crypto")->get(['id', 'code']);
        if (0 == count($currencies)) {
            throw new WithdrawalSettingException(__("No :x found.", ["x" => __("Currency")]));
        }
        return $currencies;
    }

}
