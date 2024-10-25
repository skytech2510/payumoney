<?php

namespace App\Http\Requests\Api\V2\QrCode;

use App\Http\Requests\CustomFormRequest;

class QrCodeRequest extends CustomFormRequest
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
            'secret_text'    => 'required',
        ];
    }
}
