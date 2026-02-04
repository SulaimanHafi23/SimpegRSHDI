<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'location_id',
        'attendance_date',
        'check_in',
        'check_out',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'distance_check_in',
        'distance_check_out',
        'check_in_by_admin',
        'check_in_admin_id',
        'check_out_by_admin',
        'check_out_admin_id',
        'status',
        'is_late',
        'late_minutes',
        'is_early_leave',
        'early_leave_minutes',
        'is_outside_radius',
        'overtime_minutes',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'distance_check_in' => 'integer',
        'distance_check_out' => 'integer',
        'check_in_by_admin' => 'boolean',
        'check_out_by_admin' => 'boolean',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'is_early_leave' => 'boolean',
        'early_leave_minutes' => 'integer',
        'is_outside_radius' => 'boolean',
        'overtime_minutes' => 'integer',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function checkInAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_in_admin_id');
    }

    public function checkOutAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_out_admin_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AttendancePhoto::class, 'attendance_id');
    }

    public function checkInPhoto(): HasMany
    {
        return $this->hasMany(AttendancePhoto::class, 'attendance_id')
            ->where('photo_type', 'check_in');
    }

    public function checkOutPhoto(): HasMany
    {
        return $this->hasMany(AttendancePhoto::class, 'attendance_id')
            ->where('photo_type', 'check_out');
    }
}
