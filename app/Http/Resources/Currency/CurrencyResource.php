<?php

namespace App\Http\Resources\Currency;

use Illuminate\Http\Resources\Json\Resource;

class CurrencyResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'symbol'        => $this->symbol,
            'code'          => $this->code,
            'status'        => $this->status,
        ];
    }

    public function with($request)
    {
        return [
            'version' => '1.0',
        ];
    }
}
