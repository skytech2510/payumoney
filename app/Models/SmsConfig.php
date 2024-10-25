<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsConfig extends Model
{
    protected $table = 'sms_configs';
    protected $fillable = ['type', 'credentials', 'status'];
}
