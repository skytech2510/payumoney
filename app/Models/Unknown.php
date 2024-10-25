<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unknown extends Model
{
    protected $table = 'unknowns';

    protected $fillable = [
        'transaction_type',
        'transaction_reference_id',
        'phone',
        'email',
    ];

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }
}
