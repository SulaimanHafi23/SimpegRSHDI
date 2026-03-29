<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwapAuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'shift_swap_request_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'notes',
        'metadata',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function shiftSwapRequest(): BelongsTo
    {
        return $this->belongsTo(ShiftSwapRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create audit log entry
     */
    public static function log(
        string $shiftSwapRequestId,
        string $action,
        string $newStatus,
        ?string $userId = null,
        ?string $oldStatus = null,
        ?string $notes = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'shift_swap_request_id' => $shiftSwapRequestId,
            'user_id' => $userId,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'metadata' => $metadata,
            'user_agent' => request()->userAgent(),
        ]);
    }
}
