<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    use HasFactory, HasUuids;

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
        'total_hours' => 'integer',
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
     * Get the actual shift for this overtime date, considering ShiftOverride (swap).
     * Returns null if no shift found.
     */
    public function getActualShiftAttribute()
    {
        if (!$this->worker || !$this->overtime_date) {
            return null;
        }

        return $this->worker->getShiftForDate($this->overtime_date);
    }
}
