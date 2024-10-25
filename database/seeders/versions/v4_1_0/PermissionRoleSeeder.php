<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        $cachePermission = \App\Models\Permission::where('group', 'Cache Clear')->get(['id', 'user_type']);


        foreach ($cachePermission as $permission) {
            if ($permission->user_type == 'Admin') {
                $adminPermissionRole[] = [
                    'role_id' => 1,
                    'permission_id' => $permission->id,
                ];
            }
        }

        \App\Models\PermissionRole::insert($adminPermissionRole);

    }
}


