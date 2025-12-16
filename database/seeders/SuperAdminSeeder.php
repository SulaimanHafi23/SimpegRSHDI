<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👑 Starting SuperAdminSeeder...');

        // ========== VALIDASI MASTER DATA ==========
        
        $this->command->info('🔍 Checking master data...');
        
        $gender = \App\Models\Gender::firstOrCreate(['name' => 'Laki-laki']);
        $religion = \App\Models\Religion::firstOrCreate(['name' => 'Islam']);
        $department = \App\Models\Department::firstOrCreate(
            ['name' => 'Admin'], 
            ['description' => 'Staff administrasi']
        );

        $this->command->info("   ✅ Gender: {$gender->name}");
        $this->command->info("   ✅ Religion: {$religion->name}");
        $this->command->info("   ✅ Department: {$department->name}");

        // ========== VALIDASI SPATIE ROLE ==========
        
        $superAdminRole = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
        
        if (!$superAdminRole) {
            $this->command->error('❌ Role "Super Admin" tidak ada!');
            $this->command->error('   Run RolePermissionSeeder terlebih dahulu.');
            return;
        }

        $this->command->info("   ✅ Role: {$superAdminRole->name}");

        // ========== CEK DUPLICATE ==========
        
        $existingUser = User::where('username', 'superadmin')->first();
        if ($existingUser) {
            $this->command->warn('⚠️  Super Admin already exists. Skipping...');
            $this->command->info("   Username: {$existingUser->username}");
            $this->command->info("   Email: {$existingUser->email}");
            return;
        }

        $existingWorker = Worker::where('nip', 'SA001')->first();
        if ($existingWorker) {
            $this->command->warn('⚠️  Worker SA001 already exists. Skipping...');
            return;
        }

        // ========== CREATE SUPER ADMIN ==========
        
        $this->command->info('');
        $this->command->info('🔨 Creating Super Admin...');

        try {
            // CREATE WORKER
            $worker = Worker::create([
                'id' => Str::uuid(),
                'nip' => 'NIP-0001',
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'phone_number' => '081234567890',
                'address' => 'Jakarta',
                'birth_date' => '1990-01-01',
                'birth_place' => 'Jakarta',
                'gender_id' => $gender->id,
                'religion_id' => $religion->id,
                'department_id' => $department->id,
                'hire_date' => now(),
                'employment_status' => 'permanent',
                'status' => 'active',
            ]);

            $this->command->info("   ✅ Worker created: {$worker->name} (NIP: {$worker->nip})");

            // CREATE USER
            $user = User::create([
                'id' => Str::uuid(),
                'username' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'worker_id' => $worker->id,
                'is_active' => true,
            ]);

            $this->command->info("   ✅ User created: {$user->username}");

            // ASSIGN ROLE
            $user->assignRole('Super Admin');
            $this->command->info("   ✅ Role assigned: Super Admin");

            // ========== SUCCESS OUTPUT ==========
            
            $this->command->info('');
            $this->command->info('✅ Super Admin created successfully!');
            $this->command->info('');
            $this->command->line('┌─────────────────────────────────────────┐');
            $this->command->line('│         🎉 SUPER ADMIN CREDENTIALS      │');
            $this->command->line('├─────────────────────────────────────────┤');
            $this->command->line('│  📧 Email    : superadmin@rshdi.com     │');
            $this->command->line('│  👤 Username : superadmin               │');
            $this->command->line('│  🔑 Password : password                 │');
            $this->command->line('│  🆔 NIP      : SA001                    │');
            $this->command->line('└─────────────────────────────────────────┘');
            $this->command->info('');

        } catch (\Exception $e) {
            $this->command->error('❌ Failed to create Super Admin!');
            $this->command->error("   Error: {$e->getMessage()}");
            
            // Rollback if worker created but user failed
            if (isset($worker) && $worker->exists) {
                $worker->delete();
                $this->command->warn('   ⚠️  Rollback: Worker deleted.');
            }
        }
    }
}
