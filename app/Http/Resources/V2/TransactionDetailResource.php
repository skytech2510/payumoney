<?php

/**
 * @package TransactionDetailResource
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) : array
    {
        $symbol = optional($this->currency)->symbol;
        if ('fiat' != $this->currency->type) {
            $this->totalFees = (($this->charge_percentage == 0) && ($this->charge_fixed == 0)) ? moneyFormat($symbol, formatNumber(0, $this->currency_id)) :
            moneyFormat($symbol, formatNumber($this->charge_fixed, $this->currency_id));
        } else {
            $this->totalFees = (($this->charge_percentage == 0) && ($this->charge_fixed == 0)) ? moneyFormat($symbol, formatNumber(0, $this->currency_id)) : moneyFormat($symbol, formatNumber($this->charge_percentage + $this->charge_fixed, $this->currency_id));
        }
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'user_first_name'     => optional($this->user)->first_name,
            'user_last_name'      => optional($this->user)->last_name,
            'user_email'          => optional($this->user)->email,
            'user_phone'          => optional($this->user)->formattedPhone,
            'user_photo'          => optional($this->user)->picture,

            'end_user_id'         => $this->end_user_id,
            'end_user_first_name' => optional($this->end_user)->first_name,
            'end_user_last_name'  => optional($this->end_user)->last_name,
            'end_user_email'      => optional($this->end_user)->email,
            'end_user_phone'      => optional($this->end_user)->formattedPhone,
            'end_user_photo'      => optional($this->end_user)->picture,

            'currency_id'         => optional($this->currency)->id,
            'currency_code'       => optional($this->currency)->code,
            'currebcy_symbol'     => $symbol,

            'total'               => moneyFormat($symbol, formatNumber($this->total, $this->currency_id)),
            'subtotal'            => moneyFormat($symbol, formatNumber($this->subtotal, $this->currency_id)),
            'totalFees'           => $this->totalFees,

            'payment_method_name' => optional($this->payment_method)->name,
            'company_name'        => settings('name'),

            'merchant_name'       => optional($this->merchant)->business_name,
            'type_id'             => optional($this->transaction_type)->id,
            'transaction_type'    => optional($this->transaction_type)->name,
            'transaction_reference_id' => $this->transaction_reference_id,
            'note'                => $this->description,
            'uuid'                => $this->transaction_id,
            'status'              => $this->status,
            't_created_at'        => dateFormat($this->t_created_at, $this->user_id),
        ];
    }
}
