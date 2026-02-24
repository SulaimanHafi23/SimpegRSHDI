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
        'worker_id', 'destination', 'purpose', 'start_date', 'end_date', 'estimated_cost', 'status', 'approved_by', 'approved_at', 'rejection_reason', 'itinerary'
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
}
