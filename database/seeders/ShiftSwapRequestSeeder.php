<?php

namespace Database\Seeders;

use App\Models\ShiftSwapRequest;
use App\Models\WorkerShiftSchedule;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ShiftSwapRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Shift Swap Requests...');

        $workers = Worker::all();
        $approvers = User::role(['Super Admin', 'HR', 'Manager'])->get();

        if ($workers->count() < 2) {
            $this->command->warn('Not enough workers for shift swap requests. Skipping.');
            return;
        }

        $reasons = [
            'Keperluan keluarga mendesak',
            'Jadwal bentrok dengan acara penting',
            'Kondisi kesehatan',
            'Pertukaran shift dengan kesepakatan bersama',
            'Keperluan pribadi',
        ];

        $statuses = ['pending', 'approved', 'rejected', 'cancelled'];

        // Generate 10-15 shift swap requests
        $count = rand(10, 15);
        
        for ($i = 0; $i < $count; $i++) {
            // Get two different workers
            $requester = $workers->random();
            $target = $workers->where('id', '!=', $requester->id)->random();
            
            // Get schedules for both workers
            $requesterSchedule = WorkerShiftSchedule::where('worker_id', $requester->id)
                ->where('date', '>=', Carbon::now()->format('Y-m-d'))
                ->inRandomOrder()
                ->first();
            
            if (!$requesterSchedule) continue;
            
            $targetSchedule = WorkerShiftSchedule::where('worker_id', $target->id)
                ->where('date', '>=', Carbon::now()->format('Y-m-d'))
                ->where('date', '!=', $requesterSchedule->date)
                ->inRandomOrder()
                ->first();
            
            if (!$targetSchedule) continue;
            
            $status = $statuses[array_rand($statuses)];
            
            $swapRequest = ShiftSwapRequest::create([
                'requester_worker_id' => $requester->id,
                'target_worker_id' => $target->id,
                'requester_shift_schedule_id' => $requesterSchedule->id,
                'target_shift_schedule_id' => $targetSchedule->id,
                'reason' => $reasons[array_rand($reasons)],
                'status' => $status,
                'target_response' => in_array($status, ['approved', 'rejected']) ? ($status === 'approved' ? 'accepted' : 'declined') : 'pending',
                'target_response_at' => in_array($status, ['approved', 'rejected']) ? Carbon::now()->subDays(rand(1, 3)) : null,
                'notes' => $i % 3 == 0 ? 'Sudah dikonfirmasi dengan kedua pihak' : null,
            ]);

            // If approved or rejected, add approver info
            if (in_array($status, ['approved', 'rejected']) && $approvers->isNotEmpty()) {
                $approver = $approvers->random();
                $swapRequest->update([
                    'approved_by' => $approver->id,
                    'approved_at' => Carbon::now()->subDays(rand(1, 2)),
                ]);

                if ($status === 'rejected') {
                    $swapRequest->update([
                        'rejection_reason' => 'Pertukaran shift tidak memenuhi kebijakan operasional',
                    ]);
                }
            }
        }

        $this->command->info('✅ Shift Swap Requests seeded successfully!');
    }
}
