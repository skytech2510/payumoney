<?php

namespace App\Http\Requests\Api\V2\QrCode;

use App\Http\Requests\CustomFormRequest;

class QrCodePaymentSubmitRequest extends CustomFormRequest
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
            'merchant_id'      => 'required|numeric',
            'merchant_user_id' => 'required|numeric',
            'user_id'          => 'required|numeric|different:merchant_user_id',
            'currency_id'      => 'required',
            'amount'           => 'required',
            'fee'              => 'required'
        ];
    }

    public function messages()
    {
        return [
            'user_id.different' => __('Merchant cannot make payment to himself'),
        ];
    }
}
