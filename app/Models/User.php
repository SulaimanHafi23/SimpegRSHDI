<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ✅ Add this
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, HasApiTokens; // ✅ Add HasApiTokens

    // ✅ IMPORTANT: Specify key type for Spatie
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'worker_id',
        'email',
        'username',
        'password',
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the worker that owns the user.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Check if the user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Check if the user is in HR.
     */
    public function isHR(): bool
    {
        return $this->hasRole('HR');
    }

    /**
     * Check if the user is a Manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    /**
     * Check if the user is a Director.
     */
    public function isDirektur(): bool
    {
        return $this->hasRole('Direktur');
    }
}
