<?php

namespace App\Rules;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\Rule;

class CheckWithdrwalMethod implements Rule
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
        $paymentMethods = PaymentMethod::whereIn('id', getPaymoneySettings('payment_methods')['mobile']['withdrawal'])
                                        ->active()->pluck('id')->toArray();
        if (!in_array($value, $paymentMethods)) {
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
        return __("Payment method not allowed for withdrwal");
    }
}
