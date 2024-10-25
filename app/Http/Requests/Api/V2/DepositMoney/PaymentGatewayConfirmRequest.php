<?php

/**
 * @package GetPaymentMethodRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 17-12-2022
 */

namespace App\Http\Requests\Api\V2\DepositMoney;

use App\Http\Requests\CustomFormRequest;

class PaymentGatewayConfirmRequest extends CustomFormRequest
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
            'amount' => 'required',
            'currency_id' => 'required',
            'payment_method_id' => 'required',
        ];
    }
}
