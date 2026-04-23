<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomITSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai Custom IT Seeder...');

        // 1. Seed Roles and Permissions
        $this->command->info('🔑 Seeding Role & Permission...');
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // 2. Seed Department IT dan Admin
        $this->command->info('🏢 Seeding Departemen IT & Admin...');
        $itDept = [
            'id' => Str::uuid(),
            'name' => 'IT & Digital',
            'code' => 'IT',
            'description' => 'Teknologi informasi dan sistem',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $adminDept = [
            'id' => Str::uuid(),
            'name' => 'Admin',
            'code' => 'ADM',
            'description' => 'Staff administrasi',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('departments')->insert([$itDept, $adminDept]);
        $this->command->info('✅ Departemen IT & Admin berhasil dibuat!');

        // 3. Seed Super Admin
        $this->command->info('👤 Seeding Data Admin...');
        $this->call([
            SuperAdminSeeder::class,
        ]);

        $this->command->info('🎉 Custom IT Seeding Selesai!');
    }
}
