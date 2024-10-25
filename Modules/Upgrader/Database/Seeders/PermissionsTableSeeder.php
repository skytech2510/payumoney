<?php

namespace Modules\Upgrader\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \App\Models\Permission::insert([
            ['group' => 'System Upgrader', 'name' => 'view_system_upgrader', 'display_name' => 'View System Upgrader', 'description' => 'View System Upgrader', 'user_type' => 'Admin'],
            ['group' => 'System Upgrader', 'name' => 'add_system_upgrader', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
            ['group' => 'System Upgrader', 'name' => 'edit_system_upgrader', 'display_name' => 'Edit System Upgrader', 'description' => 'Edit System Upgrader', 'user_type' => 'Admin'],
            ['group' => 'System Upgrader', 'name' => 'delete_system_upgrader', 'display_name' => null, 'description' => null, 'user_type' => 'Admin'],
        ]);
    }
}
