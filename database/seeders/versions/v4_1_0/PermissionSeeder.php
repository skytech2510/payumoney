<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('permissions')->insert(
            [
                [
                    'group' => 'Cache Clear',
                    'name' => 'view_cache_clear',
                    'display_name' => 'View Cache Clear',
                    'description' => 'View Cache Clear',
                    'user_type' => 'Admin',
                ],
                [
                    'group' => 'Cache Clear',
                    'name' => 'add_cache_clear',
                    'display_name' => 'Add Cache Clear',
                    'description' => 'Add Cache Clear',
                    'user_type' => 'Admin',
                ],
                [
                    'group' => 'Cache Clear',
                    'name' => 'edit_cache_clear',
                    'display_name' => 'Edit Cache Clear',
                    'description' => 'Edit Cache Clear',
                    'user_type' => 'Admin',
                ],
                [
                    'group' => 'Cache Clear',
                    'name' => 'delete_cache_clear',
                    'display_name' => 'Delete Cache Clear',
                    'description' => 'Delete Cache Clear',
                    'user_type' => 'Admin',
                ]
            ]

        );
    }
}


