<?php

namespace Database\Seeders\versions\v4_1_0;

use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('email_templates')->insert(
            [
                [
                    'language_id' => 1,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => 'New merchant Creation Notification',
                    'body' => 'Hi <b>{admin}</b>,<br><br>A new merchant has been created by <b>{user}</b></br><br><br><b><u><i>Here’s a brief overview of the merchant:</i></u></b><br><br><b><u>Created at:</u></b> {created_at}<br><br><b><u>Merchant:</u></b> {business_name}<br><br><b><u>Site URL:</u></b> {site_url}<br><br><b><u>Currency:</u></b> {code}<br><br><b><u>Type:</u></b> {merchant_type}<br><br><b><u>Message:</u></b> {message}<br><br>If you have any questions, please feel free to reply to this email.<br><br>Regards,<br><b>{soft_name}</b>',
                    'lang' => 'en',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 2,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'ar',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 3,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'fr',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 4,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'pt',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 5,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'ru',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 6,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'es',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 7,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'tr',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ],

                [
                    'language_id' => 8,
                    'name' => 'Notify Admin on Merchant Creation',
                    'alias' => 'notify-admin-on-merchant-creation',
                    'subject' => NULL,
                    'body' => NULL,
                    'lang' => 'ch',
                    'type' => 'email',
                    'status' => 'Active',
                    'group' => 'Merchant Payment',
                ]

            ]

        );
    }
}





