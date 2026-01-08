<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'id' => Str::uuid(),
                'name' => 'Rumah Sakit Haji Darlan Ismail',
                'address' => 'Jl. Haji Darlan Ismail, Banjarmasin, Kalimantan Selatan',
                'latitude' => -3.5794142510657774,
                'longitude' => 114.62778236571398,
                'radius' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('locations')->insert($locations);
    }
}
