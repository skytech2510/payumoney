<?php

namespace Database\Seeders;

use App\Models\EmailConfig;
use Illuminate\Database\Seeder;

class EmailConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailConfig::truncate();
        EmailConfig::insert([
            [
                'email_protocol'   => 'sendmail',
                'email_encryption' => 'tls',
                'smtp_host'        => '',
                'smtp_port'        => '587',
                'smtp_email'       => '',
                'smtp_username'    => '',
                'smtp_password'    => '',
                'from_address'     => '',
                'from_name'        => '',
                'status'           => '1',
            ],
        ]);
    }
}
