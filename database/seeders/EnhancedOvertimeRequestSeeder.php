<?php

namespace Database\Seeders;

use App\Models\OvertimeRequest;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedOvertimeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds dengan berbagai status dan skenario
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive overtime request data...');

        $workers = Worker::all();

        if ($workers->isEmpty()) {
            $this->command->warn('No workers found!');
            return;
        }

        $statuses = [
            'pending' => 25,      // 25% pending
            'approved' => 50,     // 50% approved
            'rejected' => 15,     // 15% rejected
            'cancelled' => 10,    // 10% cancelled
        ];

        foreach ($workers as $worker) {
            // Setiap worker punya 2-6 overtime requests
            $requestCount = rand(2, 6);

            for ($i = 0; $i < $requestCount; $i++) {
                $status = $this->getRandomStatus($statuses);
                
                $overtime = $this->createOvertimeRequest($worker, $status);

                $this->command->info("Created {$status} overtime for {$worker->name}: {$overtime->overtime_date} ({$overtime->total_hours}h)");
            }
        }

        $this->command->info('✅ Enhanced overtime request data created successfully!');
    }

    /**
     * Get random status berdasarkan weight
     */
    private function getRandomStatus(array $statuses): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $status => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'pending';
    }

    /**
     * Create overtime request dengan detail
     */
    private function createOvertimeRequest(Worker $worker, string $status)
    {
        $now = Carbon::now();

        // Generate overtime date berdasarkan status
        switch ($status) {
            case 'pending':
                // Future dates (1-30 hari ke depan)
                $overtimeDate = $now->copy()->addDays(rand(1, 30));
                break;

            case 'approved':
                // Mix of past and future
                $daysOffset = rand(-60, 30);
                $overtimeDate = $now->copy()->addDays($daysOffset);
                break;

            case 'rejected':
            case 'cancelled':
                // Mostly past
                $overtimeDate = $now->copy()->subDays(rand(1, 60));
                break;

            default:
                $overtimeDate = $now->copy()->addDays(rand(1, 15));
        }

        // Generate time range
        $startHour = rand(17, 22); // 5 PM - 10 PM
        $startTime = Carbon::parse($overtimeDate)->setHour($startHour)->setMinute(rand(0, 59));
        
        // Duration: 1-8 jam
        $duration = rand(1, 8);
        $endTime = $startTime->copy()->addHours($duration);

        $reasons = [
            'Penyelesaian proyek urgent',
            'Deadline laporan bulanan',
            'Maintenance sistem di luar jam kerja',
            'Persiapan event besar',
            'Backup staff yang berhalangan',
            'Peningkatan produktivitas proyek',
            'Meeting dengan client luar negeri',
            'Training karyawan baru',
            'Tutup buku akhir bulan',
            'Audit internal',
            'Migrasi sistem',
            'Penanganan emergency',
        ];

        $rejectionReasons = [
            'Budget overtime bulan ini sudah tercapai',
            'Tidak ada urgensi yang jelas',
            'Bisa diselesaikan di jam kerja normal',
            'Perlu koordinasi ulang dengan tim',
            'Request terlalu mendadak',
            'Duplikasi dengan overtime request lain',
        ];

        $data = [
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'overtime_date' => $overtimeDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_hours' => $duration,
            'reason' => $reasons[array_rand($reasons)],
            'status' => $status,
            'created_at' => $overtimeDate->copy()->subDays(rand(1, 7)),
        ];

        // Add approval/rejection details
        if (in_array($status, ['approved', 'rejected'])) {
            $approver = \App\Models\User::role(['Manager', 'HR', 'Super Admin'])->inRandomOrder()->first();
            
            if ($approver) {
                $data['approved_by'] = $approver->id;
                $data['approved_at'] = $data['created_at']->copy()->addDays(rand(1, 2));

                if ($status === 'approved') {
                    // Calculate multiplier (1.5x or 2x)
                    $isWeekend = $overtimeDate->isWeekend();
                    $data['multiplier'] = $isWeekend ? 2.0 : 1.5;
                    $data['notes'] = $isWeekend ? 'Overtime weekend (2x)' : 'Overtime weekday (1.5x)';
                }
            }

            if ($status === 'rejected') {
                $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
            }
        }

        // Add cancellation details
        if ($status === 'cancelled') {
            $data['cancelled_at'] = $data['created_at']->copy()->addDays(rand(1, 3));
            $data['cancellation_reason'] = 'Dibatalkan karena pekerjaan sudah selesai';
        }

        return OvertimeRequest::create($data);
    }
}
