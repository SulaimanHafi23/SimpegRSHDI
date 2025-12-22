<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            // 2025
            ['name' => 'Tahun Baru Masehi', 'date' => '2025-01-01', 'description' => 'Tahun Baru 2025'],
            ['name' => 'Isra Mikraj Nabi Muhammad SAW', 'date' => '2025-01-27', 'description' => 'Libur Nasional'],
            ['name' => 'Tahun Baru Imlek 2576', 'date' => '2025-01-29', 'description' => 'Tahun Baru Imlek'],
            ['name' => 'Hari Raya Nyepi (Tahun Baru Saka 1947)', 'date' => '2025-03-29', 'description' => 'Tahun Baru Saka'],
            ['name' => 'Wafat Yesus Kristus', 'date' => '2025-04-18', 'description' => 'Jumat Agung'],
            ['name' => 'Hari Raya Idul Fitri 1446 H', 'date' => '2025-03-31', 'description' => 'Lebaran Hari Ke-1'],
            ['name' => 'Hari Raya Idul Fitri 1446 H', 'date' => '2025-04-01', 'description' => 'Lebaran Hari Ke-2'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2025-03-28', 'description' => 'Cuti Bersama'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2025-04-02', 'description' => 'Cuti Bersama'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2025-04-03', 'description' => 'Cuti Bersama'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2025-04-04', 'description' => 'Cuti Bersama'],
            ['name' => 'Hari Buruh Internasional', 'date' => '2025-05-01', 'description' => 'May Day'],
            ['name' => 'Kenaikan Yesus Kristus', 'date' => '2025-05-29', 'description' => 'Kenaikan Isa Almasih'],
            ['name' => 'Hari Raya Waisak 2569', 'date' => '2025-05-12', 'description' => 'Hari Raya Waisak'],
            ['name' => 'Hari Lahir Pancasila', 'date' => '2025-06-01', 'description' => 'Hari Pancasila'],
            ['name' => 'Hari Raya Idul Adha 1446 H', 'date' => '2025-06-07', 'description' => 'Idul Adha'],
            ['name' => 'Tahun Baru Islam 1447 H', 'date' => '2025-06-27', 'description' => '1 Muharram 1447 H'],
            ['name' => 'Hari Kemerdekaan RI', 'date' => '2025-08-17', 'description' => 'HUT RI Ke-80'],
            ['name' => 'Maulid Nabi Muhammad SAW', 'date' => '2025-09-05', 'description' => 'Maulid Nabi'],
            ['name' => 'Hari Raya Natal', 'date' => '2025-12-25', 'description' => 'Natal'],
            ['name' => 'Cuti Bersama Natal', 'date' => '2025-12-26', 'description' => 'Cuti Bersama'],
            
            // 2026 (beberapa hari untuk kontinuitas)
            ['name' => 'Tahun Baru Masehi', 'date' => '2026-01-01', 'description' => 'Tahun Baru 2026'],
            ['name' => 'Isra Mikraj Nabi Muhammad SAW', 'date' => '2026-01-16', 'description' => 'Libur Nasional'],
            ['name' => 'Tahun Baru Imlek 2577', 'date' => '2026-02-17', 'description' => 'Tahun Baru Imlek'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create([
                'name' => $holiday['name'],
                'date' => $holiday['date'],
                'description' => $holiday['description'],
                'is_national' => true,
            ]);
        }
    }
}
