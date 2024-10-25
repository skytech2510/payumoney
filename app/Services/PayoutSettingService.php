<?php

/**
 * @package PayoutSettingService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 11-12-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\PayoutSettingException;
use App\Http\Resources\V2\{
    WithdrawSettingCollection,
    WithdrawSettingResource,
};
use App\Models\{
    Currency,
    PaymentMethod,
    PayoutSetting
};

class PayoutSettingService
{
    /**
     * Get list of payout setting 
     * 
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws PayoutSettingException
     */
    public function list($user_id) 
    {
        $payoutSettings = PayoutSetting::with(['paymentMethod:id,name','currency:id,code'])
                                        ->where(['user_id' => $user_id])
                                        ->get();
        if (0 == count($payoutSettings)) {
            throw new PayoutSettingException(__("No :x found.", [":x" => __("payout setting")]));
        }
        return new WithdrawSettingCollection($payoutSettings);
    }

    /**
     * Store payout setting
     *
     * @param array $data
     * @return bool
     */
    public function store($data)
    {
        return PayoutSetting::insert($data);
    }

    /**
     * Update payout setting
     *
     * @param array $data
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws PayoutSettingException
     */
    public function update($data, $id, $user_id)
    {
        $record = PayoutSetting::where(['id'=> $id, 'user_id' => $user_id]);
        if ($record->exists()) {
            return $record->update($data);
        }
        throw new PayoutSettingException(__('The :x does not exist.', ['x' => __('payout setting')]));
    }

    /**
     * To show a payout setting based in id
     *
     * @param int $id
     * @param int $user_id
     * @return WithdrawSettingtResource
     * @throws PayoutSettingException
     */
    public function show($id, $user_id)
    {
        $record = PayoutSetting::with(['paymentMethod:id,name','currency:id,code'])
                                        ->where(['id' => $id, 'user_id' => $user_id])
                                        ->first();
        if (!$record) {
            throw new PayoutSettingException(__('The :x does not exist.', ['x' => __('payout setting')]));
        }
        return new WithdrawSettingResource($record);
    }

    /**
     * Delete payout setting by id
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws PayoutSettingException
     */
    public function delete($id, $user_id)
    {
        $record = PayoutSetting::where(['id' => $id, 'user_id' => $user_id])->first(['id']);
        if (!$record) {
            throw new PayoutSettingException(__('The :x does not exist.', ['x' => __('payout setting')]));
        }
        return $record->delete();
    }
    
    /**
     * Delete payout setting by id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws PayoutSettingException
     */
    public function paymentMethods()
    {
        $paymentMethods  = PaymentMethod::whereIn('id', getPaymoneySettings("payment_methods")['mobile']['withdrawal'])
                        ->active()
                        ->get(['id', 'name']);
        if (0 == count($paymentMethods)) {
            throw new PayoutSettingException(__("No :x found.", [":x" => __("Payment Method")]));
        }
        return $paymentMethods;
    }

    /**
     * List of crypto currencies
     *
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws PayoutSettingException
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
            throw new PayoutSettingException(__("No :x found.", [":x" => __("Currency")]));
        }
        return $currencies;
    }

}
