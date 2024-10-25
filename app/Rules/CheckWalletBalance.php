<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;

class CheckWalletBalance implements Rule
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
        if (!empty($value)) {
            $request = app(\Illuminate\Http\Request::class);
            $wallet  = Wallet::where(['user_id' => Auth::user()->id, 'currency_id' => $request->currency_id])->first();

            if ($wallet->balance < $request->amount) {
                return false;
            }
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
        return trans('validation.check_wallet_balance');
    }
}
