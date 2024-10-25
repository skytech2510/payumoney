<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestMoneyDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'email'          => optional($this)->email,
            'phone'          => optional($this)->phone,
            'amount'         => optional($this)->amount,
            'note'           => optional($this)->note,
            'displayAmount'  => formatNumber(optional($this)->amount, optional($this->currency)->id),
            'currency'       => optional($this->currency)->code,
            'currency_id'    => optional($this->currency)->id,
            'currencySymbol' => optional($this->currency)->symbol,
            'currencyType'   => optional($this->currency)->type,
        ];
    }
}
