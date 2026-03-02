<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'name',
        'code',
        'max_days_per_year',
        'requires_approval',
        'requires_attachment',
        'days_notice',
        'is_active',
    ];

    protected $casts = [
        'max_days_per_year' => 'integer',
        'requires_approval' => 'boolean',
        'requires_attachment' => 'boolean',
        'days_notice' => 'integer',
        'is_active' => 'boolean',
    ];

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
