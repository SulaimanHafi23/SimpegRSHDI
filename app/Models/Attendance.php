<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'worker_id',
        'shift_id',
        'attendance_date',
        'check_in',
        'check_out',
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
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'distance_check_in' => 'integer',
        'distance_check_out' => 'integer',
        'check_in_by_admin' => 'boolean',
        'check_out_by_admin' => 'boolean',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'is_early_leave' => 'boolean',
        'early_leave_minutes' => 'integer',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function checkInAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_in_admin_id')->withTrashed();
    }

    public function checkOutAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_out_admin_id')->withTrashed();
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

    public function getCheckInAdminDisplayNameAttribute(): ?string
    {
        return $this->resolveAdminDisplayName($this->check_in_admin_id, $this->checkInAdmin);
    }

    public function getCheckOutAdminDisplayNameAttribute(): ?string
    {
        return $this->resolveAdminDisplayName($this->check_out_admin_id, $this->checkOutAdmin);
    }

    private function resolveAdminDisplayName(?string $adminId, ?User $resolvedUser = null): ?string
    {
        if (empty($adminId)) {
            return null;
        }

        // 1) ID mengarah ke tabel users (normal case)
        $user = $resolvedUser ?? User::withTrashed()->with('worker')->find($adminId);
        if ($user) {
            return $user->worker?->name
                ?? $user->username
                ?? $user->email
                ?? null;
        }

        // 2) Fallback: data lama kemungkinan menyimpan worker_id langsung
        $worker = Worker::withTrashed()->find($adminId);
        if ($worker) {
            return $worker->name;
        }

        // 3) Fallback: cari user berdasarkan worker_id = adminId
        $userByWorker = User::withTrashed()->with('worker')->where('worker_id', $adminId)->first();
        if ($userByWorker) {
            return $userByWorker->worker?->name
                ?? $userByWorker->username
                ?? $userByWorker->email
                ?? null;
        }

        return null;
    }
}
