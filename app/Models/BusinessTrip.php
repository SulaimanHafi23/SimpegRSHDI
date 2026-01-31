<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTrip extends Model
{
    use HasFactory;

    protected $table = 'business_trips';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'worker_id', 'destination', 'purpose', 'start_date', 'end_date', 'estimated_cost', 'status', 'approved_by', 'approved_at', 'rejection_reason', 'itinerary'
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