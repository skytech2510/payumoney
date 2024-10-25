<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        Role::insert([
            [
                'id'            => 1,
                'name'          => 'Admin',
                'display_name'  => 'Admin',
                'description'   => 'Admin',
                'user_type'     => 'Admin',
                'customer_type' => 'user',
                'is_default'    => 'No',
            ],
            [
                'id'            => 2,
                'name'          => 'Default User',
                'display_name'  => 'Default User',
                'description'   => 'Default User',
                'user_type'     => 'User',
                'customer_type' => 'user',
                'is_default'    => 'Yes',
            ],
            [
                'id'            => 3,
                'name'          => 'Merchant Regular',
                'display_name'  => 'Merchant Regular',
                'description'   => 'Merchant Regular',
                'user_type'     => 'User',
                'customer_type' => 'merchant',
                'is_default'    => 'Yes',
            ],
        ]);
    }
}
