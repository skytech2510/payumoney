<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantApp extends Model
{
    protected $fillable = ['client_id', 'client_secret'];

    protected $hidden = ['client_id', 'client_secret'];

    public function accessToken()
    {
        return $this->hasOne(AppToken::class, 'app_id', 'id');
    }

    public function transactionInfo()
    {
        return $this->hasMany(AppTransactionsInfo::class, 'app_id', 'id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
    }
}
