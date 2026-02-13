<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerShiftHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'effective_from',
        'effective_until',
        'changed_at',
        'change_reason',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'changed_at' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Log a shift change to history
     */
    public static function logChange(
        string $workerId,
        string $shiftId,
        ?string $effectiveFrom,
        ?string $effectiveUntil,
        string $changeReason = 'shift_replaced',
        ?string $changedBy = null,
        ?string $notes = null
    ): self {
        return self::create([
            'worker_id' => $workerId,
            'shift_id' => $shiftId,
            'effective_from' => $effectiveFrom,
            'effective_until' => $effectiveUntil,
            'changed_at' => now()->format('Y-m-d'),
            'change_reason' => $changeReason,
            'changed_by' => $changedBy ?? auth()->id(),
            'notes' => $notes,
        ]);
    }
}
