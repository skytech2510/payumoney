<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
	protected $table = 'notification_types';

	protected $fillable = ['name', 'alias', 'status'];

    public function notification_settings()
    {
        return $this->hasMany(NotificationSetting::class);
    }
}
