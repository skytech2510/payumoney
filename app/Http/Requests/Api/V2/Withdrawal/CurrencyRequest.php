<?php

/**
 * @package CurrencyRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 29-12-2022
 */

namespace App\Http\Requests\Api\V2\Withdrawal;

use App\Rules\{
    CheckWithdrwalMethod,
    CheckCurrencyTypeRule
};
use App\Http\Requests\CustomFormRequest;


class CurrencyRequest extends CustomFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_method' => [
                'required',
                'integer',
                'max:10',
                new CheckWithdrwalMethod
            ],
            'currency_id' => [
                'required_if:payment_method,'.Crypto.'',
                'integer',
                'exists:currencies,id',
                new CheckCurrencyTypeRule
            ],
        ];
    }

     /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'currency_id.required_if' => __("The currency id field is required when payment method is Crypto."),
        ];
    }


}
