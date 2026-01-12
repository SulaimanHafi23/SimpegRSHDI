<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerShift extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'pattern_type',
        'rotating_days',
        'custom_working_days',
        'effective_from',
        'effective_until',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'rotating_days' => 'array',
        'custom_working_days' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get shift for specific date based on pattern
     */
    public function getShiftForDate(\DateTime $date): ?string
    {
        // Treat null/empty pattern_type as 'fixed' to support legacy data
        $pattern = $this->pattern_type ?: 'fixed';

        if ($pattern === 'fixed') {
            return $this->shift_id;
        }

        // Custom pattern: shift applies only on specified working days
        if ($pattern === 'custom') {
            $dayOfWeek = (int) $date->format('N'); // 1 (Monday) to 7 (Sunday)
            $days = $this->custom_working_days ?? [];
            if (is_array($days)) {
                // Convert string values to integers for comparison
                $normalizedDays = array_map('intval', $days);
                if (in_array($dayOfWeek, $normalizedDays, true)) {
                    return $this->shift_id;
                }
            }
            return null;
        }

        // Rotating pattern: rotating_days maps dayOfWeek -> shift_id
        if ($pattern === 'rotating' && $this->rotating_days) {
            $dayOfWeek = $date->format('N'); // string key like '1'..'7'
            return $this->rotating_days[$dayOfWeek] ?? null;
        }

        return null;
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
