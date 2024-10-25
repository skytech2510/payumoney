<?php

namespace Modules\BlockIo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BlockIoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(BlockIoProviderTableSeeder::class);
        $this->call(BlockIoPaymentMethodTableSeeder::class);
        $this->call(BlockIoMetasTableSeeder::class);
    }
}
