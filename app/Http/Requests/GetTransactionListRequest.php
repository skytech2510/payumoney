<?php

/**
 * @package GetTransactionListRequest
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 08-12-2022
 */

namespace App\Http\Requests;

class GetTransactionListRequest extends CustomFormRequest
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
            'type'   => 'required',
            'offset' => 'numeric|min:0',
            'limit'  => 'numeric|min:1',
            'order'  => 'in:desc,asc',
        ];
    }
}
