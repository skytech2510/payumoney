<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class FeesLimitSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $pm = \App\Models\PaymentMethod::where('name', 'Coinbase')->first();

        \DB::table('fees_limits')->insert(

            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => $pm->id,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ]
        );
    }
}
