<?php

namespace Modules\BlockIo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BlockIoMetasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
        $metas = [
            ['url' => 'crypto/send/blockio/{walletCurrencyCode}/{walletId}', 'title' => 'Crypto Send', 'description' => 'Crypto Send', 'keywords' => ''],
            ['url' => 'crypto/send/blockio/confirm', 'title' => 'Send Crypto Confirm', 'description' => 'Send Crypto Confirm', 'keywords' => ''],
            ['url' => 'crypto/send/blockio/success', 'title' => 'Send Crypto Success', 'description' => 'Send Crypto Success', 'keywords' => ''],
            ['url' => 'crypto/receive/blockio/{walletCurrencyCode}/{walletId}', 'title' => 'Crypto Receive', 'description' => 'Crypto Receive', 'keywords' => ''],
        ];

        \App\Models\Meta::insert($metas);
    }
}
