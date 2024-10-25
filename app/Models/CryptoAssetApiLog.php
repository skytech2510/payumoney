<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoAssetApiLog extends Model
{
    use HasFactory;

    public function getCryptoAssetapiLog($constraints, $selectOptions)
    {
        return $this->where($constraints)->first($selectOptions);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'object_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'object_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getBlockIoAssetapiLog($constraints, $selectOptions)
    {
        return $this->where($constraints)->first($selectOptions);
    }
}
