<?php

namespace Database\Seeders;

use App\Models\MerchantGroup;
use Illuminate\Database\Seeder;

class MerchantGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // MerchantGroup::truncate();
        MerchantGroup::insert([
            ['id' => 1, 'name' => 'Premium', 'description' => 'This is the premium merchant group', 'fee' => 0.51234567, 'fee_bearer' => 'Merchant', 'is_default' => 'No'],
            ['id' => 2, 'name' => 'Gold', 'description' => 'This is the gold merchant group', 'fee' => 1.99933344, 'fee_bearer' => 'Merchant', 'is_default' => 'No'],
            ['id' => 3, 'name' => 'Silver', 'description' => 'This is the silver merchant group', 'fee' => 1.50000044, 'fee_bearer' => 'Merchant', 'is_default' => 'Yes'],
            ['id' => 4, 'name' => 'Bronze', 'description' => 'This is the bronze merchant group', 'fee' => 2.77711194, 'fee_bearer' => 'Merchant', 'is_default' => 'No'],
        ]);
    }
}
