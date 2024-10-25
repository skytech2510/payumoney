<?php

/**
 * @package CustomFormRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class CustomFormRequest extends FormRequest
{
    use ApiResponse;
    /**
     * To handle validation error for api and formatting the error response
     *
     * @param Validator $validator
     * @throws HttpResponseException
     * @throws ValidationException
     */
    public function failedValidation(Validator $validator)
    {
        if ($this->wantsJson()) {
            throw new HttpResponseException($this->unprocessableResponse($validator->errors()));
        }
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
}
