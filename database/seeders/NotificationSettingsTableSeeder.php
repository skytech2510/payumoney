<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use Illuminate\Database\Seeder;

class NotificationSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotificationSetting::truncate();
        NotificationSetting::insert([
            [
                'id'                   => '1',
                'notification_type_id' => '1',
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '2',
                'notification_type_id' => '2',
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '3',
                'notification_type_id' => '3',
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '4',
                'notification_type_id' => '4',
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '5',
                'notification_type_id' => '5',
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '6',
                'notification_type_id' => '6',
                'recipient_type'       => 'email',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '7',
                'notification_type_id' => '1',
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '8',
                'notification_type_id' => '2',
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '9',
                'notification_type_id' => '3',
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '10',
                'notification_type_id' => '4',
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '11',
                'notification_type_id' => '5',
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

            [
                'id'                   => '12',
                'notification_type_id' => '6',
                'recipient_type'       => 'sms',
                'recipient'            => NULL,
                'status'               => 'No',
            ],

        ]);
    }
}
