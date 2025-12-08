<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class WorkerShiftSchedule extends Model
{
    use HasUuid;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'day_of_week',
        'is_default',
        'schedule_date',
        'is_override',
        'replaced_worker_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'is_default' => 'boolean',
        'is_override' => 'boolean',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function replacedWorker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'replaced_worker_id');
    }

    public function absents()
    {
        return $this->hasMany(Absent::class);
    }

    // ========== SCOPES ==========
    
    /**
     * Scope: Default recurring schedules
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true)
                     ->whereNull('schedule_date');
    }

    /**
     * Scope: Override/exception schedules
     */
    public function scopeOverride(Builder $query): Builder
    {
        return $query->where('is_override', true)
                     ->whereNotNull('schedule_date');
    }

    /**
     * Scope: For specific day of week
     */
    public function scopeForDay(Builder $query, string $dayOfWeek): Builder
    {
        return $query->where('day_of_week', strtolower($dayOfWeek));
    }

    /**
     * Scope: For specific date
     */
    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('schedule_date', $date);
    }

    /**
     * Scope: Active schedules only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: For specific worker
     */
    public function scopeForWorker(Builder $query, $workerId): Builder
    {
        return $query->where('worker_id', $workerId);
    }

    // ========== HELPER METHODS ==========
    
    /**
     * Get schedule for specific worker on specific date
     * Priority: Override > Default
     */
    public static function getScheduleForDate($workerId, $date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = strtolower($date->format('l')); // 'monday', 'tuesday', etc

        // Priority 1: Check override for this specific date
        $override = self::where('worker_id', $workerId)
            ->where('schedule_date', $date->format('Y-m-d'))
            ->where('is_override', true)
            ->where('status', 'Active')
            ->with(['shift'])
            ->first();

        if ($override) {
            return $override;
        }

        // Priority 2: Get default recurring schedule for this day
        $default = self::where('worker_id', $workerId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_default', true)
            ->where('status', 'Active')
            ->with(['shift'])
            ->first();

        return $default;
    }

    /**
     * Get all workers scheduled for specific date
     * Returns: Collection of schedules (override + default)
     */
    public static function getWorkersForDate($date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = strtolower($date->format('l'));

        // Get all overrides for this date
        $overrides = self::with(['worker', 'shift'])
            ->where('schedule_date', $date->format('Y-m-d'))
            ->where('is_override', true)
            ->where('status', 'Active')
            ->get();

        $overrideWorkerIds = $overrides->pluck('worker_id');

        // Get all default schedules for this day (exclude overridden workers)
        $defaults = self::with(['worker', 'shift'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_default', true)
            ->where('status', 'Active')
            ->whereNotIn('worker_id', $overrideWorkerIds)
            ->get();

        return $overrides->merge($defaults);
    }

    /**
     * Get worker's schedule for date range
     */
    public static function getScheduleForRange($workerId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $schedules = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $schedule = self::getScheduleForDate($workerId, $date);
            
            if ($schedule) {
                $schedules->push([
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $date->format('l'),
                    'schedule' => $schedule,
                    'shift' => $schedule->shift,
                    'type' => $schedule->is_override ? 'override' : 'default',
                ]);
            }
        }

        return $schedules;
    }

    /**
     * Check if worker has schedule on specific date
     */
    public static function hasScheduleOn($workerId, $date): bool
    {
        return self::getScheduleForDate($workerId, $date) !== null;
    }

    /**
     * Create or update default recurring schedule
     */
    public static function setDefaultSchedule($workerId, $shiftId, array $daysOfWeek, $notes = null)
    {
        $schedules = [];

        foreach ($daysOfWeek as $day) {
            $schedule = self::updateOrCreate(
                [
                    'worker_id' => $workerId,
                    'shift_id' => $shiftId,
                    'day_of_week' => strtolower($day),
                    'is_default' => true,
                ],
                [
                    'status' => 'Active',
                    'notes' => $notes,
                ]
            );

            $schedules[] = $schedule;
        }

        return $schedules;
    }

    /**
     * Create override schedule (swap/exception)
     */
    public static function createOverride($workerId, $shiftId, $date, $replacedWorkerId = null, $notes = null)
    {
        return self::create([
            'worker_id' => $workerId,
            'shift_id' => $shiftId,
            'schedule_date' => $date,
            'is_override' => true,
            'replaced_worker_id' => $replacedWorkerId,
            'status' => 'Active',
            'notes' => $notes,
        ]);
    }

    // ========== VALIDATION ==========
    
    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Validation: Must have either day_of_week OR schedule_date
            if (empty($model->day_of_week) && empty($model->schedule_date)) {
                throw new \InvalidArgumentException('Schedule must have either day_of_week (default) or schedule_date (override)');
            }

            // Validation: Default schedule must have day_of_week
            if ($model->is_default && empty($model->day_of_week)) {
                throw new \InvalidArgumentException('Default schedule must have day_of_week');
            }

            // Validation: Override schedule must have schedule_date
            if ($model->is_override && empty($model->schedule_date)) {
                throw new \InvalidArgumentException('Override schedule must have schedule_date');
            }
        });
    }
}
