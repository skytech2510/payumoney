<?php

/**
 * @package StoreWithdrawalSettingRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 19-12-2022
 */

namespace App\Http\Requests;

use App\Rules\CheckAllowedWithdrawalSettingPaymentMethod;
use App\Models\PaymentMethod;
use App\Rules\WithdrawalSetting\{
    CheckDuplicateCryptoAddress,
    CheckDuplicatePayPalEmail
};

class StoreWithdrawalSettingRequest extends CustomFormRequest
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
            'type'           => 'required|integer',
            'payment_method' => [
                                    'required',
                                    'string',
                                    'max:10',
                                    new CheckAllowedWithdrawalSettingPaymentMethod
                                ],
            'email'          => [
                                    'required_if:payment_method,Paypal|max:191',
                                    new CheckDuplicatePayPalEmail
                                ],
            'account_name'        => 'required_if:payment_method,Bank|max:100',
            'account_number'      => 'required_if:payment_method,Bank|max:25',
            'swift_code'          => 'required_if:payment_method,Bank|max:12',
            'bank_branch_name'    => 'required_if:payment_method,Bank|max:50',
            'bank_branch_city'    => 'required_if:payment_method,Bank|max:50',
            'bank_branch_address' => 'required_if:payment_method,Bank|max:191',
            'country'             => 'required_if:payment_method,Bank|integer|exists:countries,id',
            'currency_id'         => 'required_if:payment_method,Crypto|integer|exists:currencies,id',
            'crypto_address'      => [
                                        'required_if:payment_method,Crypto|max:191',
                                        new CheckDuplicateCryptoAddress
                                    ],
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
            'type'                => __('Payment method'),
            'payment_method'      => __('Payment method'),
            'email'               => __('Email'),
            'account_name'        => __('Account Name'),
            'account_number'      => __('Account Number'),
            'swift_code'          => __('Swift Code'),
            'bank_branch_name'    => __('Branch Name'),
            'bank_branch_city'    => __('Branch City'),
            'bank_branch_address' => __('Branch Address'),
            'country'             => __('Country'),
            'currency_id'         => __('Currency'),
            'crypto_address'      => __('Crypto Address'),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
            'type'    => PaymentMethod::where('name', request()->payment_method)->value('id')
        ]);
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validatedFormRequest()
    {
        switch (request()->type) {
            case Bank:
                $data = [
                    'account_name',
                    'account_number',
                    'swift_code',
                    'bank_name',
                    'bank_branch_name',
                    'bank_branch_city',
                    'bank_branch_address',
                    'country'
                ];
                break;
            case Paypal:
                $data = [
                    'email'
                ];
                break;
            case Crypto:
                $data =[
                    'currency_id',
                    'crypto_address',
                ];
                break;
            default:
                $data= [];
                break;
        }
        array_push($data,"type","user_id");
        return request()->only($data);
    }

    /** 
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'currency_id.exists' => __("The :x does not exist.", [":x" => __("currency")]),
            'country.exists'     => __("The :x does not exist.", [":x" => __("country")]),
        ];
    }
}
