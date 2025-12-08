<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasUuid;

    protected $fillable = [
        'worker_id',
        'basic_salary',
        'allowance',
        'deduction',
        'total_salary',
        'payment_date',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'total_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    protected static function booted(): void
    {
        static::saving(function ($salary) {
            $salary->total_salary = $salary->basic_salary + $salary->allowance - $salary->deduction;
        });
    }
}
