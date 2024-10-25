<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoProvider extends Model
{
    use HasFactory;

    public function cryptoAssetSettings()
    {
        return $this->hasMany(CryptoAssetSetting::class, 'crypto_provider_id');
    }

    public static function getStatus($name = null)
    {
        return self::where('alias', $name)->value('status');
    }
}
