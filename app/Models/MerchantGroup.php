<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantGroup extends Model
{
    protected $table = 'merchant_groups';

    protected $fillable = ['name', 'description', 'fee', 'is_default'];

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'merchant_group_id');
    }
}
