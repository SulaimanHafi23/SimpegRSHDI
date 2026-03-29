<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkerOffDay extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'worker_id',
        'day_of_week',
        'effective_from',
        'effective_until',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'day_of_week' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
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
     * Get active off-day rules for worker on specific date.
     */
    public static function getActiveRules($workerId, $date)
    {
        $date = Carbon::parse($date);

        return self::where('worker_id', $workerId)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date->format('Y-m-d'));
            })
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Check if date is off-day based on worker_off_days entries.
     *
     * Single-day off can be represented by setting:
     * - day_of_week to the date's day
     * - effective_from == effective_until
     */
    public static function isOffDay($workerId, $date): bool
    {
        $date = Carbon::parse($date);
        $rules = self::getActiveRules($workerId, $date);

        if ($rules->isEmpty()) {
            return false;
        }

        foreach ($rules as $rule) {
            if (is_array($rule->day_of_week) && in_array($date->dayOfWeek, $rule->day_of_week)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Backward-compatible helper for existing callers.
     */
    public static function isOffDayByPattern($workerId, $date): bool
    {
        return self::isOffDay($workerId, $date);
    }
}
