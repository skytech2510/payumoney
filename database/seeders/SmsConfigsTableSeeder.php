<?php

namespace Database\Seeders;

use App\Models\SmsConfig;
use Illuminate\Database\Seeder;

class SmsConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SmsConfig::truncate();
        SmsConfig::insert([
            [
                'id'         => 1,
                'type'       => 'twilio',
                'status'     => 'Inactive',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],

            [
                'id'         => 2,
                'type'       => 'nexmo',
                'status'     => 'Inactive',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);
    }
}
