<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasUuid;

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
        'enforce_geofence' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function absents(): HasMany
    {
        return $this->hasMany(Absent::class);
    }

    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://maps.google.com/?q={$this->latitude},{$this->longitude}";
    }
}
