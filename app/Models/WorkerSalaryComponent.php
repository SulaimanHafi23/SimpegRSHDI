<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerSalaryComponent extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'worker_id',
        'salary_component_id',
        'calculation_type',
        'amount',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    /**
     * Hitung nilai efektif berdasarkan tipe perhitungan dan gaji pokok pekerja.
     */
    public function computedAmount(float $baseSalary = 0): float
    {
        if ($this->calculation_type === 'percentage') {
            return round($baseSalary * ((float) $this->amount / 100), 2);
        }

        return (float) $this->amount;
    }
}
