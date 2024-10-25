<?php

/**
 * @package UserStoreRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 27-12-2022
 */

namespace App\Http\Requests;

use App\Rules\DuplicatePhoneNumberRule;
use Illuminate\Validation\Rule;
use App\Models\Role;

class UserStoreRequest extends CustomFormRequest
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
        $availableRoles = (new Role())->availableUserRoles();
        return [
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required',
            "type"       => ["required", Rule::in($availableRoles)],
            "phone"      => [new DuplicatePhoneNumberRule]
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'first_name' => __('First Name'),
            'last_name'  => __('Last Name'),
            'email'      => __('Email'),
            'password'   => __('Password'),
            "type"       => __('Type'),
        ];
    }
}
