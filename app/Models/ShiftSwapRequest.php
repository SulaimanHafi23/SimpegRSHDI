<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftSwapRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'shift_swap_requests';

    protected $fillable = [
        'requester_id',
        'target_worker_id',
        'requester_shift_id',
        'target_shift_id',
        'swap_type',
        'swap_start_date',
        'swap_end_date',
        'swap_dates',
        'status',
        'requires_manager_approval',
        'manager_id',
        'manager_approved_at',
        'reason',
        'metadata',
        'requested_at',
        'executed_by',
        'executed_at',
    ];

    protected $casts = [
        'requires_manager_approval' => 'boolean',
        'metadata' => 'array',
        'swap_dates' => 'array',
        'swap_start_date' => 'date',
        'swap_end_date' => 'date',
        'requested_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    // Backward compatibility: legacy code may still read/write swap_date.
    public function getSwapDateAttribute()
    {
        if ($this->swap_type !== 'single_date') {
            return null;
        }

        return $this->swap_start_date;
    }

    public function setSwapDateAttribute($value): void
    {
        if (!$value) {
            return;
        }

        $this->attributes['swap_start_date'] = $value;
        $this->attributes['swap_end_date'] = $value;
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'requester_id');
    }

    public function targetWorker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'target_worker_id');
    }

    public function requesterShift(): BelongsTo
    {
        return $this->belongsTo(WorkerShift::class, 'requester_shift_id');
    }

    public function targetShift(): BelongsTo
    {
        return $this->belongsTo(WorkerShift::class, 'target_shift_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ShiftSwapAuditLog::class);
    }
}
