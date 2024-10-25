<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class MetasTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('metas')->insert(
            [
                ['url' => 'crypto/send/tatumio/{walletCurrencyCode}/{walletId}', 'title' => 'Crypto Send', 'description' => 'Crypto Send', 'keywords' => ''],
                ['url' => 'crypto/send/tatumio/confirm', 'title' => 'Send Crypto Confirm', 'description' => 'Send Crypto Confirm', 'keywords' => ''],
                ['url' => 'crypto/send/tatumio/success', 'title' => 'Send Crypto Success', 'description' => 'Send Crypto Success', 'keywords' => ''],
                ['url' => 'crypto/receive/tatumio/{walletCurrencyCode}/{walletId}','title' => 'Crypto Receive','description' => 'Crypto Receive','keywords' => ''],
                ['url' => 'crypto/receive/tatumio/{walletCurrencyCode}/{walletId}','title' => 'Crypto Receive','description' => 'Crypto Receive','keywords' => ''],
                ['url' => 'crypto/receive/tatumio/{walletCurrencyCode}/{walletId}','title' => 'Crypto Receive','description' => 'Crypto Receive','keywords' => ''],
                ['url' => 'deposit/confirm', 'title' => 'Deposit confirm', 'description' => 'Deposit confirm', 'keyword' => NULL],
                ['url' => 'deposit/success', 'title' => 'Deposit Success', 'description' => 'Deposit Success', 'keyword' => NULL],
            ]
        );
    }
}
