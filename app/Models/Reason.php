<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $table    = 'reasons';
    protected $fillable = [
        'title',
    ];

    public function dispute()
    {
        return $this->hasOne(Dispute::class, 'reason_id', 'id');
    }
}
