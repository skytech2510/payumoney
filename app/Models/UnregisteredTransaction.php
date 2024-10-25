<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnregisteredTransaction extends Model
{
    protected $table = 'unregistered_transactions';

    protected $fillable = [
        'transaction_type',
        'transaction_reference_id',
        'phone',
        'email',
    ];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transaction_reference_id');
    }

    public function request_payment()
    {
        return $this->belongsTo(RequestPayment::class, 'transaction_reference_id');
    }
}
