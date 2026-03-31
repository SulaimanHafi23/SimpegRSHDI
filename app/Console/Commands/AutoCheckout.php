<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCheckout extends Command
{
    protected $signature = 'attendance:auto-checkout {--hours=3 : Hours after shift end to auto-checkout}';
    protected $description = 'Auto checkout workers who forgot to check out after their shift ended';

    public function handle()
    {
        $hoursAfterShift = (int) $this->option('hours');
        $now = Carbon::now();

        $this->info("Running auto-checkout at {$now->format('Y-m-d H:i:s')} (threshold: {$hoursAfterShift} hours after shift end)");

        // Find all attendance records with check_in but no check_out
        $pendingCheckouts = Attendance::whereNotNull('check_in')
            ->whereNull('check_out')
            ->where('status', 'present')
            ->with(['worker.workerShifts.shift', 'worker.shiftOverrides.shift', 'shift'])
            ->get();

        $autoCheckedOut = 0;

        foreach ($pendingCheckouts as $attendance) {
            try {
                $shift = $attendance->shift;

                // If no shift on the attendance record, try to find from worker's schedule
                if (!$shift && $attendance->worker) {
                    $attendanceDate = Carbon::parse($attendance->attendance_date);

                    // Check override first
                    $override = $attendance->worker->shiftOverrides
                        ->where('override_date', $attendanceDate->format('Y-m-d'))
                        ->first();

                    if ($override && $override->shift) {
                        $shift = $override->shift;
                    } else {
                        // Find active worker shift using isActiveOnDate
                        $activeWs = $attendance->worker->workerShifts
                            ->first(function ($ws) use ($attendanceDate) {
                                return $ws->isActiveOnDate($attendanceDate);
                            });

                        if ($activeWs && $activeWs->shift) {
                            $shift = $activeWs->shift;
                        }
                    }
                }

                if (!$shift) {
                    $this->warn("Attendance #{$attendance->id} - No shift found, skipping");
                    continue;
                }

                // Calculate shift end time
                $attendanceDate = Carbon::parse($attendance->attendance_date);
                $schedule = $shift->getScheduleForDate($attendanceDate);
                $shiftEnd = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $schedule['end_time']);

                // Handle overnight shift
                if ($schedule['is_overnight'] ?? false) {
                    $shiftEnd->addDay();
                }

                // Auto checkout time is shift end + threshold hours
                $autoCheckoutTime = $shiftEnd->copy()->addHours($hoursAfterShift);

                // Only auto-checkout if we've passed the threshold
                if ($now->lt($autoCheckoutTime)) {
                    continue;
                }

                // Auto checkout at shift end time (not now)
                $checkOutTime = $shiftEnd;

                DB::beginTransaction();
                try {
                    $attendance->update([
                        'check_out' => $checkOutTime,
                        'distance_check_out' => $attendance->distance_check_in ?? 0,
                        'is_early_leave' => false,
                        'early_leave_minutes' => 0,
                        'notes' => trim(($attendance->notes ?? '') . "\n[SYSTEM] Auto checkout pada {$checkOutTime->format('H:i')} (shift berakhir, pegawai tidak melakukan check-out manual)"),
                    ]);

                    DB::commit();
                    $autoCheckedOut++;

                    $workerName = $attendance->worker->name ?? 'Unknown';
                    $this->info("Auto checked out: {$workerName} (attendance #{$attendance->id}, date: {$attendance->attendance_date->format('Y-m-d')}, shift end: {$shiftEnd->format('H:i')})");

                    Log::info('Auto checkout executed', [
                        'attendance_id' => $attendance->id,
                        'worker_id' => $attendance->worker_id,
                        'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                        'check_out_time' => $checkOutTime->format('Y-m-d H:i:s'),
                        'shift_end' => $shiftEnd->format('H:i'),
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed to auto-checkout attendance #{$attendance->id}: {$e->getMessage()}");
                    Log::error('Auto checkout failed', [
                        'attendance_id' => $attendance->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("Error processing attendance #{$attendance->id}: {$e->getMessage()}");
            }
        }

        $this->info("Auto-checkout complete: {$autoCheckedOut} record(s) processed out of {$pendingCheckouts->count()} pending");

        return Command::SUCCESS;
    }
}
