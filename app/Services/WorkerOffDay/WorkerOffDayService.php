<?php

namespace App\Services\WorkerOffDay;

use App\Models\Worker;
use App\Models\WorkerOffDayException;
use App\Models\WorkerOffDay;
use Carbon\Carbon;

class WorkerOffDayService
{
    /**
     * Check if date is an off-day for worker
     */
    public function isOffDay(Worker $worker, $date): bool
    {
        $date = Carbon::parse($date);
        return $worker->isOffDay($date);
    }

    /**
     * Check if worker can perform attendance on date
     * For check-in: reject if off-day
     * For check-out: allow if shift started previous day
     */
    public function canPerformAttendance(Worker $worker, $checkDate, $type = 'check_in', $checkInDate = null): array
    {
        $checkDate = Carbon::parse($checkDate);

        if ($type === 'check_in') {
            if ($worker->isOffDay($checkDate)) {
                return [
                    'can_perform' => false,
                    'message' => "Anda tidak dapat melakukan check-in pada tanggal {$checkDate->format('d M Y')} karena termasuk hari libur Anda.",
                    'reason' => 'off_day'
                ];
            }
        } elseif ($type === 'check_out') {
            // For check-out: allow if check-in was on different (working) day
            if ($checkInDate) {
                $checkInDate = Carbon::parse($checkInDate);
                if ($worker->canCheckOutOnDate($checkDate, $checkInDate)) {
                    return [
                        'can_perform' => true,
                        'message' => 'Check-out diperbolehkan (shift overnight dari hari kerja)',
                        'reason' => 'overnight_shift_allowed'
                    ];
                }
            }

            if ($worker->isOffDay($checkDate)) {
                return [
                    'can_perform' => false,
                    'message' => "Anda tidak dapat melakukan check-out pada tanggal {$checkDate->format('d M Y')} karena termasuk hari libur Anda.",
                    'reason' => 'off_day'
                ];
            }
        }

        return [
            'can_perform' => true,
            'message' => 'Dapat melakukan absensi',
            'reason' => 'allowed'
        ];
    }

    /**
     * Get off-day info for worker on specific date
     */
    public function getOffDayInfo(Worker $worker, $date): ?array
    {
        $date = Carbon::parse($date);

        // Check exception first
        $exception = $worker->offDayExceptions()
            ->where(function ($query) use ($date) {
                $dateStr = $date->format('Y-m-d');
                $query->where('type', 'single')
                    ->where('off_date', $dateStr)
                    ->orWhere(function ($q) use ($dateStr) {
                        $q->where('type', 'recurring')
                            ->where('off_date', '<=', $dateStr)
                            ->where(function ($subQ) use ($dateStr) {
                                $subQ->whereNull('recurring_pattern->until')
                                    ->orWhereRaw("JSON_EXTRACT(recurring_pattern, '$.until') >= ?", [$dateStr]);
                            });
                    });
            })
            ->first();

        if ($exception) {
            return [
                'type' => 'exception',
                'date' => $date->format('Y-m-d'),
                'exception_type' => $exception->type,
                'reason' => $exception->reason,
                'created_at' => $exception->created_at->format('d M Y H:i'),
            ];
        }

        // Check pattern
        $offDayPattern = $worker->offDays()
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date->format('Y-m-d'));
            })
            ->first();

        if ($offDayPattern && in_array($date->dayOfWeek, $offDayPattern->day_of_week)) {
            return [
                'type' => 'pattern',
                'date' => $date->format('Y-m-d'),
                'pattern' => $offDayPattern->day_of_week,
                'reason' => $offDayPattern->reason,
                'effective_from' => $offDayPattern->effective_from->format('d M Y'),
                'effective_until' => $offDayPattern->effective_until?->format('d M Y') ?? 'Tanpa batas',
            ];
        }

        return null;
    }

    /**
     * Create single day off exception
     */
    public function createException(Worker $worker, $date, $reason = null, $userId = null): WorkerOffDayException
    {
        return $worker->offDayExceptions()->create([
            'off_date' => $date,
            'type' => 'single',
            'reason' => $reason,
            'created_by' => $userId,
        ]);
    }

    /**
     * Create recurring off-day exception
     */
    public function createRecurringException(Worker $worker, array $daysOfWeek, $until = null, $reason = null, $userId = null): WorkerOffDayException
    {
        return $worker->offDayExceptions()->create([
            'off_date' => now()->toDateString(),
            'type' => 'recurring',
            'recurring_pattern' => [
                'day_of_week' => $daysOfWeek,
                'until' => $until,
            ],
            'reason' => $reason,
            'created_by' => $userId,
        ]);
    }

    /**
     * Create off-day pattern (rotating off-days)
     */
    public function createOffDayPattern(Worker $worker, array $daysOfWeek, $effectiveFrom, $effectiveUntil = null, $reason = null, $userId = null): WorkerOffDay
    {
        return $worker->offDays()->create([
            'day_of_week' => $daysOfWeek,
            'effective_from' => $effectiveFrom,
            'effective_until' => $effectiveUntil,
            'reason' => $reason,
            'created_by' => $userId,
        ]);
    }

    /**
     * Get all active off-days for worker (exceptions + patterns) in date range
     */
    public function getOffDaysInRange(Worker $worker, $startDate, $endDate): array
    {
        $offDays = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Iterate through all dates
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($this->isOffDay($worker, $date)) {
                $offDays[] = $date->format('Y-m-d');
            }
        }

        return $offDays;
    }

    /**
     * Delete exception
     */
    public function deleteException($exceptionId): bool
    {
        return (bool) WorkerOffDayException::destroy($exceptionId);
    }

    /**
     * Delete off-day pattern
     */
    public function deletePattern($patternId): bool
    {
        return (bool) WorkerOffDay::destroy($patternId);
    }
}
