<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Worker extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'nip',
        'name',
        'surname',
        'frontname',
        'backname',
        'email',
        'address',
        'birth_date',
        'birth_place',
        'gender_id',
        'religion_id',
        'position_id',
        'phone_number',
        'status',
        'hire_date',
        'resign_date',
        'photo_url',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'resign_date' => 'date',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    // ✅ SHIFT SCHEDULES (Recurring + Override)
    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(WorkerShiftSchedule::class);
    }

    // ✅ SHORTCUT: Akses shifts via schedules
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(
            Shift::class,
            'worker_shift_schedules',
            'worker_id',
            'shift_id'
        )
        ->withPivot([
            'day_of_week',
            'schedule_date',
            'is_default',
            'is_override',
            'status',
            'notes'
        ])
        ->withTimestamps();
    }

    // ✅ TAMBAH: Get active shift assignments
    public function activeShifts(): BelongsToMany
    {
        return $this->shifts()
            ->wherePivot('assignment_date', '<=', now())
            ->latest('worker_shift_assigments.assignment_date');
    }

    // Other relationships
    public function absents(): HasMany
    {
        return $this->hasMany(Absent::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function businessTrips(): HasMany
    {
        return $this->hasMany(BusinessTrip::class);
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
    }

    public function salary(): HasOne
    {
        return $this->hasOne(Salary::class);
    }

    // ========== SCOPES ==========
    
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    public function scopeResigned($query)
    {
        return $query->where('status', 'Resigned');
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    // ========== ACCESSORS ==========
    
    /**
     * Get full name with titles
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        
        if ($this->frontname) {
            $name = $this->frontname . ' ' . $name;
        }
        
        if ($this->backname) {
            $name .= ', ' . $this->backname;
        }
        
        return $name;
    }

    /**
     * Get age in years
     * 
     * @return int|null
     */
    public function getAgeAttribute(): ?int
    {
        // ✅ FIX: Check if birth_date exists and is Carbon instance
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /**
     * Get years of service
     * 
     * @return int|null
     */
    public function getYearsOfServiceAttribute(): ?int
    {
        // ✅ FIX: Check if hire_date exists
        if (!$this->hire_date) {
            return null;
        }

        $endDate = $this->resign_date ?? now();
        return $this->hire_date->diffInYears($endDate);
    }

    // ========== HELPERS ==========
    
    /**
     * Check if worker is active
     */
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Check if worker has resigned
     */
    public function hasResigned(): bool
    {
        return $this->status === 'Resigned' && $this->resign_date !== null;
    }

    /**
     * Resign worker
     */
    public function resign($date = null): bool
    {
        $this->status = 'Resigned';
        $this->resign_date = $date ?? now();
        return $this->save();
    }

    /**
     * Reactivate worker
     */
    public function reactivate(): bool
    {
        $this->status = 'Active';
        $this->resign_date = null;
        return $this->save();
    }

    /**
     * Get current shift for today
     */
    public function getCurrentShift()
    {
        $today = now()->format('l'); // Monday, Tuesday, etc.
        
        return $this->shiftSchedules()
            ->where('is_default', true)
            ->where('day_of_week', strtolower($today))
            ->where('status', 'Active')
            ->with('shift')
            ->first();
    }

    /**
     * Get shift for specific date
     */
    public function getShiftForDate($date)
    {
        $checkDate = is_string($date) ? Carbon::parse($date) : $date;
        
        // Check override first
        $override = $this->shiftSchedules()
            ->where('is_override', true)
            ->whereDate('schedule_date', $checkDate)
            ->where('status', 'Active')
            ->with('shift')
            ->first();
        
        if ($override) {
            return $override;
        }
        
        // Check default schedule
        $dayOfWeek = strtolower($checkDate->format('l'));
        
        return $this->shiftSchedules()
            ->where('is_default', true)
            ->where('day_of_week', $dayOfWeek)
            ->where('status', 'Active')
            ->with('shift')
            ->first();
    }

    /**
     * Get all active default schedules
     */
    public function getDefaultSchedules()
    {
        return $this->shiftSchedules()
            ->where('is_default', true)
            ->where('status', 'Active')
            ->with('shift')
            ->get()
            ->groupBy('day_of_week');
    }
}
