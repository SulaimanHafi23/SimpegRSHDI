<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Traits\Auditable;

class Worker extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $auditExclude = ['photo_url'];

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
        'shift_id',
        'hire_date',
        'resign_date',
        'employment_status',
        'payroll_category',
        'payroll_payment_type',
        'base_salary',
        'rank',
        'rank_level',
        'weekly_work_hours',
        'outsourced_vendor',
        'outsourced_contract_start',
        'outsourced_contract_end',
        'status',
        'photo_url',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'resign_date' => 'date',
        'base_salary' => 'decimal:2',
        'weekly_work_hours' => 'integer',
        'outsourced_contract_start' => 'date',
        'outsourced_contract_end' => 'date',
    ];

    public function isOutsourced(): bool
    {
        return $this->payroll_category === 'outsourced';
    }

    public function isPartTimePppk(): bool
    {
        return $this->payroll_category === 'pppk_paruh_waktu';
    }

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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
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
        return $this->hasMany(ShiftOverride::class);
    }

    public function shiftHistories(): HasMany
    {
        return $this->hasMany(WorkerShiftHistory::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
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

    public function salaryComponentAssignments(): HasMany
    {
        return $this->hasMany(WorkerSalaryComponent::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function promotionRequests(): HasMany
    {
        return $this->hasMany(PromotionRequest::class);
    }

    public function promotionHistories(): HasMany
    {
        return $this->hasMany(PromotionHistory::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function shiftSwapRequestsAsRequester(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'requester_id');
    }

    public function shiftSwapRequestsAsTarget(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'target_worker_id');
    }

    public function offDayExceptions(): HasMany
    {
        return $this->hasMany(WorkerOffDayException::class);
    }

    public function offDays(): HasMany
    {
        return $this->hasMany(WorkerOffDay::class);
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
     * Note: Consider off-days (exceptions + patterns) before returning shift
     * For overnight shifts: off-day check only affects CHECK-IN date,
     * allowing check-out on off-day if shift started previous day
     */
    public function getShiftForDate(\DateTime $date): ?string
    {
        // Check if this date is an off-day
        // For check-in: reject if off-day
        if ($this->isOffDay($date)) {
            return null;
        }

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

    /**
     * Check if date is an off-day (exception or pattern-based)
     */
    public function isOffDay(\DateTime $date): bool
    {
        // Check exceptions first (single or recurring)
        if (WorkerOffDayException::isOffDay($this->id, $date)) {
            return true;
        }

        // Check pattern-based off-days
        if (WorkerOffDay::isOffDayByPattern($this->id, $date)) {
            return true;
        }

        return false;
    }

    /**
     * Check if worker can check-out on date (allow if shift started previous day)
     * Used for overnight shift handling
     */
    public function canCheckOutOnDate(\DateTime $checkOutDate, \DateTime $checkInDate): bool
    {
        // If check-out date is off-day but check-in was on previous day, allow
        if ($this->isOffDay($checkOutDate) && $checkInDate->format('Y-m-d') !== $checkOutDate->format('Y-m-d')) {
            return true;
        }

        return false;
    }

    /**
     * Get current shift for today
     */
    public function getCurrentShift(): ?Shift
    {
        $today = now();

        // Check override first
        $override = $this->shiftOverrides()
            ->where('override_date', $today->format('Y-m-d'))
            ->with('shift')
            ->first();

        if ($override && $override->shift) {
            return $override->shift;
        }

        // Get active worker shift
        $workerShift = $this->workerShifts()
            ->where('is_active', true)
            ->where('effective_from', '<=', $today->format('Y-m-d'))
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $today->format('Y-m-d'));
            })
            ->with('shift')
            ->first();

        if ($workerShift && $workerShift->shift) {
            return $workerShift->shift;
        }

        // Fallback to default shift
        return $this->shift;
    }

    /**
     * Resolve effective shift for a specific date.
     *
     * Priority:
     * 1) Shift override on that date (including executed shift swap override)
     * 2) Active worker shift assignment on that date
     * 3) Default worker shift
     */
    public function resolveShiftForDate($date): array
    {
        $dateObj = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateStr = $dateObj->format('Y-m-d');

        $override = $this->shiftOverrides()
            ->whereDate('override_date', $dateStr)
            ->with([
                'shift',
                'shiftSwapRequest.requester',
                'shiftSwapRequest.targetWorker',
            ])
            ->first();

        if ($override && $override->shift) {
            $swapRequest = $override->shiftSwapRequest;
            $swapWithName = null;

            if ($swapRequest) {
                $swapWithName = $swapRequest->requester_id === $this->id
                    ? optional($swapRequest->targetWorker)->name
                    : optional($swapRequest->requester)->name;
            }

            return [
                'shift' => $override->shift,
                'schedule' => $override->shift->getScheduleForDate($dateObj),
                'source' => $override->shift_swap_request_id ? 'shift_swap' : 'override',
                'override' => $override,
                'swap_request' => $swapRequest,
                'swap_with_name' => $swapWithName,
            ];
        }

        $workerShift = $this->workerShifts()
            ->where('is_active', true)
            ->where('effective_from', '<=', $dateStr)
            ->where(function ($query) use ($dateStr) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $dateStr);
            })
            ->with('shift')
            ->orderByDesc('effective_from')
            ->first();

        if ($workerShift && $workerShift->shift) {
            return [
                'shift' => $workerShift->shift,
                'schedule' => $workerShift->shift->getScheduleForDate($dateObj),
                'source' => 'worker_shift',
                'override' => null,
                'swap_request' => null,
                'swap_with_name' => null,
            ];
        }

        if ($this->shift) {
            return [
                'shift' => $this->shift,
                'schedule' => $this->shift->getScheduleForDate($dateObj),
                'source' => 'default_shift',
                'override' => null,
                'swap_request' => null,
                'swap_with_name' => null,
            ];
        }

        return [
            'shift' => null,
            'schedule' => null,
            'source' => 'none',
            'override' => null,
            'swap_request' => null,
            'swap_with_name' => null,
        ];
    }
}
