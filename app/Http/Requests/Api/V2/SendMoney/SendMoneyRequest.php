<?php

namespace App\Http\Requests\Api\V2\SendMoney;

use App\Http\Requests\CustomFormRequest;

class SendMoneyRequest extends CustomFormRequest
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
            'email' => 'required_without:phone|email',
            'phone' => 'nullable|string',
            'amount' => 'required|numeric',
            'total_fees' => 'required|numeric',
            'currency_id' => 'required|numeric',
            'note' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required_without' => __('Email or phone is required'),
        ];
    }
}
