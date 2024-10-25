<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $table    = 'transaction_types';
    protected $fillable = [
        'name',
    ];

    public function fees_limit()
    {
        return $this->hasOne(FeesLimit::class, 'transaction_type_id', 'id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
