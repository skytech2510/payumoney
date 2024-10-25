<?php

namespace Modules\TatumIo\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TatumIoProviderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $tatumioProvider = [
            'name' => 'TatumIo',
            'alias' => 'TatumIo',
            'description' => 'The world\'s easiest Bitcoin Wallet as a Service.',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        \App\Models\CryptoProvider::create($tatumioProvider);
    }
}
