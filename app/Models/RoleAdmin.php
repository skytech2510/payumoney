<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleAdmin extends Model
{
    protected $table = 'role_admin';
	protected $fillable = ['role_id', 'admin_id'];
	public $timestamps = false;
}
