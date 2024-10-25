<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OauthClient;
class OauthClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        OauthClient::truncate();
        OauthClient::insert([
            [
                'id'                     => 1,
                'user_id'                => null,
                'name'                   => 'Laravel Personal Access Client',
                'secret'                 => 'agkL4ISxlzHE5z2zS2vwqZqqoF7ker3HMXo7De3v',
                'redirect'               => 'http://localhost',
                'personal_access_client' => 1,
                'password_client'        => 0,
                'revoked'                => 0,
            ],
            [
                'id'                     => 2,
                'user_id'                => null,
                'name'                   => 'Laravel Password Grant Client',
                'secret'                 => 'TwF6YvwSCLuVejXhUQCAqMaPAqhHZ29sEhhFfsM9',
                'redirect'               => 'http://localhost',
                'personal_access_client' => 0,
                'password_client'        => 1,
                'revoked'                => 0,
            ],
            [
                'id'                     => 3,
                'user_id'                => null,
                'name'                   => 'Laravel Personal Access Client',
                'secret'                 => 'YWG63Yjp0bcf7iL45MgK75Yc5Tq18KS9rcv8ltBM',
                'redirect'               => 'http://localhost',
                'personal_access_client' => 1,
                'password_client'        => 0,
                'revoked'                => 0,
            ],
        ]);
    }
}
