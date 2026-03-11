<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'month',
        'year',
        'start_date',
        'end_date',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'month'      => 'integer',
        'year'       => 'integer',
        'start_date' => 'date',
        'end_date'   => 'date',
        'paid_at'    => 'date',
    ];

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$this->month] ?? '-') . ' ' . $this->year;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft'      => ['variant' => 'secondary', 'label' => 'Draft'],
            'processing' => ['variant' => 'warning',   'label' => 'Proses'],
            'finalized'  => ['variant' => 'info',      'label' => 'Final'],
            'paid'       => ['variant' => 'success',   'label' => 'Dibayar'],
            default      => ['variant' => 'secondary', 'label' => ucfirst((string) $this->status)],
        };
    }
}
