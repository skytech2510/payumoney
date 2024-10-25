<?php

namespace Database\Seeders;

use App\Models\Reason;
use Illuminate\Database\Seeder;

class ReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Reason::truncate();
        Reason::insert([
            [
                'id'   => 1,
                'title' => 'I have not received the goods',
            ],
            [
                'id'   => 2,
                'title' => 'Description does not match with product',
            ],
        ]);
    }
}
