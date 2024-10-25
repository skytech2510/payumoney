<?php

namespace Database\Seeders;

namespace database\seeders;
use App\Models\Preference;
use Illuminate\Database\Seeder;

class PreferencesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pre[0]['category'] = 'preference';
        $pre[0]['field']    = 'row_per_page';
        $pre[0]['value']    = '25';

        $pre[1]['category'] = 'preference';
        $pre[1]['field']    = 'date_format';
        $pre[1]['value']    = '1';

        $pre[2]['category'] = 'preference';
        $pre[2]['field']    = 'date_sepa';
        $pre[2]['value']    = '-';

        $pre[4]['category'] = 'company';
        $pre[4]['field']    = 'site_short_name';
        $pre[4]['value']    = 'PM';

        $pre[5]['category'] = 'preference';
        $pre[5]['field']    = 'percentage';
        $pre[5]['value']    = '2';

        $pre[6]['category'] = 'preference';
        $pre[6]['field']    = 'quantity';
        $pre[6]['value']    = '0';

        $pre[7]['category'] = 'preference';
        $pre[7]['field']    = 'date_format_type';
        $pre[7]['value']    = 'dd-mm-yyyy';

        $pre[8]['category'] = 'company';
        $pre[8]['field']    = 'company_name';
        $pre[8]['value']    = 'Pay Money';

        $pre[9]['category'] = 'company';
        $pre[9]['field']    = 'company_email';
        $pre[9]['value']    = 'admin@techvill.net';

        $pre[10]['category'] = 'company';
        $pre[10]['field']    = 'dflt_lang';
        $pre[10]['value']    = 'en';

        $pre[11]['category'] = 'preference';
        $pre[11]['field']    = 'default_money_format';
        $pre[11]['value']    = '&nbsp;&#36;';

        $pre[12]['category'] = 'preference';
        $pre[12]['field']    = 'money_format';
        $pre[12]['value']    = 'before';

        $pre[13]['category'] = 'preference';
        $pre[13]['field']    = 'decimal_format_amount';
        $pre[13]['value']    = '2';

        $pre[14]['category'] = 'preference';
        $pre[14]['field']    = 'thousand_separator';
        $pre[14]['value']    = ',';

        $pre[15]['category'] = 'preference';
        $pre[15]['field']    = 'dflt_timezone';
        $pre[15]['value']    = 'Asia/Dhaka';

        $pre[16]['category'] = 'preference';
        $pre[16]['field']    = 'verification_mail';
        $pre[16]['value']    = 'Disabled';

        $pre[17]['category'] = 'preference';
        $pre[17]['field']    = 'phone_verification';
        $pre[17]['value']    = 'Disabled';

        $pre[18]['category'] = 'preference';
        $pre[18]['field']    = 'two_step_verification';
        $pre[18]['value']    = 'disabled';

        $pre[19]['category'] = 'preference';
        $pre[19]['field']    = 'processed_by';
        $pre[19]['value']    = 'email';

        $pre[20]['category'] = 'preference';
        $pre[20]['field']    = 'decimal_format_amount_crypto';
        $pre[20]['value']    = '8';

        $pre[21]['category'] = 'preference';
        $pre[21]['field']    = 'admin_url_prefix';
        $pre[21]['value']    = config('paymoney.prefix');

        $pre[22]['category'] = 'preference';
        $pre[22]['field']    = 'file_size';
        $pre[22]['value']    = '2';

        Preference::truncate();
        Preference::insert($pre);
    }
}
