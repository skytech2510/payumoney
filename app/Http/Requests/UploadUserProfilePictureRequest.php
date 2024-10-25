<?php

/**
 * @package UploadUserProfilePictureRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 05-12-2022
 */

namespace App\Http\Requests;

class UploadUserProfilePictureRequest extends CustomFormRequest
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
            'file' => 'image|max:5120',
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
            'file.image' => __('The file must be an image (jpg, jpeg, png, bmp, or gif)'),
            'file.max'   => __('The file size must not be greater than 5MB'),
        ];
    }
}
