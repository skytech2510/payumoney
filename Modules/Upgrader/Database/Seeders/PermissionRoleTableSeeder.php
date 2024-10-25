<?php

namespace Modules\Upgrader\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $systemUpgraderPermissions = \App\Models\Permission::whereIn('group', config('upgrader.permission_group'))->get(['id', 'user_type']);

        foreach ($systemUpgraderPermissions as $permission) {
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
