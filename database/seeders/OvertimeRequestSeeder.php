<?php

namespace Database\Seeders;

use App\Models\OvertimeRequest;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OvertimeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Overtime Requests...');

        $workers = Worker::with('user')->get();
        $approvers = User::role(['Super Admin', 'HR', 'Manager'])->get();

        $reasons = [
            'Menyelesaikan laporan bulanan',
            'Backup data sistem',
            'Persiapan event perusahaan',
            'Menyelesaikan project urgent',
            'Meeting dengan klien',
            'Training karyawan baru',
            'Audit internal',
            'Maintenance server',
        ];

        $statuses = ['pending', 'approved', 'rejected'];

        foreach ($workers as $worker) {
            // Generate 3-5 overtime requests per worker
            $count = rand(3, 5);

            for ($i = 0; $i < $count; $i++) {
                $date = Carbon::now()->subDays(rand(1, 60))->format('Y-m-d');
                $status = $statuses[array_rand($statuses)];
                $startTime = Carbon::createFromTime(17, 0, 0); // After work hours
                $endTime = $startTime->copy()->addHours(rand(2, 4));

                $overtime = OvertimeRequest::create([
                    'end_time' => $endTime->format('H:i:s'),
                    'total_hours' => $endTime->diffInHours($startTime),
                    'reason' => $reasons[array_rand($reasons)],
                    'status' => $status,
                    'notes' => $i % 2 == 0 ? 'Dikerjakan di kantor' : null,
                ]);

                // If approved or rejected, add approver info
                if ($status !== 'pending' && $approvers->isNotEmpty()) {
                    $approver = $approvers->random();
                    $overtime->update([
                        'approved_by' => $approver->id,
                        'approved_at' => Carbon::parse($date)->addDays(1),
                    ]);

                    if ($status === 'rejected') {
                        $overtime->update([
                            'rejection_reason' => 'Tidak ada kebutuhan lembur pada tanggal tersebut',
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Overtime Requests seeded successfully!');
    }
}
