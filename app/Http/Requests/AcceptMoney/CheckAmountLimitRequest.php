<?php

namespace App\Http\Requests\AcceptMoney;

use Illuminate\Foundation\Http\FormRequest;

class CheckAmountLimitRequest extends FormRequest
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
            'amount'      => 'required',
            'currency_id' => 'required',
        ];
    }
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'amount'      => __('Amount'),
            'currency_id' => __('Currency'),
        ];
    }
    
}
