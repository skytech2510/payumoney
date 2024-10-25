<?php

/**
 * @package CheckDuplicateCryptoAddress
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 19-12-2022
 */

namespace App\Rules\PayoutSetting;

use App\Models\PayoutSetting;
use Illuminate\Contracts\Validation\Rule;

class CheckDuplicateCryptoAddress implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $duplicate = PayoutSetting::where(['user_id' => request()->user_id, 'crypto_address' => $value])
                                ->when(request()->isMethod('PUT'), function ($q) {
                                    $q->where('id', request()->id);
                                })
                                ->exists();
        if ("Crypto" == request()->payment_method && $duplicate) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The :x is already exist.', ['x' => __('Crypto address')]);
    }
}
