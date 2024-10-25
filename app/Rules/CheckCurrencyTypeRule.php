<?php

namespace App\Rules;
use App\Models\Currency;
use Illuminate\Contracts\Validation\Rule;

class CheckCurrencyTypeRule implements Rule
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
        $currency = Currency::where('id', request()->currency_id)->first('type');

        if (!$currency) {
            return false;
        }

        if ($currency->type == 'crypto') {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __("The currency type is not crypto.");
    }
}
