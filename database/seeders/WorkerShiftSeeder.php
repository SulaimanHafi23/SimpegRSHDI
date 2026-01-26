<?php

namespace Database\Seeders;

use App\Models\WorkerShiftSchedule;
use App\Models\Worker;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WorkerShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Worker Shifts...');

        $workers = Worker::all();
        $shifts = Shift::where('is_active', true)->get();

        if ($shifts->isEmpty()) {
            $this->command->warn('No active shifts found. Skipping worker shift seeding.');
            return;
        }

        // Generate shift schedules for the next 30 days
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        foreach ($workers as $worker) {
            // Assign a default shift pattern for the worker
            $primaryShift = $shifts->random();
            
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Rotate shifts every 7 days for some variety
                $weekNumber = $currentDate->diffInWeeks($startDate);
                $assignedShift = $weekNumber % 2 == 0 ? $primaryShift : $shifts->random();
                
                // Skip Sundays (day off)
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    WorkerShiftSchedule::create([
                        'worker_id' => $worker->id,
                        'shift_id' => $assignedShift->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'is_override' => false,
                        'notes' => null,
                    ]);
                }

                $currentDate->addDay();
            }
        }

        // Add some shift overrides (special cases)
        $this->addShiftOverrides($workers, $shifts, $startDate, $endDate);

        $this->command->info('✅ Worker Shifts seeded successfully!');
    }

    private function addShiftOverrides($workers, $shifts, $startDate, $endDate): void
    {
        // Add 5-10 random shift overrides
        $overrideCount = rand(5, 10);
        
        for ($i = 0; $i < $overrideCount; $i++) {
            $worker = $workers->random();
            $shift = $shifts->random();
            $date = Carbon::parse($startDate)->addDays(rand(0, 30))->format('Y-m-d');
            
            // Check if schedule exists for this date
            $existingSchedule = WorkerShiftSchedule::where('worker_id', $worker->id)
                ->where('date', $date)
                ->first();
            
            if ($existingSchedule) {
                $existingSchedule->update([
                    'shift_id' => $shift->id,
                    'is_override' => true,
                    'notes' => 'Penggantian shift karena keperluan operasional',
                ]);
            }
        }
    }
}
