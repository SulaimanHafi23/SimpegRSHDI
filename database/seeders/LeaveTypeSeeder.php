<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Tahunan',
                'code' => 'CT',
                'max_days_per_year' => 12,
                'requires_approval' => true,
                'requires_attachment' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Sakit',
                'code' => 'CS',
                'max_days_per_year' => 30,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Melahirkan',
                'code' => 'CM',
                'max_days_per_year' => 90,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Menikah',
                'code' => 'CK',
                'max_days_per_year' => 3,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Tanpa Gaji',
                'code' => 'CTG',
                'max_days_per_year' => 365,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Haji',
                'code' => 'CH',
                'max_days_per_year' => 40,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Umroh',
                'code' => 'CU',
                'max_days_per_year' => 14,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Besar',
                'code' => 'CB',
                'max_days_per_year' => 60,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Alasan Penting',
                'code' => 'CAP',
                'max_days_per_year' => 7,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Diluar Tanggungan Negara',
                'code' => 'CDTN',
                'max_days_per_year' => 180,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Cuti Lainnya',
                'code' => 'CTL',
                'max_days_per_year' => 15,
                'requires_approval' => true,
                'requires_attachment' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('leave_types')->insert($leaveTypes);
    }
}
