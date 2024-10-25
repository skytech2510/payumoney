<?php

namespace App\Console\Commands;

use Database\Seeders\AdminTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'product migration and seeding in one command for development';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        // Main app migrate and seed
        $this->call('migrate:fresh');
        $this->call('db:seed');

        // Admin user table seed
        $this->info('Admin Table Seeding...');
        $this->call('db:seed', ['--class' => AdminTableSeeder::class]);
        $this->info('Admin Table Seeding completed.');

        // Assing role to the admin Seed
        $this->info('RoleUser Table Seeding...');
        $this->call('db:seed', ['--class' => RoleUserTableSeeder::class]);
        $this->info('RoleUser Table Seeding completed.');

        // Core addon seed
        $this->moduleSeed();
        $this->call('optimize:clear');

    }


    protected function moduleSeed()
    {
        $this->warn('Module Seeding: ');

        foreach ($this->modulesName() as $module) {
            Artisan::call('module:seed ' . $module);
            $this->line('   ✔ ' . $module);
        }

        $this->info('Module seeding completed successfully.');
    }

    protected function modulesName()
    {
        $moduels = [];

        foreach (\Nwidart\Modules\Facades\Module::getOrdered() as $module) {
            array_push($moduels, $module->getName());
        }
        return $moduels;
    }
}
