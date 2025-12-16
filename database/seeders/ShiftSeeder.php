<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⏰ Starting ShiftSeeder...');

        $shifts = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Shift Pagi',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'total_hours' => 8,
                'grace_period_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Shift Siang',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'total_hours' => 8,
                'grace_period_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Shift Malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'total_hours' => 8,
                'grace_period_minutes' => 15,
                'is_overnight' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Shift Reguler',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'total_hours' => 8,
                'grace_period_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('shifts')->insert($shifts);

        $this->command->info('✅ Created ' . count($shifts) . ' shifts');
    }
}
