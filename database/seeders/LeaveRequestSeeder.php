<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\Worker;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Leave Requests...');

        $workers = Worker::with('user')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $approvers = User::role(['Super Admin', 'HR', 'Manager'])->get();

        $reasons = [
            'Keperluan keluarga',
            'Acara keluarga',
            'Liburan tahunan',
            'Istirahat',
            'Mengurus dokumen penting',
            'Keperluan pribadi mendesak',
            'Kondisi kesehatan memerlukan istirahat',
        ];

        $statuses = ['pending', 'approved', 'rejected', 'cancelled'];

        foreach ($workers as $worker) {
            // Generate 2-4 leave requests per worker
            $count = rand(2, 4);

            for ($i = 0; $i < $count; $i++) {
                $leaveType = $leaveTypes->random();
                $startDate = Carbon::now()->addDays(rand(-30, 30));
                $totalDays = rand(1, min(5, $leaveType->max_days_per_year));
                $endDate = $startDate->copy()->addDays($totalDays - 1);
                $status = $statuses[array_rand($statuses)];

                $leaveRequest = LeaveRequest::create([
                    'worker_id' => $worker->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_days' => $totalDays,
                    'reason' => $reasons[array_rand($reasons)],
                    'status' => $status,
                    'attachment_path' => null,
                ]);

                // If approved or rejected, add approver info
                if (in_array($status, ['approved', 'rejected']) && $approvers->isNotEmpty()) {
                    $approver = $approvers->random();
                    $leaveRequest->update([
                        'approved_by' => $approver->id,
                        'approved_at' => $startDate->copy()->subDays(rand(1, 5)),
                    ]);

                    if ($status === 'rejected') {
                        $leaveRequest->update([
                            'rejection_reason' => 'Kuota cuti sudah habis untuk tahun ini',
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Leave Requests seeded successfully!');
    }
}
