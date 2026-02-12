<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

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

    public function dayTimes(): HasMany
    {
        return $this->hasMany(ShiftDayTime::class);
    }

    public function shiftOverrides(): HasMany
    {
        return $this->hasMany(ShiftOverride::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getScheduleForDate($date): array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $dayTime = $this->relationLoaded('dayTimes')
            ? $this->dayTimes->firstWhere('day_of_week', $dayOfWeek)
            : $this->dayTimes()->where('day_of_week', $dayOfWeek)->first();

        $startTime = $dayTime?->start_time ?? $this->start_time;
        $endTime = $dayTime?->end_time ?? $this->end_time;

        $start = Carbon::parse($startTime)->format('H:i:s');
        $end = Carbon::parse($endTime)->format('H:i:s');

        $isOvernight = Carbon::createFromFormat('H:i:s', $end)
            ->lessThan(Carbon::createFromFormat('H:i:s', $start));

        return [
            'start_time' => $start,
            'end_time' => $end,
            'is_overnight' => $isOvernight,
        ];
    }
}
