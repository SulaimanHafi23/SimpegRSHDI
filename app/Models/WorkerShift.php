<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkerShift extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'effective_from',
        'effective_until',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Accessor for start_date (alias for effective_from)
     */
    public function getStartDateAttribute(): ?string
    {
        return $this->effective_from?->format('Y-m-d');
    }

    /**
     * Accessor for end_date (alias for effective_until)
     */
    public function getEndDateAttribute(): ?string
    {
        return $this->effective_until?->format('Y-m-d');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get shift for specific date
     */
    public function getShiftForDate(\DateTime $date): ?string
    {
        if (!$this->shift_id) {
            return null;
        }

        $shift = $this->relationLoaded('shift')
            ? $this->shift
            : $this->shift()->with('dayTimes')->first();

        if (!$shift) {
            return null;
        }

        $hasPerDaySchedule = $shift->dayTimes()->exists();
        if (!$hasPerDaySchedule) {
            return $this->shift_id;
        }

        $dayOfWeek = Carbon::instance($date)->dayOfWeek;
        $isActiveOnDay = $shift->dayTimes()->where('day_of_week', $dayOfWeek)->exists();

        return $isActiveOnDay ? $this->shift_id : null;
    }

    /**
     * Check if shift is active on specific date
     */
    public function isActiveOnDate(\DateTime $date): bool
    {
        if (!$this->is_active) {
            return false;
        }
        // Normalize everything to date strings (Y-m-d) so comparison works correctly
        $dateString = $date->format('Y-m-d');
        $from = $this->effective_from ? $this->effective_from->toDateString() : null;
        $until = $this->effective_until ? $this->effective_until->toDateString() : null;

        if ($from && $from > $dateString) {
            return false;
        }

        if ($until && $until < $dateString) {
            return false;
        }

        return true;
    }
}
