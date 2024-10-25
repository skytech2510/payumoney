<?php
namespace Database\Seeders\versions\v4_1_0;


use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


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
        $this->call(MetasTableSeeder::class);
        $this->call(CryptoProviderSeeder::class);
        $this->call(PaymentMethodsTableSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(PermissionRoleSeeder::class);
        $this->call(EmailTemplateSeeder::class);
        $this->call(FeesLimitSeeder::class);
    }
}
