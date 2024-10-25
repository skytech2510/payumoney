<?php

/**
 * @package GetBankListRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 12-12-2022
 */

 namespace Modules\TatumIo\Http\Requests;

use App\Http\Requests\CustomFormRequest;

class CryptoSendRequest extends CustomFormRequest
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
            'receiverAddress' => 'required',
            'amount' => 'required',
            'senderAddress' => 'required',
            'walletCurrencyCode' => 'required',
            'priority' => 'required'
        ];
    }


    public function messages()
    {
        return [
            'receiverAddress' => __('Receiver Address'),
            'senderAddress' => __('Sender Address'),
            'walletCurrencyCode' => __('Network'),
            'priority' => __('Priority'),
            'amount' => __('Amount'),
        ];
    }
}
