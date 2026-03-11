<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'worker_id',
        'promotion_type',
        'current_rank',
        'current_rank_level',
        'proposed_rank',
        'proposed_rank_level',
        'current_base_salary',
        'proposed_base_salary',
        'effective_date',
        'status',
        'reason',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'current_base_salary'  => 'decimal:2',
        'proposed_base_salary' => 'decimal:2',
        'effective_date'       => 'date',
        'reviewed_at'          => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'  => ['variant' => 'warning', 'label' => 'Menunggu'],
            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
            'rejected' => ['variant' => 'danger',  'label' => 'Ditolak'],
            default    => ['variant' => 'secondary', 'label' => ucfirst((string) $this->status)],
        };
    }

    public function getSalaryDiffAttribute(): float
    {
        return (float) $this->proposed_base_salary - (float) $this->current_base_salary;
    }
}
