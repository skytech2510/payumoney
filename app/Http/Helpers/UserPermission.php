<?php
namespace App\Http\Helpers;

use DB;

class UserPermission
{
    public static function has_permission($user_id, $permissions = '')
    {
        $permissions = explode('|', $permissions);
        $permission_id = [];
        $i             = 0;

        $user_permissions = DB::table('permissions')->whereIn('name', $permissions)->get();
        foreach ($user_permissions as $value)
        {
            $permission_id[$i++] = $value->id;
        }

        $role = DB::table('role_user')->where('user_id', $user_id)->first();
        if (count($permission_id) && isset($role->role_id))
        {
            $has_permit = DB::table('permission_role')
                ->where('role_id', $role->role_id)
                ->whereIn('permission_id', $permission_id);
            return $has_permit->count();
        }
        else
        {
            return 0;
        }
    }
}
