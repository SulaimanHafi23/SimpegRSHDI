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
     * Get active off-day pattern for worker on specific date
     */
    public static function getActivePattern($workerId, $date): ?array
    {
        $date = Carbon::parse($date);

        return self::where('worker_id', $workerId)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date->format('Y-m-d'));
            })
            ->orderByDesc('effective_from')
            ->first()?->day_of_week;
    }

    /**
     * Check if date is off-day based on pattern
     */
    public static function isOffDayByPattern($workerId, $date): bool
    {
        $date = Carbon::parse($date);
        $pattern = self::getActivePattern($workerId, $date);

        if (!$pattern || !is_array($pattern)) {
            return false;
        }

        return in_array($date->dayOfWeek, $pattern);
    }
}
