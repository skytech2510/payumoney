<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class CryptoProviderSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('crypto_providers')->insert([
            'name' => 'TatumIo',
            'alias' => 'TatumIo',
            'description' => 'Tatum offers a flexible framework to build, run, and scale blockchain apps fast.',
            'logo' => NULL,
            'subscription_details' => '',
            'status' => 'Active',
        ]);
    }
}
