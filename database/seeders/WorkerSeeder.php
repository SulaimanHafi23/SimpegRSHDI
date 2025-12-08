<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Worker;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Position;

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
        $this->command->info('   Positions: ' . Position::count());
        
        // ✅ AMBIL DATA (pakai firstOrCreate untuk safety)
        $genderLaki = Gender::firstOrCreate(['name' => 'Laki-laki']);
        $genderPerempuan = Gender::firstOrCreate(['name' => 'Perempuan']);
        
        $religionIslam = Religion::firstOrCreate(['name' => 'Islam']);
        $religionKristen = Religion::firstOrCreate(['name' => 'Kristen']);
        $religionKatolik = Religion::firstOrCreate(['name' => 'Katolik']);
        
        $positionDokter = Position::firstOrCreate(['name' => 'Dokter'], ['description' => 'Dokter umum dan spesialis']);
        $positionPerawat = Position::firstOrCreate(['name' => 'Perawat'], ['description' => 'Perawat']);
        $positionBidan = Position::firstOrCreate(['name' => 'Bidan'], ['description' => 'Bidan']);
        $positionAdmin = Position::firstOrCreate(['name' => 'Admin'], ['description' => 'Staff administrasi']);

        // ✅ VALIDASI FINAL
        if (!$genderLaki || !$religionIslam || !$positionDokter) {
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
                'position_id' => $positionDokter->id,
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
                'position_id' => $positionDokter->id,
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
                'position_id' => $positionDokter->id,
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
                'position_id' => $positionPerawat->id,
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
                'position_id' => $positionPerawat->id,
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
                'position_id' => $positionPerawat->id,
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
                'position_id' => $positionBidan->id,
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
                'position_id' => $positionBidan->id,
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
                'position_id' => $positionAdmin->id,
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
                'position_id' => $positionAdmin->id,
                'birth_place' => 'Surabaya',
                'birth_date' => '1996-06-10',
                'address' => 'Jl. Diponegoro No. 45, Surabaya',
                'hire_date' => '2020-06-01',
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