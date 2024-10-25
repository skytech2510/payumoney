<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = [
            ['id' => 1, 'name' => 'Deposit'],
            ['id' => 2, 'name' => 'Withdrawal'],
            ['id' => 3, 'name' => 'Transferred'],
            ['id' => 4, 'name' => 'Received'],
            ['id' => 5, 'name' => 'Exchange_From'],
            ['id' => 6, 'name' => 'Exchange_To'],
            ['id' => 7, 'name' => 'Request_Sent'],
            ['id' => 8, 'name' => 'Request_Received'],
            ['id' => 9, 'name' => 'Payment_Sent'],
            ['id' => 10, 'name' => 'Payment_Received'],
            ['id' => 11, 'name' => 'Crypto_Sent'],
            ['id' => 12, 'name' => 'Crypto_Received'],
        ];
        DB::table('transaction_types')->insert($type);
    }
}
