<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'total_hours' => 8,
                'description' => 'Shift pagi',
            ],
            [
                'name' => 'Siang',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
                'total_hours' => 8,
                'description' => 'Shift siang',
            ],
            [
                'name' => 'Malam',
                'start_time' => '23:00:00',
                'end_time' => '07:00:00',
                'total_hours' => 8,
                'description' => 'Shift malam',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
