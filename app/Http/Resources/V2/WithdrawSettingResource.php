<?php

/**
 * @package WithdrawSettingResource
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 19-12-2022
 */


namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        switch ($this->type) {
            case Bank:
                $data = [
                    'account_name'        => $this->account_name,
                    'account_number'      => $this->account_number,
                    'swift_code'          => $this->swift_code,
                    'bank_name'           => $this->bank_name,
                    'bank_branch_name'    => $this->bank_branch_name,
                    'bank_branch_city'    => $this->bank_branch_city,
                    'bank_branch_address' => $this->bank_branch_address,
                    'country'             => $this->country,
                ];
                break;
            case Paypal:
                $data = [
                    'email' => $this->email,
                ];
                break;
            case Crypto:
                $data = [
                    'currency'        => $this->currency,
                    'crypto_address'  => $this->crypto_address,
                ];
                break;
            default:
                $data= [];
                break;
        }
        $data['payment_method'] = $this->type;
        $data['id'] = $this->id;
        return $data;
    }
}
