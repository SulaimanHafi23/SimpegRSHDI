<?php

namespace Database\Seeders;

use App\Models\ShiftSwapRequest;
use App\Models\Worker;
use App\Models\WorkerShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedShiftSwapRequestSeeder extends Seeder
{
    /**
     * Run the database seeds dengan berbagai status dan skenario
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive shift swap request data...');

        $workers = Worker::with('workerShifts')->get();

        if ($workers->count() < 2) {
            $this->command->warn('Need at least 2 workers for shift swap!');
            return;
        }

        $statuses = [
            'pending' => 20,              // 20% menunggu target worker
            'awaiting_approval' => 25,    // 25% menunggu approval HR
            'approved' => 30,              // 30% approved
            'rejected' => 15,              // 15% rejected
            'cancelled' => 10,             // 10% cancelled
        ];

        // Create 15-30 shift swap requests
        $totalRequests = rand(15, 30);

        for ($i = 0; $i < $totalRequests; $i++) {
            $status = $this->getRandomStatus($statuses);
            
            // Pick random requester
            $requester = $workers->random();
            
            // Pick target worker (different from requester, 30% null for open request)
            $targetWorker = null;
            if (rand(1, 100) > 30) {
                $targetWorker = $workers->where('id', '!=', $requester->id)->random();
            }

            $swap = $this->createShiftSwapRequest($requester, $targetWorker, $status);

            if ($swap) {
                $targetName = $targetWorker ? $targetWorker->name : 'Open Request';
                $this->command->info("Created {$status} swap: {$requester->name} <-> {$targetName}");
            }
        }

        $this->command->info('✅ Enhanced shift swap request data created successfully!');
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
     * Create shift swap request dengan detail
     */
    private function createShiftSwapRequest(Worker $requester, ?Worker $targetWorker, string $status)
    {
        $now = Carbon::now();

        // Get worker shifts
        $requesterShift = $requester->workerShifts()
            ->whereDate('effective_from', '>=', $now)
            ->inRandomOrder()
            ->first();

        if (!$requesterShift) {
            return null;
        }

        $targetShift = null;
        if ($targetWorker) {
            $targetShift = $targetWorker->workerShifts()
                ->whereDate('effective_from', '>=', $now)
                ->inRandomOrder()
                ->first();

            if (!$targetShift) {
                return null;
            }
        }

        $swapDate = $requesterShift->effective_from;

        $reasons = [
            'Keperluan keluarga mendesak',
            'Ada janji dokter yang tidak bisa diubah',
            'Menghadiri acara keluarga',
            'Kondisi kesehatan kurang fit',
            'Anak sakit perlu perhatian',
            'Bentrok dengan jadwal kuliah',
            'Ada keperluan urgent di kampung',
            'Perlu istirahat tambahan',
            'Ada event penting yang harus dihadiri',
            'Request dari rekan kerja',
        ];

        $rejectionReasons = [
            'Tidak memenuhi minimum notice period',
            'Shift tidak compatible untuk ditukar',
            'Departemen butuh coverage minimum',
            'Terlalu banyak swap request dalam periode ini',
            'Alasan tidak cukup kuat',
            'Target worker sudah punya jadwal padat',
        ];

        // Check if cross-department
        $requiresManagerApproval = false;
        if ($targetWorker && $requester->department_id !== $targetWorker->department_id) {
            $requiresManagerApproval = true;
        }

        $data = [
            'id' => Str::uuid(),
            'requester_worker_id' => $requester->id,
            'target_worker_id' => $targetWorker?->id,
            'requester_shift_id' => $requesterShift->id,
            'target_shift_id' => $targetShift?->id,
            'swap_date' => $swapDate,
            'reason' => $reasons[array_rand($reasons)],
            'status' => $status,
            'requires_manager_approval' => $requiresManagerApproval,
            'requested_at' => $now->copy()->subDays(rand(1, 10)),
            'created_at' => $now->copy()->subDays(rand(1, 10)),
        ];

        // Add status-specific data
        switch ($status) {
            case 'awaiting_approval':
                // Target worker sudah accept, menunggu manager
                $data['target_accepted_at'] = $data['created_at']->copy()->addDays(rand(1, 2));
                break;

            case 'approved':
                if ($targetWorker) {
                    $data['target_accepted_at'] = $data['created_at']->copy()->addDays(rand(1, 2));
                }
                
                // Get manager/HR
                $approver = \App\Models\User::role(['Manager', 'HR', 'Super Admin'])->inRandomOrder()->first();
                if ($approver) {
                    $data['manager_id'] = $approver->id;
                    $data['manager_approved_at'] = $data['created_at']->copy()->addDays(rand(2, 4));
                }
                break;

            case 'rejected':
                $approver = \App\Models\User::role(['Manager', 'HR', 'Super Admin'])->inRandomOrder()->first();
                if ($approver) {
                    $data['manager_id'] = $approver->id;
                    $data['manager_approved_at'] = $data['created_at']->copy()->addDays(rand(1, 3));
                    $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
                }
                break;

            case 'cancelled':
                $data['cancelled_at'] = $data['created_at']->copy()->addDays(rand(1, 5));
                $data['cancellation_reason'] = 'Dibatalkan karena sudah tidak diperlukan';
                break;
        }

        $swap = ShiftSwapRequest::create($data);

        // Create audit logs
        if ($swap) {
            $this->createAuditLogs($swap);
        }

        return $swap;
    }

    /**
     * Create audit logs for swap
     */
    private function createAuditLogs(ShiftSwapRequest $swap)
    {
        $logs = [];

        // Log 1: Created
        $logs[] = [
            'id' => Str::uuid(),
            'shift_swap_request_id' => $swap->id,
            'action' => 'created',
            'old_status' => null,
            'new_status' => 'pending',
            'user_id' => $swap->requester->user_id,
            'notes' => 'Permintaan tukar shift dibuat',
            'created_at' => $swap->created_at,
        ];

        // Additional logs berdasarkan status
        if ($swap->target_accepted_at) {
            $logs[] = [
                'id' => Str::uuid(),
                'shift_swap_request_id' => $swap->id,
                'action' => 'accepted',
                'old_status' => 'pending',
                'new_status' => 'awaiting_approval',
                'user_id' => $swap->targetWorker->user_id ?? null,
                'notes' => 'Target worker menerima permintaan',
                'created_at' => $swap->target_accepted_at,
            ];
        }

        if ($swap->manager_approved_at && $swap->status === 'approved') {
            $logs[] = [
                'id' => Str::uuid(),
                'shift_swap_request_id' => $swap->id,
                'action' => 'approved',
                'old_status' => 'awaiting_approval',
                'new_status' => 'approved',
                'user_id' => $swap->manager_id,
                'notes' => 'Disetujui oleh manager/HR',
                'created_at' => $swap->manager_approved_at,
            ];
        }

        if ($swap->status === 'rejected') {
            $logs[] = [
                'id' => Str::uuid(),
                'shift_swap_request_id' => $swap->id,
                'action' => 'rejected',
                'old_status' => 'awaiting_approval',
                'new_status' => 'rejected',
                'user_id' => $swap->manager_id,
                'notes' => $swap->rejection_reason,
                'created_at' => $swap->manager_approved_at ?? $swap->created_at->addDays(1),
            ];
        }

        if ($swap->cancelled_at) {
            $logs[] = [
                'id' => Str::uuid(),
                'shift_swap_request_id' => $swap->id,
                'action' => 'cancelled',
                'old_status' => 'pending',
                'new_status' => 'cancelled',
                'user_id' => $swap->requester->user_id,
                'notes' => $swap->cancellation_reason,
                'created_at' => $swap->cancelled_at,
            ];
        }

        foreach ($logs as $log) {
            \DB::table('shift_swap_audit_logs')->insert($log);
        }
    }
}
