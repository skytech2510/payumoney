<?php

namespace Modules\TatumIo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TatumSendConfirmRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required',
            'merchantAddress' => 'required',
            'merchantBalance' => 'required',
            'userAddress' => 'required',
            'amount' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'user_id' => __('User'),
            'merchantAddress' => __('Merchant Address'),
            'merchantBalance' => __('Merchant Balance'),
            'userAddress' => __('User Address'),
            'amount' => __('Amount'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
