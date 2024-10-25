<?php

/**
 * @package UserLoginRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Http\Requests;

class UserLoginRequest extends CustomFormRequest
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
            'email'    => 'required',
            'password' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $loginVia = settings('login_via');
        switch ($loginVia) {
            case 'phone_only':
                $loginMethod = __("Phone");
                break;

            case 'email_or_phone':
                $loginMethod = __("Email or Phone");
                break;

            default:
                $loginMethod = __("Email");
                break;
        }
        return [
            'email'    => $loginMethod,
            'password' => __('Password'),
        ];
    }
}
