<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name', 'display_name', 'description',
    ];

    public static function permission_role($id)
    {
        return DB::table('permission_role')->where('role_id', $id)->pluck('permission_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'role_id');
    }

    /**
     * Available user roles
     *
     * @return array 
     */
    public function availableUserRoles() : array
    {
        $merchantRole = self::where(['user_type' => 'User', 'customer_type' => 'merchant', 'is_default' => 'Yes'])->first(['id']);
        $userRole     = self::where(['user_type' => 'User', 'customer_type' => 'user', 'is_default' => 'Yes'])->first(['id']);
        if ($merchantRole) {
            $types[] = "merchant";
        }
        if ($userRole) {
            $types[] = "user";
        }
        return $types;
    }
}
