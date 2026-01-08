<?php

namespace Database\Seeders;

use App\Models\SalaryComponent;
use Illuminate\Database\Seeder;

class SalaryComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            // Earnings (Pendapatan)
            [
                'code' => 'BASIC',
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'description' => 'Gaji pokok bulanan',
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ALLOWANCE_TRANSPORT',
                'name' => 'Tunjangan Transport',
                'type' => 'earning',
                'description' => 'Tunjangan transportasi',
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ALLOWANCE_MEAL',
                'name' => 'Tunjangan Makan',
                'type' => 'earning',
                'description' => 'Tunjangan makan',
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ALLOWANCE_POSITION',
                'name' => 'Tunjangan Jabatan',
                'type' => 'earning',
                'description' => 'Tunjangan jabatan',
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ALLOWANCE_HEALTH',
                'name' => 'Tunjangan Kesehatan',
                'type' => 'earning',
                'description' => 'Tunjangan kesehatan',
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'OVERTIME',
                'name' => 'Lembur',
                'type' => 'earning',
                'description' => 'Pembayaran lembur',
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'code' => 'BONUS',
                'name' => 'Bonus',
                'type' => 'earning',
                'description' => 'Bonus kinerja',
                'is_taxable' => true,
                'is_active' => true,
            ],

            // Deductions (Potongan)
            [
                'code' => 'TAX_PPH21',
                'name' => 'PPh 21',
                'type' => 'deduction',
                'description' => 'Pajak Penghasilan Pasal 21',
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_KESEHATAN',
                'name' => 'BPJS Kesehatan',
                'type' => 'deduction',
                'description' => 'Iuran BPJS Kesehatan',
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_KETENAGAKERJAAN',
                'name' => 'BPJS Ketenagakerjaan',
                'type' => 'deduction',
                'description' => 'Iuran BPJS Ketenagakerjaan',
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'LOAN',
                'name' => 'Pinjaman',
                'type' => 'deduction',
                'description' => 'Cicilan pinjaman',
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'LATE_DEDUCTION',
                'name' => 'Potongan Terlambat',
                'type' => 'deduction',
                'description' => 'Potongan karena keterlambatan',
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'ABSENT_DEDUCTION',
                'name' => 'Potongan Absen',
                'type' => 'deduction',
                'description' => 'Potongan karena tidak masuk',
                'is_taxable' => false,
                'is_active' => true,
            ],
        ];

        foreach ($components as $component) {
            SalaryComponent::create($component);
        }
    }
}
