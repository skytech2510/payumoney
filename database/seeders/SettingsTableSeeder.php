<?php

namespace database\seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::truncate();

        Setting::insert([
            ['name' => 'name', 'value' => 'Pay Money', 'type' => 'general'],
            ['name' => 'logo', 'value' => '1532175849_logo.png', 'type' => 'general'],
            ['name' => 'favicon', 'value' => '1530689937_favicon.png', 'type' => 'general'],
            ['name' => 'head_code', 'value' => '', 'type' => 'general'],
            ['name' => 'default_currency', 'value' => 1, 'type' => 'general'],
            ['name' => 'allowed_wallets', 'value' => '2,3', 'type' => 'general'],
            ['name' => 'default_language', 'value' => 1, 'type' => 'general'],
            ['name' => 'site_key', 'value' => '', 'type' => 'recaptcha'],
            ['name' => 'secret_key', 'value' => '', 'type' => 'recaptcha'],
            ['name' => 'default_timezone', 'value' => 'Asia/Dhaka', 'type' => 'general'],
            ['name' => 'has_captcha', 'value' => 'Disabled', 'type' => 'general'],
            ['name' => 'login_via', 'value' => 'email_only', 'type' => 'general'],
            ['name' => 'admin_access_ip_setting', 'value' => 'Disabled', 'type' => 'admin_security'],
            ['name' => 'admin_access_ips', 'value' => '::1', 'type' => 'admin_security'],
            ['name' => 'exchange_enabled_api', 'value' => 'Disabled', 'type' => 'currency_exchange_rate'],
            ['name' => 'currency_converter_api_key', 'value' => NULL, 'type' => 'currency_exchange_rate'],
            ['name' => 'exchange_rate_api_key', 'value' => NULL, 'type' => 'currency_exchange_rate'],
            ['name' => 'crypto_compare_enabled_api', 'value' => 'Disabled', 'type' => 'crypto_compare_rate'],
            ['name' => 'crypto_compare_api_key', 'value' => '', 'type' => 'crypto_compare_rate'],
            
        ]);
    }
}
