<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\Resource;

class TransactionResource extends Resource
{
    public function toArray($request)
    {
        return parent::toArray($request);

        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'subtotal' => $this->subtotal,
            'fee'      => $this->fee,
            'total '   => $this->total,
        ];
    }

    public function with($request)
    {
        return [
        ];
    }

    // public function withResponse($request, $response)
    // {
    //     $response->header([
    //         'Accept'       => 'application/json',
    //         'Content-Type' => 'application/json',
    //     ]);
    // }
}
