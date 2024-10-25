<?php

namespace Database\Seeders\versions\v4_0_1;

use Illuminate\Database\Seeder;

class MetasTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('metas')->insert(['url' => 'privacy-policy', 'title' => 'Privacy Policy', 'description' => 'Privacy Policy', 'keywords' => '']);
    }
}
