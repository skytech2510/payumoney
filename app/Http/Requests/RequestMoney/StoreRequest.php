<?php

/**
 * @package StoreRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Http\Requests\RequestMoney;

use App\Http\Requests\CustomFormRequest;

class StoreRequest extends CustomFormRequest
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
            'emailOrPhone' => 'required',
            'amount'       => 'required',
            'currencyId'   => 'required',
            'note'         => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $processedBy    = preference('processed_by');
        switch ($processedBy) {
            case 'phone':
                $method = __("Phone");
                break;
            
            case 'email_or_phone':
                $method = __("Email or Phone");
                break;
            
            default:
                $method = __("Email");
                break;
        }
        return [
            'emailOrPhone' => $method,
            'amount'       => __("Amount"),
            'email'        => __("Email"),
            'note'         => __("Note"),
            'currencyId'   => __("Currency"),
        ];
    }
    
}
