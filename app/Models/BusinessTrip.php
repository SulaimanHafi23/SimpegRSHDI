<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTrip extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'business_trips';

    protected $fillable = [
        'worker_id',
        'destination',
        'purpose',
        'start_date',
        'end_date',
        'trip_duration_type',
        'half_day_session',
        'transportation',
        'accommodation',
        'notes',
        'estimated_cost',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'itinerary',
    ];

    protected $casts = [
        'itinerary' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getHalfDaySessionLabelAttribute(): ?string
    {
        return match ($this->half_day_session) {
            'pagi' => 'Pagi',
            'siang' => 'Siang',
            default => null,
        };
    }

    public function getDurationValueAttribute(): float|int
    {
        if ($this->trip_duration_type === 'half_day') {
            return 0.5;
        }

        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        return \Carbon\Carbon::parse($this->start_date)->diffInDays(\Carbon\Carbon::parse($this->end_date)) + 1;
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->trip_duration_type === 'half_day') {
            $session = $this->half_day_session_label ? ' (' . $this->half_day_session_label . ')' : '';

            return '0.5 hari' . $session;
        }

        return $this->duration_value . ' hari';
    }
}
