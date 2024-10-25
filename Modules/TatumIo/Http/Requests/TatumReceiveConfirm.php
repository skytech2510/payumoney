<?php

namespace Modules\TatumIo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TatumReceiveConfirmRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'userAddress' => 'required',
            'userBalance' => 'required',
            'merchantAddress' => 'required',
            'amount' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'userAddress' => __('User Address'),
            'userBalance' => __('User Balance'),
            'merchantAddress' => __('User Address'),
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
