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
        return $this->shift_id;
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
