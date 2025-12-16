<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'nip',
        'name',
        'email',
        'phone_number',
        'address',
        'birth_date',
        'birth_place',
        'gender_id',
        'religion_id',
        'department_id',
        'hire_date',
        'resign_date',
        'employment_status',
        'status',
        'photo_url',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'resign_date' => 'date',
    ];

    // Relationships
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function workerShifts(): HasMany
    {
        return $this->hasMany(WorkerShift::class);
    }

    public function shiftOverrides(): HasMany
    {
        return $this->hasMany(ShiftOverrides::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendances::class);
    }

    public function workerDocuments(): HasMany
    {
        return $this->hasMany(WorkerDocument::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    /**
     * Get active worker shift
     */
    public function activeWorkerShift(): HasOne
    {
        return $this->hasOne(WorkerShift::class)
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->latestOfMany();
    }

    /**
     * Get shift for specific date
     */
    public function getShiftForDate(\DateTime $date): ?string
    {
        // Check override first
        $override = $this->shiftOverrides()
            ->where('override_date', $date->format('Y-m-d'))
            ->first();

        if ($override) {
            return $override->shift_id;
        }

        // Get active worker shift
        $workerShift = $this->workerShifts()
            ->where('is_active', true)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date->format('Y-m-d'));
            })
            ->first();

        return $workerShift?->getShiftForDate($date);
    }
}
