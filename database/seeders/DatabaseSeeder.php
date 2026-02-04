<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Master Data...');
        $this->call([
            GenderSeeder::class,
            ReligionSeeder::class,
            DepartmentSeeder::class,
            LocationSeeder::class,
            ShiftSeeder::class,
            DocumentTypeSeeder::class,
            DepartmentDocumentTypeSeeder::class, // Maps documents to departments
            LeaveTypeSeeder::class,
            SalaryComponentSeeder::class,
            HolidaySeeder::class,
        ]);

        $this->command->info('🔐 Seeding Roles & Permissions...');
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $this->command->info('👤 Seeding Users & Workers...');
        $this->call([
            SuperAdminSeeder::class,
            WorkerSeeder::class,
            UserSeeder::class,
            HRUserSeeder::class, // HR dedicated user
        ]);

        $this->command->info('📋 Seeding Operational Data...');
        $this->call([
            WorkerShiftSeeder::class,
        ]);

        $this->command->info('📝 Seeding Leave & Business Data...');
        $this->call([
            LeaveRequestSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
    }
}
