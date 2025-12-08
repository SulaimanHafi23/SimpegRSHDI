<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'name' => 'Dokter Umum',
                'description' => 'Dokter praktek umum',
                'has_shift' => true,
            ],
            [
                'name' => 'Dokter Spesialis',
                'description' => 'Dokter spesialis',
                'has_shift' => false,
            ],
            [
                'name' => 'Perawat',
                'description' => 'Perawat rumah sakit',
                'has_shift' => true,
            ],
            [
                'name' => 'Bidan',
                'description' => 'Bidan rumah sakit',
                'has_shift' => true,
            ],
            [
                'name' => 'Apoteker',
                'description' => 'Apoteker farmasi',
                'has_shift' => false,
            ],
            [
                'name' => 'Radiografer',
                'description' => 'Teknisi radiologi',
                'has_shift' => true,
            ],
            [
                'name' => 'Analis Laboratorium',
                'description' => 'Analis laboratorium medis',
                'has_shift' => true,
            ],
            [
                'name' => 'Staff Administrasi',
                'description' => 'Staff administrasi dan keuangan',
                'has_shift' => false,
            ],
            [
                'name' => 'Petugas Loket',
                'description' => 'Petugas loket pendaftaran',
                'has_shift' => true,
            ],
            [
                'name' => 'Security',
                'description' => 'Petugas keamanan',
                'has_shift' => true,
            ],
            [
                'name' => 'Cleaning Service',
                'description' => 'Petugas kebersihan',
                'has_shift' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
