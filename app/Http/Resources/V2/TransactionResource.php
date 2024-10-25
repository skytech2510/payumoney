<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'id'                     => $this->id,
            'user_id'                => $this->user_id,
            'end_user_id'            => $this->end_user_id,

            'user_first_name'        => optional($this->user)->first_name,
            'user_last_name'         => optional($this->user)->last_name,
            'user_full_name'         => optional($this->user)->full_name,
            'user_email'             => optional($this->user)->email,
            'user_photo'             => image(optional($this->user)->picture, 'profile'),

            'end_user_first_name'    => optional($this->end_user)->first_name,
            'end_user_last_name'     => optional($this->end_user)->last_name,
            'end_user_full_name'     => optional($this->end_user)->full_name,
            'end_user_email'         => optional($this->end_user)->email,
            'end_user_photo'         => image(optional($this->end_user)->picture, 'profile'),

            'transaction_type_id'    => $this->transaction_type_id,
            'transaction_type'       => optional($this->transaction_type)->name,
            'curr_code'              => optional($this->currency)->code,
            'curr_symbol'            => optional($this->currency)->symbol,

            'charge_percentage'      => $this->charge_percentage,
            'charge_fixed'           => $this->charge_fixed,
            'subtotal'               => $this->subtotal,
            'total'                  => $this->total,
            'totalFees'              => $this->charge_percentage + $this->charge_fixed,

            'display_charge_percentage'=> formatNumber($this->charge_percentage, $this->currency_id),
            'display_charge_fixed'   => formatNumber($this->charge_fixed, $this->currency_id),
            'display_subtotal'       => moneyFormat(optional($this->currency)->symbol, formatNumber($this->subtotal, $this->currency_id)),
            'display_total'          => moneyFormat(optional($this->currency)->symbol, formatNumber($this->total, $this->currency_id)),
            'display_totalFess'      => moneyFormat(optional($this->currency)->symbol, formatNumber($this->charge_percentage + $this->charge_fixed, $this->currency_id)),

            'status'                 => $this->status,
            'email'                  => $this->email,
            'phone'                  => $this->phone,
            'transaction_created_at' => dateFormat($this->t_created_at, $this->user_id),

            'payment_method_id'      => $this->payment_method_id,
            'payment_method_name'    => optional($this->payment_method)->name,
            'company_name'           => settings('name'),
            'company_logo'           => logoPath(),

            'merchant_id'            => $this->merchant_id,
            'merchant_name'          => optional($this->merchant)->business_name,
            'logo'                   => optional($this->merchant)->logo,

            'note'                   => $this->note,
            'uuid'                   => $this->uuid,
            'transaction_reference_id' => $this->transaction_reference_id,

            'bank_id'                => $this->bank_id,
            'bank_name'              => optional($this->bank)->bank_name,
            'bank_logo'              => optional(optional($this->bank)->file)->filename
        ];
    }
}
