<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::truncate();
        Currency::insert([
            [
                'type'                   => 'fiat',
                'name'                   => 'US Dollar',
                'symbol'                 => '$',
                'code'                   => 'USD',
                'rate'                   => '1',
                'logo'                   => 'icons8-us-dollar-64.png',
                'exchange_from'          => 'local',
                'default'                => '1',
                'status'                 => 'Active',
            ],
            [
                'type'                   => 'fiat',
                'name'                   => 'Pound Sterling',
                'symbol'                 => '£',
                'code'                   => 'GBP',
                'rate'                   => '0.75',
                'logo'                   => 'icons8-british-pound-64.png',
                'exchange_from'          => 'local',
                'default'                => '0',
                'status'                 => 'Active',
            ],
            [
                'type'                   => 'fiat',
                'name'                   => 'Euro',
                'symbol'                 => '€',
                'code'                   => 'EUR',
                'rate'                   => '0.85',
                'logo'                   => 'icons8-euro-64.png',
                'exchange_from'          => 'local',
                'default'                => '0',
                'status'                 => 'Active',
            ],
            [
                'type'                   => 'crypto',
                'name'                   => 'Bitcoin',
                'symbol'                 => '฿',
                'code'                   => 'BTC',
                'rate'                   => '0.00',
                'logo'                   => 'bitcoin_crypto.png',
                'exchange_from'          => 'local',
                'default'                => '0',
                'status'                 => 'Inactive',
            ],
            [
                'type'                   => 'crypto',
                'name'                   => 'Litecoin',
                'symbol'                 => 'Ł',
                'code'                   => 'LTC',
                'rate'                   => '0.00',
                'logo'                   => '1660982923.png',
                'exchange_from'          => 'local',
                'default'                => '0',
                'status'                 => 'Inactive',
            ],
        ]);
    }
}
