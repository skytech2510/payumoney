<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('admins')->insert([
            [
                'id'         => 1,
                'role_id'    => 1,
                'first_name' => 'admin',
                'last_name'  => 'techvill',
                'email'      => 'admin@techvill.net',
                'password'   => \Hash::make('123456'),
                'status'     => 'Active',
            ],
        ]);
    }
}
