<?php

namespace Database\Seeders;

use App\Models\Absent;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Attendances...');

        $workers = Worker::with('user')->get();

        // Generate attendance for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($workers as $worker) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // Skip weekends (Saturday & Sunday)
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                // 90% chance of attendance
                $isPresent = rand(1, 100) <= 90;

                if ($isPresent) {
                    $checkInTime = $currentDate->copy()->setTime(8, rand(0, 30), 0); // 08:00 - 08:30
                    $checkOutTime = $currentDate->copy()->setTime(17, rand(0, 60), 0); // 17:00 - 18:00

                    Absent::create([
                        'worker_id' => $worker->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'check_in' => $checkInTime->format('H:i:s'),
                        'check_out' => $checkOutTime->format('H:i:s'),
                        'status' => 'present',
                        'notes' => null,
                    ]);
                } else {
                    // 10% absent - some with reason, some without
                    $reasons = [
                        'Sakit',
                        'Izin keluarga',
                        'Keperluan mendadak',
                        null, // No reason
                    ];

                    Absent::create([
                        'worker_id' => $worker->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'check_in' => null,
                        'check_out' => null,
                        'status' => 'absent',
                        'notes' => $reasons[array_rand($reasons)],
                    ]);
                }

                $currentDate->addDay();
            }
        }

        $this->command->info('✅ Attendances seeded successfully!');
    }
}
