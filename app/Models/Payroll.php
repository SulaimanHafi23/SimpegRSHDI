<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'worker_id',
        'period',
        'period_start',
        'period_end',
        'payment_date',
        'basic_salary',
        'total_earnings',
        'total_deductions',
        'gross_salary',
        'net_salary',
        'total_days_worked',
        'total_present',
        'total_late',
        'total_absent',
        'total_overtime_hours',
        'overtime_amount',
        'tax_amount',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_overtime_hours' => 'decimal:2',
    ];

    /**
     * Get the worker that owns the payroll
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get payroll details
     */
    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    /**
     * Scope untuk periode tertentu
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if payroll is editable
     */
    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'paid' => 'Dibayar',
            default => $this->status,
        };
    }
}
