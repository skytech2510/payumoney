<?php

namespace App\Http\Requests\Api\V2\SendMoney;

use App\Http\Requests\CustomFormRequest;

class AmountLimitCheckRequest extends CustomFormRequest
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
            'send_currency'    => 'required|numeric',
            'send_amount'    => 'required|numeric|min:0|not_in:0',
        ];
    }
}
