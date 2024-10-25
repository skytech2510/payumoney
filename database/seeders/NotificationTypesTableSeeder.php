<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotificationType::truncate();
        NotificationType::insert([
            [
                'id'         => '1',
                'name'       => 'Deposit',
                'alias'      => 'deposit',
                'status'     => 'Active',
            ],

            [
                'id'         => '2',
                'name'       => 'Withdraw',
                'alias'      => 'withdraw',
                'status'     => 'Active',
            ],

            [
                'id'         => '3',
                'name'       => 'Send',
                'alias'      => 'send',
                'status'     => 'Active',
            ],

            [
                'id'         => '4',
                'name'       => 'Request',
                'alias'      => 'request',
                'status'     => 'Active',
            ],

            [
                'id'         => '5',
                'name'       => 'Exchange',
                'alias'      => 'exchange',
                'status'     => 'Active',
            ],

            [
                'id'         => '6',
                'name'       => 'Payment',
                'alias'      => 'payment',
                'status'     => 'Active',
            ],
        ]);
    }
}
