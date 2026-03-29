<?php

namespace App\Services\WorkerOffDay;

use App\Models\Worker;
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
        $dateStr = $date->format('Y-m-d');

        $offDayPattern = $worker->offDays()
            ->where('effective_from', '<=', $dateStr)
            ->where(function ($query) use ($dateStr) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $dateStr);
            })
            ->orderByDesc('effective_from')
            ->get()
            ->first(function ($item) use ($date) {
                return is_array($item->day_of_week) && in_array($date->dayOfWeek, $item->day_of_week);
            });

        if ($offDayPattern) {
            $isSingleDay = $offDayPattern->effective_until
                && $offDayPattern->effective_from
                && $offDayPattern->effective_from->format('Y-m-d') === $offDayPattern->effective_until->format('Y-m-d');

            return [
                'type' => $isSingleDay ? 'single' : 'recurring',
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
     * Create off-day rule.
     *
     * - Recurring: pass day list + effective range
     * - Single-day: pass one date, method auto-converts to 1-day range rule
     */
    public function createOffDayRule(
        Worker $worker,
        array $daysOfWeek,
        $effectiveFrom,
        $effectiveUntil = null,
        $reason = null,
        $userId = null
    ): WorkerOffDay
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
     * Backward-compatible wrapper.
     */
    public function createOffDayPattern(Worker $worker, array $daysOfWeek, $effectiveFrom, $effectiveUntil = null, $reason = null, $userId = null): WorkerOffDay
    {
        return $this->createOffDayRule($worker, $daysOfWeek, $effectiveFrom, $effectiveUntil, $reason, $userId);
    }

    /**
     * Get all active off-days for worker in date range.
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
     * Delete off-day pattern
     */
    public function deletePattern($patternId): bool
    {
        return (bool) WorkerOffDay::destroy($patternId);
    }
}
