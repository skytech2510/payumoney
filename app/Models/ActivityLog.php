<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table   = 'activity_logs';

    protected $fillable = [
        'user_id',
        'type',
        'ip_address',
        'browser_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    /**
     * Store activity log
     *
     * @param int $user_id
     * @param string $type
     * @param string $ipAddress
     * @param string $userAgent
     * @return void
     */
    public static function createActivityLog($user_id = null, $type = 'User', $ipAddress, $userAgent)
    {
        $log                 = new self();
        $log->user_id        = (int) $user_id;
        $log->type           = $type;
        $log->ip_address     = $ipAddress;
        $log->browser_agent  = $userAgent;
        $log->save();
    }
}
