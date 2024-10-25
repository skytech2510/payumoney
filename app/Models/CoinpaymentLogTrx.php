<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinpaymentLogTrx extends Model
{
    use HasFactory;

    protected $table = 'coinpayment_log_trxes';
    
    protected $guarded = ['id'];
    protected $dates   = [
        'payment_created_at',
        'expired',
        'confirmation_at',
    ];
    protected $hidden = [
        'created_at', 'updated_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function merchant()
    {
        return $this->belongsTo(MerchantPayment::class, 'merchant_id');
    }
}
