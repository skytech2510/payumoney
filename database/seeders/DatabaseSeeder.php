<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call(NotificationTypesTableSeeder::class);
        $this->command->info('Notification Types Seeded Successfully!');

        $this->call(NotificationSettingsTableSeeder::class);
        $this->command->info('Notification Settings Seeded Successfully!');

        $this->call(AppStoreCredentialsTableSeeder::class);
        $this->command->info('App Store Credentials Seeded Successfully!');

        $this->call(CountriesTableSeeder::class);
        $this->command->info('Countries Seeded Successfully!');

        $this->call(CurrenciesTableSeeder::class);
        $this->command->info('Currencies Seeded Successfully!');

        $this->call(EmailConfigsTableSeeder::class);
        $this->command->info('Email Configurations Seeded Successfully!');

        $this->call(SmsConfigsTableSeeder::class);
        $this->command->info('Sms Configurations Seeded Successfully!');

        $this->call(EmailTemplatesTableSeeder::class);
        $this->command->info('Email Templates Seeded Successfully!');

        $this->call(LanguagesTableSeeder::class);
        $this->command->info('Languages Seeded Successfully!');

        $this->call(MetasTableSeeder::class); //not done
        $this->command->info('Metas Seeded Successfully!');

        $this->call(PaymentMethodsTableSeeder::class);
        $this->command->info('Payment Methods Seeded Successfully!');

        $this->call(MerchantGroupsTableSeeder::class);
        $this->command->info('Merchant Groups Seeded Successfully!');

        $this->call(RolesTableSeeder::class);
        $this->command->info('Roles Seeded Successfully!');

        $this->call(PermissionsTableSeeder::class);
        $this->command->info('Permissions Seeded Successfully!');

        $this->call(PermissionsRolesTableSeeder::class);
        $this->command->info('Permissions Roles Seeded Successfully!');

        $this->call(PreferencesTableSeeder::class);
        $this->command->info('Preferences Seeded Successfully!');

        $this->call(ReasonsTableSeeder::class);
        $this->command->info('Reasons Seeded Successfully!');

        $this->call(SettingsTableSeeder::class);
        $this->command->info('Settings Seeded Successfully!');

        $this->call(TicketStatusesTableSeeder::class);
        $this->command->info('Ticket Statuses Seeded Successfully!');

        $this->call(TimeZonesTableSeeder::class);
        $this->command->info('TimeZones Seeded Successfully!');

        $this->call(TransactionTypesTableSeeder::class);
        $this->command->info('Transaction Types Seeded Successfully!');

        $this->call(FeesLimitsTableSeeder::class); //new
        $this->command->info('Fees Limit Seeded Successfully!');

        $this->call(SocialsTableSeeder::class);
        $this->command->info('Socials Seeded Successfully!');

        $this->call(OauthClientsTableSeeder::class);
        $this->command->info('Oauth Clients Seeded Successfully!');

        $this->call(OauthPersonalAccessClientsTableSeeder::class);
        $this->command->info('Oauth Personal Access Clients Seeded Successfully!');

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
