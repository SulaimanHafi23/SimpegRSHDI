<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftOverride extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'override_date',
        'reason',
        'created_by',
        'shift_swap_request_id',
    ];

    protected $casts = [
        'override_date' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shiftSwapRequest(): BelongsTo
    {
        return $this->belongsTo(ShiftSwapRequest::class, 'shift_swap_request_id');
    }
}
