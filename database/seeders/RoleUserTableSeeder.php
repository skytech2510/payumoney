<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('role_user')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'user_type' => 'Admin',
            ],
        ]);
    }
}
