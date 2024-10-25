<?php

namespace Modules\BlockIo\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BlockIoProviderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $blockioProvider = [
            'name' => 'BlockIo',
            'alias' => 'BlockIo',
            'description' => 'The world\'s easiest Bitcoin Wallet as a Service.',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        \App\Models\CryptoProvider::create($blockioProvider);
    }
}
