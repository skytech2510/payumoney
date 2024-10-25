<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('payment_methods')->insert(
            [
                ['name' => 'TatumIo', 'status' => 'Active'],
                ['name' => 'Coinbase', 'status' => 'Active']
            ]

        );
    }
}
