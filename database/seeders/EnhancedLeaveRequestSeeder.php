<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedLeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds dengan berbagai status dan skenario
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive leave request data...');

        $workers = Worker::all();
        $leaveTypes = LeaveType::all();

        if ($workers->isEmpty() || $leaveTypes->isEmpty()) {
            $this->command->warn('No workers or leave types found!');
            return;
        }

        $statuses = [
            'pending' => 20,      // 20% pending
            'approved' => 50,     // 50% approved
            'rejected' => 15,     // 15% rejected
            'cancelled' => 15,    // 15% cancelled
        ];

        foreach ($workers as $worker) {
            // Setiap worker punya 3-8 leave requests
            $requestCount = rand(3, 8);

            for ($i = 0; $i < $requestCount; $i++) {
                $status = $this->getRandomStatus($statuses);
                $leaveType = $leaveTypes->random();
                
                // Generate date ranges
                $dateRange = $this->generateDateRange($status);
                
                $leave = $this->createLeaveRequest(
                    $worker,
                    $leaveType,
                    $status,
                    $dateRange
                );

                $this->command->info("Created {$status} leave for {$worker->name}: {$dateRange['start']->format('Y-m-d')} to {$dateRange['end']->format('Y-m-d')}");
            }
        }

        $this->command->info('✅ Enhanced leave request data created successfully!');
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
     * Generate date range berdasarkan status
     */
    private function generateDateRange(string $status): array
    {
        $now = Carbon::now();

        switch ($status) {
            case 'pending':
                // Future dates (1-60 hari ke depan)
                $startDate = $now->copy()->addDays(rand(1, 60));
                break;

            case 'approved':
                // Mix of past, present, and future
                $daysOffset = rand(-90, 60);
                $startDate = $now->copy()->addDays($daysOffset);
                break;

            case 'rejected':
                // Mostly past requests
                $startDate = $now->copy()->subDays(rand(1, 90));
                break;

            case 'cancelled':
                // Past and future
                $daysOffset = rand(-60, 30);
                $startDate = $now->copy()->addDays($daysOffset);
                break;

            default:
                $startDate = $now->copy()->addDays(rand(1, 30));
        }

        // Duration: 1-14 hari
        $duration = rand(1, 14);
        $endDate = $startDate->copy()->addDays($duration - 1);

        return [
            'start' => $startDate,
            'end' => $endDate,
            'duration' => $duration,
        ];
    }

    /**
     * Create leave request dengan detail
     */
    private function createLeaveRequest(Worker $worker, LeaveType $leaveType, string $status, array $dateRange)
    {
        $reasons = [
            'Keperluan keluarga',
            'Acara pernikahan keluarga',
            'Liburan bersama keluarga',
            'Urusan pribadi penting',
            'Kondisi kesehatan',
            'Pemeriksaan kesehatan rutin',
            'Mengurus dokumen penting',
            'Kunjungan ke kampung halaman',
            'Menemani keluarga yang sakit',
            'Renovasi rumah',
            'Acara keluarga besar',
            'Pendidikan anak',
        ];

        $rejectionReasons = [
            'Periode cuti terlalu dekat dengan cuti sebelumnya',
            'Departemen membutuhkan jumlah staff minimum',
            'Periode peak season, mohon reschedule',
            'Kuota cuti tahunan sudah habis',
            'Request terlalu mendadak, butuh minimal 3 hari notice',
            'Bentrok dengan jadwal proyek penting',
        ];

        $data = [
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $dateRange['start'],
            'end_date' => $dateRange['end'],
            'total_days' => $dateRange['duration'],
            'reason' => $reasons[array_rand($reasons)],
            'status' => $status,
            'created_at' => $dateRange['start']->copy()->subDays(rand(3, 30)),
        ];

        // Add approval/rejection details
        if (in_array($status, ['approved', 'rejected'])) {
            // Get a manager or HR user
            $approver = \App\Models\User::role(['Manager', 'HR', 'Super Admin'])->inRandomOrder()->first();
            
            if ($approver) {
                $data['approved_by'] = $approver->id;
                $data['approved_at'] = $data['created_at']->copy()->addDays(rand(1, 3));
            }

            if ($status === 'rejected') {
                $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
            }
        }

        // Add cancellation details
        if ($status === 'cancelled') {
            $data['cancelled_at'] = $data['created_at']->copy()->addDays(rand(1, 5));
            $data['cancellation_reason'] = 'Dibatalkan karena perubahan rencana';
        }

        // Add attachment (30% kemungkinan)
        if (rand(1, 100) <= 30) {
            $data['attachment_path'] = 'leave_attachments/sample_' . Str::random(10) . '.pdf';
        }

        return LeaveRequest::create($data);
    }
}
