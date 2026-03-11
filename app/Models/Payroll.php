<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'payroll_period_id',
        'worker_id',
        'base_salary',
        'total_earnings',
        'total_deductions',
        'net_salary',
        'components',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'base_salary'       => 'decimal:2',
        'total_earnings'    => 'decimal:2',
        'total_deductions'  => 'decimal:2',
        'net_salary'        => 'decimal:2',
        'components'        => 'array',
        'paid_at'           => 'datetime',
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft'     => ['variant' => 'secondary', 'label' => 'Draft'],
            'finalized' => ['variant' => 'info',      'label' => 'Final'],
            'paid'      => ['variant' => 'success',   'label' => 'Dibayar'],
            default     => ['variant' => 'secondary', 'label' => ucfirst((string) $this->status)],
        };
    }

    public function getEarningsListAttribute(): array
    {
        return array_filter((array) ($this->components ?? []), fn ($c) => ($c['type'] ?? '') === 'earning');
    }

    public function getDeductionsListAttribute(): array
    {
        return array_filter((array) ($this->components ?? []), fn ($c) => ($c['type'] ?? '') === 'deduction');
    }
}
