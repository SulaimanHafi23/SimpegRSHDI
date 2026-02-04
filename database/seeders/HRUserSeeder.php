<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Worker;
use App\Models\Department;
use App\Models\Religion;
use App\Models\Gender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HRUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👨‍💼 Creating HR User...');

        // Check if HR role exists
        $roleHR = \Spatie\Permission\Models\Role::where('name', 'HR')->first();
        if (!$roleHR) {
            $this->command->error('❌ Role "HR" belum ada! Run RolePermissionSeeder dulu.');
            return;
        }

        // Check if user already exists
        if (User::where('email', 'hr@rshdi.com')->exists()) {
            $this->command->warn('⚠️  HR user sudah ada!');
            return;
        }

        // Get or create necessary data
        $department = Department::where('name', 'Human Resource')->first()
                      ?? Department::where('name', 'LIKE', '%hr%')->first()
                      ?? Department::first();

        $religion = Religion::where('name', 'Islam')->first() ?? Religion::first();
        $gender = Gender::where('name', 'Laki-laki')->first() ?? Gender::first();

        if (!$department || !$religion || !$gender) {
            $this->command->error('❌ Master data belum lengkap! Run master seeders dulu.');
            return;
        }

        // Create Worker for HR
        $worker = Worker::create([
            'nip' => 'HR001',
            'name' => 'HR RSHDI',
            'email' => 'hr@rshdi.com',
            'phone_number' => '081999888777',
            'address' => 'RSUD Haji Darlan Ismail, Kab. Sambas',
            'birth_date' => '1985-01-01',
            'birth_place' => 'Sambas',
            'hire_date' => '2020-01-01',
            'department_id' => $department->id,
            'religion_id' => $religion->id,
            'gender_id' => $gender->id,
            'employment_status' => 'permanent',
            'status' => 'active',
        ]);

        // Create User for HR
        $user = User::create([
            'worker_id' => $worker->id,
            'email' => 'hr@rshdi.com',
            'username' => 'hrrshdi',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Assign HR role
        $user->assignRole($roleHR);

        $this->command->info('✅ HR User berhasil dibuat:');
        $this->command->info("   Email: hr@rshdi.com");
        $this->command->info("   Username: hrrshdi");
        $this->command->info("   Password: password");
        $this->command->info("   Role: HR");
        $this->command->info("   NIP: HR001");
    }
}
