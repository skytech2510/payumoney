<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OauthPersonalAccessClient;

class OauthPersonalAccessClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        OauthPersonalAccessClient::truncate();
        OauthPersonalAccessClient::insert([
            [
                'id'                     => 1,
                'client_id'                => 1,
            ],
            [
                'id'                     => 2,
                'client_id'                => 2,
            ],
            [
                'id'                     => 3,
                'client_id'                => 3,
            ],
        ]);
    }
}
