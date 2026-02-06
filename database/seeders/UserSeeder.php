<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get workers (exclude Super Admin)
        $workers = Worker::where('nip', '!=', 'SA001')->get();

        if ($workers->isEmpty()) {
            $this->command->warn('⚠️  No workers found. Run WorkerSeeder first.');
            return;
        }

        // Get roles
        $roleUser = \Spatie\Permission\Models\Role::where('name', 'Employee')->first();
        $roleHR = \Spatie\Permission\Models\Role::where('name', 'HR')->first();
        $roleManager = \Spatie\Permission\Models\Role::where('name', 'Manager')->first();

        if (!$roleUser) {
            $this->command->error('❌ Role "Employee" belum ada! Run RolePermissionSeeder dulu.');
            return;
        }

        $this->command->info('👥 Creating users for workers...');

        $count = 0;
        foreach ($workers as $worker) {
            // Skip if user already exists
            if (User::where('worker_id', $worker->id)->exists()) {
                continue;
            }

            // Generate username from name
            $username = strtolower(str_replace([' ', '.', ','], '', explode(',', $worker->name)[0]));

            // Ensure unique username
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Create user
            $user = User::create([
                'worker_id' => $worker->id,
                'email' => $worker->email,
                'username' => $username,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            // Assign role based on NIP
            $workerNip = $worker->nip ?? '';

            if (str_starts_with($workerNip, 'MGR')) {
                // Manager role for MGR001, MGR002, MGR003, MGR004
                $user->assignRole($roleManager);
                $role = 'Manager';
            } elseif (str_starts_with($workerNip, 'HR')) {
                // HR role for HR001
                $user->assignRole($roleHR);
                $role = 'HR';
            } else {
                // Employee role for everyone else
                $user->assignRole($roleUser);
                $role = 'Employee';
            }

            $this->command->info("✅ {$worker->name} → {$username} [{$role}]");
            $count++;
        }

        $this->command->info('');
        $this->command->info("📊 Total users created: {$count}");
        $this->command->info('   Default password: password');
    }
}
