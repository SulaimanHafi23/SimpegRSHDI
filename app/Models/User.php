<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Auditable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUuids, HasRoles, SoftDeletes, Auditable;

    protected $auditExclude = ['password', 'remember_token', 'last_login', 'email_verified_at', 'photo'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'worker_id',
        'name',
        'email',
        'username',
        'photo',
        'password',
        'email_verified_at',
        'last_login',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the worker that owns the user.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function createdShiftOverrides(): HasMany
    {
        return $this->hasMany(ShiftOverride::class, 'created_by');
    }

    public function verifiedDocuments(): HasMany
    {
        return $this->hasMany(WorkerDocument::class, 'verified_by');
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function approvedOvertimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class, 'approved_by');
    }
}
