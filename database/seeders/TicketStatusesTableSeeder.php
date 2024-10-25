<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Seeding updated on july 15,2018
        TicketStatus::truncate();
        TicketStatus::insert([
            [
                'id'         => 1,
                'name'       => 'Open',
                'color'      => '00a65a',
                'is_default' => 0,
            ],
            [
                'id'         => 2,
                'name'       => 'In Progress',
                'color'      => '3c8dbc',
                'is_default' => 1,
            ],
            [
                'id'         => 3,
                'name'       => 'Hold',
                'color'      => 'f39c12',
                'is_default' => 0,
            ],
            [
                'id'         => 4,
                'name'       => 'Closed',
                'color'      => 'dd4b39',
                'is_default' => 0,
            ],
        ]);
    }
}
