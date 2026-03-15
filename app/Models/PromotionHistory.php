<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'promotion_request_id',
        'promotion_type',
        'old_rank',
        'old_rank_level',
        'new_rank',
        'new_rank_level',
        'old_base_salary',
        'new_base_salary',
        'effective_date',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'old_base_salary' => 'decimal:2',
        'new_base_salary' => 'decimal:2',
        'effective_date'  => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function promotionRequest(): BelongsTo
    {
        return $this->belongsTo(PromotionRequest::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getSalaryDiffAttribute(): float
    {
        return (float) $this->new_base_salary - (float) $this->old_base_salary;
    }
}
