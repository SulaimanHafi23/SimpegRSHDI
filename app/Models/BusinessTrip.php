<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessTrip extends Model
{
    use HasUuid;

    protected $fillable = [
        'worker_id',
        'title',
        'destination',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'purpose',
        'start_date',
        'end_date',
        'total_days',
        'budget',
        'actual_cost',
        'transport_type',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'attachment_url',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function absents(): HasMany
    {
        return $this->hasMany(Absent::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(BusinessTripReport::class);
    }

    public function getDestinationMapsUrlAttribute(): string
    {
        if (!$this->destination_latitude || !$this->destination_longitude) {
            return '';
        }
        return "https://maps.google.com/?q={$this->destination_latitude},{$this->destination_longitude}";
    }
}
