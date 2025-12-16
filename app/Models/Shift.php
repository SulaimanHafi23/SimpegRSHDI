<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'total_hours',
        'grace_period_minutes',
        'is_overnight',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'total_hours' => 'integer',
        'grace_period_minutes' => 'integer',
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function workerShifts(): HasMany
    {
        return $this->hasMany(WorkerShift::class);
    }

    public function shiftOverrides(): HasMany
    {
        return $this->hasMany(ShiftOverride::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
