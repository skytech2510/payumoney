<?php

namespace Modules\TatumIo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TatumIoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(TatumIoProviderTableSeeder::class);
        $this->call(TatumIoPaymentMethodTableSeeder::class);
        $this->call(TatumIoMetasTableSeeder::class);

    }
}
