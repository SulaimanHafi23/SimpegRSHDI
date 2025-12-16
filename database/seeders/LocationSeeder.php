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
                'name' => 'Kantor Pusat Jakarta',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius' => 100,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cabang Surabaya',
                'address' => 'Jl. Tunjungan No. 456, Surabaya, Jawa Timur',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'radius' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('locations')->insert($locations);
    }
}
