<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'enforce_geofence',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
        'enforce_geofence' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function Attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public function calculateDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Check if coordinates are within radius
     */
    public function isWithinRadius(float $lat, float $lng): bool
    {
        return $this->calculateDistance($lat, $lng) <= $this->radius;
    }
}
