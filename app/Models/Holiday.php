<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'date',
        'description',
        'is_national',
    ];

    protected $casts = [
        'date' => 'date',
        'is_national' => 'boolean',
    ];

    /**
     * Scope for national holidays
     */
    public function scopeNational($query)
    {
        return $query->where('is_national', true);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    /**
     * Get holidays for a specific year
     */
    public function scopeYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }
}
