<?php

/**
 * @package CheckDuplicatePayPalEmail
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 19-12-2022
 */

namespace App\Rules\WithdrawalSetting;

use App\Models\PayoutSetting;
use Illuminate\Contracts\Validation\Rule;

class CheckDuplicatePayPalEmail implements Rule
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
        $dublicate = PayoutSetting::where(['user_id' => request()->user_id, 'email' => $value])
                                    ->whereNotNull('id')
                                    ->when(request()->isMethod('PUT'), function ($q) {
                                        $q->where('id', "!==", request()->id);
                                    })
                                    ->exists();
        if ("Paypal" == request()->payment_method && $dublicate) {
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
        return __('You can not add same email again as Withdrawal settings!');
    }
}
