<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
	protected $table = 'device_logs';

	protected $fillable = ['user_id', 'browser_fingerprint', 'browser_agent', 'ip'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
