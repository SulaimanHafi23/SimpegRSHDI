<?php

namespace Database\Seeders;

use App\Models\WorkerShift;
use App\Models\ShiftOverride;
use App\Models\Worker;
use App\Models\Shift;
use App\Models\User;
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
        $users = User::all();

        if ($shifts->isEmpty()) {
            $this->command->warn('No active shifts found. Skipping worker shift seeding.');
            return;
        }

        // Create fixed shift patterns for each worker
        foreach ($workers as $worker) {
            $shift = $shifts->random();

            WorkerShift::create([
                'worker_id' => $worker->id,
                'shift_id' => $shift->id,
                'effective_from' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'effective_until' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'is_active' => true,
                'notes' => "Shift: {$shift->name}",
            ]);
        }

        // Add some shift overrides for variety
        $this->addShiftOverrides($workers, $shifts, $users);

        $this->command->info('✅ Worker Shifts seeded successfully!');
    }

    private function addShiftOverrides($workers, $shifts, $users): void
    {
        // Add 20-30 random shift overrides
        $overrideCount = rand(20, 30);

        for ($i = 0; $i < $overrideCount; $i++) {
            $worker = $workers->random();
            $shift = $shifts->random();
            $user = $users->random();
            $date = Carbon::now()->addDays(rand(-10, 60))->format('Y-m-d');

            ShiftOverride::updateOrCreate(
                [
                    'worker_id' => $worker->id,
                    'override_date' => $date,
                ],
                [
                    'shift_id' => $shift->id,
                    'reason' => 'Pergantian shift atas permintaan operasional',
                    'created_by' => $user->id,
                ]
            );
        }
    }
}
