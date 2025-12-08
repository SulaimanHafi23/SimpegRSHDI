<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ShiftPattern extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'days_of_week',
        'description',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    
}
