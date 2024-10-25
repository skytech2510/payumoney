<?php

namespace App\Http\Requests\AcceptMoney;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'currency_id'  => 'required',
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
            'currency_id'  => __("Currency"),
        ];
    }
    
}