<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'total_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'position_shift')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function workers()
    {
        return $this->belongsToMany(\App\Models\Worker::class, 'worker_shift_assignments')
                    ->withPivot(['is_default','is_active','priority'])
                    ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkerShiftSchedule::class);
    }
}
