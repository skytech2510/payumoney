<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class FeesResource extends JsonResource
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
            'feesFixed'                 => $this->charge_fixed,
            'feesPercentage'            => $this->charge_percentage,
            'totalFees'                 => $this->total_fees,
            'amount'                    => $this->amount,
            'totalAmount'               => $this->total_amount,
            'formattedFeesFixed'        => formatNumber($this->charge_fixed, $this->currency_id),
            'formattedFeesPercentage'   => formatNumber($this->charge_percentage, $this->currency_id) . '%',
            'formattedTotalFees'        => formatNumber($this->total_fees, $this->currency_id),
            'formattedAmount'           => formatNumber($this->amount, $this->currency_id),
            'formattedTotalAmount'      => formatNumber($this->total_amount, $this->currency_id),
            'currencyId'                => $this->currency_id,
            'currencyType'              => optional($this->currency)->type,
            'currencyCode'              => optional($this->currency)->code,
            'currencySymbol'            => optional($this->currency)->symbol
        ];
    }
}
