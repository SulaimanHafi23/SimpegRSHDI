<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Worker;
use App\Models\Department;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;

class WorkerLeaveStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data master yang diperlukan
        $perawatDept = Department::firstOrCreate(
            ['code' => 'PERAWAT'],
            ['name' => 'Perawat']
        );
        
        $gender = Gender::first();
        $religion = Religion::first();

        // Ambil atau buat leave types
        $cutiType = LeaveType::firstOrCreate(
            ['code' => 'CUTI'],
            [
                'name' => 'Cuti Tahunan',
                'max_days_per_year' => 12,
                'requires_approval' => true,
                'requires_attachment' => false,
                'days_notice' => 3,
                'is_active' => true,
            ]
        );

        $sakitType = LeaveType::firstOrCreate(
            ['code' => 'SAKIT'],
            [
                'name' => 'Sakit',
                'max_days_per_year' => null,
                'requires_approval' => true,
                'requires_attachment' => true,
                'days_notice' => 0,
                'is_active' => true,
            ]
        );

        $izinType = LeaveType::firstOrCreate(
            ['code' => 'IZIN'],
            [
                'name' => 'Izin',
                'max_days_per_year' => null,
                'requires_approval' => true,
                'requires_attachment' => false,
                'days_notice' => 1,
                'is_active' => true,
            ]
        );

        // Hitung tanggal - mulai dari Sabtu besok (8 Februari 2026)
        $startDate = Carbon::parse('2026-02-08'); // Sabtu
        $endDateCuti = $startDate->copy()->addDays(9); // 10 hari total

        // 1. Ani Kusuma - Cuti 10 hari dari Sabtu
        $ani = Worker::updateOrCreate(
            ['nip' => 'PRW001'],
            [
                'name' => 'Ani Kusuma, S.Kep',
                'email' => 'ani.kusuma@rshdi.co.id',
                'phone_number' => '081234567001',
                'address' => 'Bumi Harapan, Tanah Laut',
                'birth_date' => '1995-05-15',
                'birth_place' => 'Banjarmasin',
                'gender_id' => $gender->id,
                'religion_id' => $religion->id,
                'department_id' => $perawatDept->id,
                'hire_date' => '2020-01-10',
                'employment_status' => 'permanent',
                'status' => 'active',
            ]
        );

        // Hapus leave request lama untuk Ani jika ada
        LeaveRequest::where('worker_id', $ani->id)->delete();
        
        LeaveRequest::create([
            'worker_id' => $ani->id,
            'leave_type_id' => $cutiType->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDateCuti->format('Y-m-d'),
            'total_days' => 10,
            'reason' => 'Cuti tahunan untuk keperluan keluarga',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // 2. Dedi Firmansyah - Sakit (hari ini)
        $dedi = Worker::updateOrCreate(
            ['nip' => 'PRW002'],
            [
                'name' => 'Dedi Firmansyah, S.Kep',
                'email' => 'dedi.firmansyah@rshdi.co.id',
                'phone_number' => '081234567002',
                'address' => 'Bumi Makmur, Tanah Laut',
                'birth_date' => '1993-08-22',
                'birth_place' => 'Pelaihari',
                'gender_id' => $gender->id,
                'religion_id' => $religion->id,
                'department_id' => $perawatDept->id,
                'hire_date' => '2019-03-15',
                'employment_status' => 'permanent',
                'status' => 'active',
            ]
        );

        // Hapus leave request lama untuk Dedi jika ada
        LeaveRequest::where('worker_id', $dedi->id)->delete();

        LeaveRequest::create([
            'worker_id' => $dedi->id,
            'leave_type_id' => $sakitType->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'total_days' => 1,
            'reason' => 'Sakit demam dan flu',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // 3. Rina Wijaya - Izin (hari ini)
        $rina = Worker::updateOrCreate(
            ['nip' => 'PRW003'],
            [
                'name' => 'Rina Wijaya, S.Kep',
                'email' => 'rina.wijaya@rshdi.co.id',
                'phone_number' => '081234567003',
                'address' => 'Bumi Harapan, Tanah Laut',
                'birth_date' => '1996-03-10',
                'birth_place' => 'Banjarbaru',
                'gender_id' => $gender->id,
                'religion_id' => $religion->id,
                'department_id' => $perawatDept->id,
                'hire_date' => '2021-06-01',
                'employment_status' => 'contract',
                'status' => 'active',
            ]
        );

        // Hapus leave request lama untuk Rina jika ada
        LeaveRequest::where('worker_id', $rina->id)->delete();

        LeaveRequest::create([
            'worker_id' => $rina->id,
            'leave_type_id' => $izinType->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'total_days' => 1,
            'reason' => 'Izin keperluan keluarga mendesak',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->command->info('✅ Berhasil membuat 3 pegawai dengan status:');
        $this->command->info('   - Ani Kusuma (PRW001): Cuti 10 hari (8-17 Feb 2026)');
        $this->command->info('   - Dedi Firmansyah (PRW002): Sakit hari ini');
        $this->command->info('   - Rina Wijaya (PRW003): Izin hari ini');
    }
}
