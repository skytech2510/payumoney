<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::truncate();

        Language::insert([
            ['id' => '1', 'name' => 'English', 'short_name' => 'en', 'default' => '1', 'flag' => '1530358989.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '2', 'name' => 'عربى', 'short_name' => 'ar', 'default' => '0', 'flag' => '1530359409.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '3', 'name' => 'Français', 'short_name' => 'fr', 'default' => '0', 'flag' => '1530359431.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '4', 'name' => 'Português', 'short_name' => 'pt', 'default' => '0', 'flag' => '1530359450.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '5', 'name' => 'Русский', 'short_name' => 'ru', 'default' => '0', 'flag' => '1530359474.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '6', 'name' => 'Español', 'short_name' => 'es', 'default' => '0', 'flag' => '1530360151.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '7', 'name' => 'Türkçe', 'short_name' => 'tr', 'default' => '0', 'flag' => '1530696845.png', 'deletable' => 'No', 'status' => 'Active'],

            ['id' => '8', 'name' => '中文 (繁體)', 'short_name' => 'ch', 'default' => '0', 'flag' => '1531227913.png', 'deletable' => 'No', 'status' => 'Active'],
        ]);
    }
}
