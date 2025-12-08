<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    use HasUuid;

    protected $fillable = [
        'worker_id',
        'overtime_date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'overtime_date' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
