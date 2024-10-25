<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;//Authenticatable trait
use Illuminate\Auth\Passwords\CanResetPassword;//CanResetPassword trait
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;

// class Admin extends Model
class Admin extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    protected $table    = 'admins';

    protected $fillable = [
        'username',
        'first_name',
    	'last_name',
    	'email',
    	'password',
    	'role_id',
    	'picture',
    ];

    protected $hidden   = ['password', 'remember_token'];

    //Admin - hasOne - log
    public function activity_log()
    {
        return $this->hasOne(ActivityLog::class);
    }

    public function disputeDiscussion()
    {
        return $this->hasMany(DisputeDiscussion::class,'user_id','id');
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class,'admin_id');
    }

    public function file()
    {
        return $this->hasOne(File::class,'admin_id');
    }

    public function ticket_reply()
    {
        return $this->hasOne(TicketReply::class,'admin_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'admin_id');
    }
}
