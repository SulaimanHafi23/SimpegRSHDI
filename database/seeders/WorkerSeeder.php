<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Gender;
use App\Models\Worker;
use App\Models\Religion;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏥 Starting WorkerSeeder...');

        // ========== DEBUG: CEK DATA MASTER ==========
        $this->command->info('🔍 Checking master data...');
        $this->command->info('   Genders: ' . Gender::count());
        $this->command->info('   Religions: ' . Religion::count());
        $this->command->info('   Positions: ' . Department::count());

        // ✅ AMBIL DATA (pakai firstOrCreate untuk safety)
        $genderLaki = Gender::firstOrCreate(['name' => 'Laki-laki']);
        $genderPerempuan = Gender::firstOrCreate(['name' => 'Perempuan']);

        $religionIslam = Religion::firstOrCreate(['name' => 'Islam']);
        $religionKristen = Religion::firstOrCreate(['name' => 'Kristen']);
        $religionKatolik = Religion::firstOrCreate(['name' => 'Katolik']);

        $departmentDokter = Department::firstOrCreate(['name' => 'Dokter'], ['description' => 'Dokter umum dan spesialis']);
        $departmentPerawat = Department::firstOrCreate(['name' => 'Perawat'], ['description' => 'Perawat']);
        $departmentBidan = Department::firstOrCreate(['name' => 'Bidan'], ['description' => 'Bidan']);
        $departmentAdmin = Department::firstOrCreate(['name' => 'Admin'], ['description' => 'Staff administrasi']);

        // ✅ VALIDASI FINAL
        if (!$genderLaki || !$religionIslam || !$departmentDokter) {
            $this->command->error('❌ Failed to create/find master data!');
            return;
        }

        $this->command->info('✅ Master data OK');
        $this->command->info('');
        $this->command->info('👥 Creating workers...');

        // ========== DATA WORKERS ==========

        $workers = [
            // DOKTER
            [
                'name' => 'Dr. Ahmad Dahlan, Sp.PD',
                'nip' => 'DKT001',
                'email' => 'ahmad.dahlan@rshdi.com',
                'phone_number' => '081234567801',
                'gender_id' => $genderLaki->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentDokter->id,
                'birth_place' => 'Jakarta',
                'birth_date' => '1985-03-15',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'hire_date' => '2015-01-10',
                'status' => 'Active',
            ],
            [
                'name' => 'Dr. Siti Nurhaliza, Sp.A',
                'nip' => 'DKT002',
                'email' => 'siti.nurhaliza@rshdi.com',
                'phone_number' => '081234567802',
                'gender_id' => $genderPerempuan->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentDokter->id,
                'birth_place' => 'Bandung',
                'birth_date' => '1988-07-20',
                'address' => 'Jl. Asia Afrika No. 45, Bandung',
                'hire_date' => '2016-03-15',
                'status' => 'Active',
            ],
            [
                'name' => 'Dr. Budi Santoso, Sp.B',
                'nip' => 'DKT003',
                'email' => 'budi.santoso@rshdi.com',
                'phone_number' => '081234567803',
                'gender_id' => $genderLaki->id,
                'religion_id' => $religionKristen->id,
                'department_id' => $departmentDokter->id,
                'birth_place' => 'Surabaya',
                'birth_date' => '1982-11-08',
                'address' => 'Jl. Pemuda No. 78, Surabaya',
                'hire_date' => '2014-08-20',
                'status' => 'Active',
            ],

            // PERAWAT
            [
                'name' => 'Ani Kusuma, S.Kep',
                'nip' => 'PRW001',
                'email' => 'ani.kusuma@rshdi.com',
                'phone_number' => '081234567811',
                'gender_id' => $genderPerempuan->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentPerawat->id,
                'birth_place' => 'Yogyakarta',
                'birth_date' => '1992-05-12',
                'address' => 'Jl. Malioboro No. 56, Yogyakarta',
                'hire_date' => '2018-02-01',
                'status' => 'Active',
            ],
            [
                'name' => 'Dedi Firmansyah, S.Kep',
                'nip' => 'PRW002',
                'email' => 'dedi.firmansyah@rshdi.com',
                'phone_number' => '081234567812',
                'gender_id' => $genderLaki->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentPerawat->id,
                'birth_place' => 'Medan',
                'birth_date' => '1990-09-25',
                'address' => 'Jl. Gatot Subroto No. 12, Medan',
                'hire_date' => '2017-06-15',
                'status' => 'Active',
            ],
            [
                'name' => 'Rina Wijaya, S.Kep',
                'nip' => 'PRW003',
                'email' => 'rina.wijaya@rshdi.com',
                'phone_number' => '081234567813',
                'gender_id' => $genderPerempuan->id,
                'religion_id' => $religionKristen->id,
                'department_id' => $departmentPerawat->id,
                'birth_place' => 'Semarang',
                'birth_date' => '1994-02-18',
                'address' => 'Jl. Pandanaran No. 89, Semarang',
                'hire_date' => '2019-01-10',
                'status' => 'Active',
            ],

            // BIDAN
            [
                'name' => 'Sari Rahmawati, A.Md.Keb',
                'nip' => 'BDN001',
                'email' => 'sari.rahmawati@rshdi.com',
                'phone_number' => '081234567821',
                'gender_id' => $genderPerempuan->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentBidan->id,
                'birth_place' => 'Solo',
                'birth_date' => '1991-04-30',
                'address' => 'Jl. Slamet Riyadi No. 34, Solo',
                'hire_date' => '2017-09-01',
                'status' => 'Active',
            ],
            [
                'name' => 'Dewi Lestari, A.Md.Keb',
                'nip' => 'BDN002',
                'email' => 'dewi.lestari@rshdi.com',
                'phone_number' => '081234567822',
                'gender_id' => $genderPerempuan->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentBidan->id,
                'birth_place' => 'Malang',
                'birth_date' => '1993-08-14',
                'address' => 'Jl. Ijen No. 67, Malang',
                'hire_date' => '2019-03-15',
                'status' => 'Active',
            ],

            // ADMIN
            [
                'name' => 'Agus Setiawan',
                'nip' => 'ADM001',
                'email' => 'agus.setiawan@rshdi.com',
                'phone_number' => '081234567831',
                'gender_id' => $genderLaki->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentAdmin->id,
                'birth_place' => 'Jakarta',
                'birth_date' => '1995-01-20',
                'address' => 'Jl. Thamrin No. 90, Jakarta',
                'hire_date' => '2020-01-15',
                'status' => 'Active',
            ],
            [
                'name' => 'Maya Sari',
                'nip' => 'ADM002',
                'email' => 'maya.sari@rshdi.com',
                'phone_number' => '081234567832',
                'gender_id' => $genderPerempuan->id,
                'religion_id' => $religionKatolik->id,
                'department_id' => $departmentAdmin->id,
                'birth_place' => 'Surabaya',
                'birth_date' => '1996-06-10',
                'address' => 'Jl. Diponegoro No. 45, Surabaya',
                'hire_date' => '2020-06-01',
                'status' => 'Active',
            ],

            // MANAGER
            [
                'name' => 'Bapak Manager RS',
                'nip' => 'MGR001',
                'email' => 'manager@rshdi.com',
                'phone_number' => '081234567900',
                'gender_id' => $genderLaki->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentAdmin->id,
                'birth_place' => 'Banjarmasin',
                'birth_date' => '1980-05-15',
                'address' => 'Jl. Ahmad Yani No. 100, Banjarmasin',
                'hire_date' => '2010-01-01',
                'status' => 'Active',
            ],

            // USER HARI
            [
                'name' => 'Hari Prasetyo',
                'nip' => 'EMP001',
                'email' => 'hari.prasetyo@rshdi.com',
                'phone_number' => '081234567901',
                'gender_id' => $genderLaki->id,
                'religion_id' => $religionIslam->id,
                'department_id' => $departmentAdmin->id,
                'birth_place' => 'Banjarmasin',
                'birth_date' => '1995-12-20',
                'address' => 'Jl. Lambung Mangkurat No. 50, Banjarmasin',
                'hire_date' => '2022-01-10',
                'status' => 'Active',
            ],
        ];

        // ========== CREATE WORKERS ==========

        foreach ($workers as $workerData) {
            Worker::create($workerData);
            $this->command->info("   ✅ {$workerData['name']}");
        }

        $this->command->info('');
        $this->command->info("📊 Total workers created: " . count($workers));
    }
}
