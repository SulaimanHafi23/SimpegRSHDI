<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Starting DepartmentSeeder...');

        $departments = [
            [
                'id' => Str::uuid(),
                'name' => 'Dokter',
                'code' => 'DKT',
                'description' => 'Dokter umum dan spesialis',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Perawat',
                'code' => 'PRW',
                'description' => 'Perawat profesional',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Bidan',
                'code' => 'BDN',
                'description' => 'Bidan profesional',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Admin',
                'code' => 'ADM',
                'description' => 'Staff administrasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Farmasi',
                'code' => 'FRM',
                'description' => 'Apoteker dan asisten apoteker',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Laboratorium',
                'code' => 'LAB',
                'description' => 'Analis laboratorium',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Radiologi',
                'code' => 'RAD',
                'description' => 'Radiografer dan teknisi radiologi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Gizi',
                'code' => 'GIZ',
                'description' => 'Ahli gizi dan dietisien',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Kebersihan',
                'code' => 'KBR',
                'description' => 'Cleaning service dan housekeeping',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Keamanan',
                'code' => 'SEC',
                'description' => 'Security dan satpam',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'IT & Digital',
                'code' => 'IT',
                'description' => 'Teknologi informasi dan sistem',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Sumber daya manusia',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Finance & Accounting',
                'code' => 'FIN',
                'description' => 'Keuangan dan akuntansi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('departments')->insert($departments);

        $this->command->info('✅ Created ' . count($departments) . ' departments');
    }
}
