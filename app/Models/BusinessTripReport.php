<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTripReport extends Model
{
    use HasUuid;

    protected $fillable = [
        'business_trip_id',
        'report_title',
        'report_content',
        'attachment_url',
        'submitted_at',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function businessTrip(): BelongsTo
    {
        return $this->belongsTo(BusinessTrip::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
