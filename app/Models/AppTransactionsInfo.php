<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppTransactionsInfo extends Model
{
    protected $table    = 'app_transactions_infos';
    protected $fillable = ['app_id', 'payment_method', 'amount', 'currency', 'success_url', 'cancel_url', 'grant_id', 'token', 'expires_in', 'status'];

    public function app()
    {
        return $this->belongsTo(MerchantApp::class, 'app_id', 'id');
    }
}
