<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Enums\AbsentStatus;
use App\Enums\AbsentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absent extends Model
{
    use HasUuid;

    protected $fillable = [
        'worker_id',
        'location_id',
        'worker_shift_schedule_id',
        'business_trip_id',
        'check_in',
        'check_out',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'distance_from_office',
        'reason',
        'status',
        'absent_type',
        'present_by_admin',
        'is_late',
        'late_minutes',
        'is_outside_radius',
        'notes',
        'photo_check_in',
        'photo_check_out',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'distance_from_office' => 'decimal:2',
        'present_by_admin' => 'boolean',
        'is_late' => 'boolean',
        'is_outside_radius' => 'boolean',
        'status' => AbsentStatus::class,
        'absent_type' => AbsentType::class,
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function workerShiftSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkerShiftSchedule::class);
    }

    public function businessTrip(): BelongsTo
    {
        return $this->belongsTo(BusinessTrip::class);
    }

    public function getCheckInMapsUrlAttribute(): string
    {
        if (!$this->check_in_latitude || !$this->check_in_longitude) {
            return '';
        }
        return "https://maps.google.com/?q={$this->check_in_latitude},{$this->check_in_longitude}";
    }

    public function getCheckOutMapsUrlAttribute(): string
    {
        if (!$this->check_out_latitude || !$this->check_out_longitude) {
            return '';
        }
        return "https://maps.google.com/?q={$this->check_out_latitude},{$this->check_out_longitude}";
    }
}
