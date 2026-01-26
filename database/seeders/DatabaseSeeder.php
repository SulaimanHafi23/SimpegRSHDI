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
        ]);

        $this->command->info('📋 Seeding Operational Data...');
        $this->call([
            WorkerShiftSeeder::class,
            AttendanceSeeder::class,
            WorkerDocumentSeeder::class,
        ]);

        $this->command->info('📝 Seeding Leave & Business Data...');
        $this->call([
            LeaveRequestSeeder::class,
            OvertimeRequestSeeder::class,
            BusinessTripSeeder::class,
            ShiftSwapRequestSeeder::class,
        ]);

        $this->command->info('💰 Seeding Payroll Data...');
        $this->call([
            PayrollSeeder::class,
        ]);

        $this->command->info('🔔 Seeding Notifications...');
        $this->call([
            NotificationSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
    }
}
