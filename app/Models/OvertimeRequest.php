<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class OvertimeRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'worker_id',
        'overtime_date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'approval_notes',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'total_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessor untuk backward compatibility
    public function getDateAttribute()
    {
        return $this->overtime_date?->format('d M Y');
    }

    public function getDurationAttribute()
    {
        return $this->total_hours;
    }

    public function getDescriptionAttribute()
    {
        return $this->reason;
    }

    /**
     * Get the actual shift for this overtime date.
     * Returns the Shift model, or null if not found.
     */
    public function getActualShiftAttribute(): ?Shift
    {
        if (!$this->worker || !$this->overtime_date) {
            return null;
        }

        $date = $this->overtime_date;

        // 1. Check active WorkerShift for this date
        $workerShift = $this->worker->workerShifts()
            ->with('shift')
            ->where('is_active', true)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                      ->orWhere('effective_until', '>=', $date->format('Y-m-d'));
            })
            ->first();

        if ($workerShift && $workerShift->shift) {
            return $workerShift->shift;
        }

        // 2. Fallback to default shift
        return $this->worker->shift;
    }
}
