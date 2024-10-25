<?php

namespace Modules\BlockIo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockIoSettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:50',
            'network' => 'required|max:10|unique:crypto_asset_settings,network,' . decrypt(request()->id),
            'symbol' => 'required|max:5',
            'logo' => 'image|mimes:jpeg,png,jpg,bmp,ico|max:1024',
            'api_key' => 'required|max:50',
            'pin' => 'required|max:191',
            'address' => 'required|max:191',
            'status' => 'required|max:8',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('Please provide a crypto network name'),
            'network.required' => __('Please provide a crypto network code'),
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
