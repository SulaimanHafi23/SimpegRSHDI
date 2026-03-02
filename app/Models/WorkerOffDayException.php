<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkerOffDayException extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'worker_id',
        'off_date',
        'type',
        'recurring_pattern',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'off_date' => 'date',
        'recurring_pattern' => 'array',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a specific date is an off-day exception
     */
    public static function isOffDay($workerId, $date): bool
    {
        $date = Carbon::parse($date);
        $dateStr = $date->format('Y-m-d');

        $exists = self::where('worker_id', $workerId)
            ->where(function ($query) use ($date, $dateStr) {
                $query->where(function ($q) use ($dateStr) {
                    // Single day off
                    $q->where('type', 'single')
                        ->where('off_date', $dateStr);
                })->orWhere(function ($q) use ($date, $dateStr) {
                    // Recurring off-day (weekly pattern)
                    $q->where('type', 'recurring')
                        ->where('off_date', '<=', $dateStr)
                        ->where(function ($subQ) use ($dateStr) {
                            $subQ->whereNull('recurring_pattern->until')
                                ->orWhereRaw("JSON_EXTRACT(recurring_pattern, '$.until') >= ?", [$dateStr]);
                        });
                });
            })
            ->exists();

        if ($exists) {
            // Double check if recurring pattern matches day of week
            $exception = self::where('worker_id', $workerId)
                ->where('type', 'recurring')
                ->where('off_date', '<=', $dateStr)
                ->where(function ($subQ) use ($dateStr) {
                    $subQ->whereNull('recurring_pattern->until')
                        ->orWhereRaw("JSON_EXTRACT(recurring_pattern, '$.until') >= ?", [$dateStr]);
                })
                ->first();

            if ($exception && $exception->recurring_pattern && isset($exception->recurring_pattern['day_of_week'])) {
                $dayOfWeek = $date->dayOfWeek;
                return in_array($dayOfWeek, $exception->recurring_pattern['day_of_week']);
            }

            return true;
        }

        return false;
    }
}
