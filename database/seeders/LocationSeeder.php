<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create([
            'name' => 'RS Haji Sulaiman - Kantor Utama',
            'address' => 'Jl. Rumah Sakit, Banjarmasin, Kalimantan Selatan',
            'latitude' => config('geofence.lat', -3.5792793888507655),
            'longitude' => config('geofence.lng', 114.62786483938096),
            'radius' => config('geofence.radius', 100),
            'enforce_geofence' => config('geofence.enforce', true),
            'is_active' => true,
        ]);
    }
}
