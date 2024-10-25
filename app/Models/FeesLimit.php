<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeesLimit extends Model
{
    protected $table    = 'fees_limits';
    protected $fillable = [
        'currency_id',
        'transaction_type_id',
        'payment_method_id',
        'charge_percentage',
        'charge_fixed',
        'min_limit',
        'max_limit',
        'processing_time',
        'has_transaction',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function scopeTransactionType($query, $value)
    {
        return $query->where('transaction_type_id', $value);
    }

    public function scopeHasTransaction($query)
    {
        return $query->where('has_transaction', "Yes");
    }
}
