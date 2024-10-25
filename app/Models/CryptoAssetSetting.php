<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoAssetSetting extends Model
{
    use HasFactory;

    protected $fillable = ['status'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    
    public function cryptoProvider()
    {
        return $this->belongsTo(CryptoProvider::class, 'crypto_provider_id');
    }

    public function getCryptoAssetSetting($constraints, $selectOptions)
    {
        return $this->where($constraints)->first($selectOptions);
    }
}
