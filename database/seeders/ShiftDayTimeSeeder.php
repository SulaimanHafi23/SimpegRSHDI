<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\ShiftDayTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShiftDayTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('shift_day_times')) {
            $this->command->warn('shift_day_times table not found. Skipping ShiftDayTimeSeeder.');
            return;
        }

        $this->command->info('🗓️  Seeding shift day times...');

        $shifts = Shift::all();
        if ($shifts->isEmpty()) {
            $this->command->warn('No shifts found. Skipping ShiftDayTimeSeeder.');
            return;
        }

        $days = [0, 1, 2, 3, 4, 5, 6];

        foreach ($shifts as $shift) {
            foreach ($days as $day) {
                ShiftDayTime::updateOrCreate(
                    ['shift_id' => $shift->id, 'day_of_week' => $day],
                    ['start_time' => $shift->start_time, 'end_time' => $shift->end_time]
                );
            }
        }

        $this->command->info('✅ Shift day times seeded.');
    }
}
