<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database dengan data lengkap untuk semua fitur.
     * Run dengan: php artisan db:seed --class=ComprehensiveDatabaseSeeder
     */
    public function run(): void
    {
        // Disable foreign key checks untuk menghindari constraint errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables (optional - hati-hati di production!)
        $this->command->info('🗑️  Membersihkan data lama...');
        $this->truncateTables();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🚀 Memulai seeding data komprehensif...');
        $this->command->newLine();

        // 1. Master Data (Foundation)
        $this->command->info('1️⃣  Seeding Master Data...');
        $this->call([
            DepartmentSeeder::class,
            ShiftSeeder::class,
            ShiftDayTimeSeeder::class,
            LeaveTypeSeeder::class,
            DocumentTypeSeeder::class,
            DepartmentDocumentTypeSeeder::class,
            HolidaySeeder::class,
        ]);
        $this->command->info('✅ Master Data seeded!');
        $this->command->newLine();

        // 2. Roles & Permissions
        $this->command->info('2️⃣  Seeding Roles & Permissions...');
        $this->call([
            RolePermissionSeeder::class,
        ]);
        $this->command->info('✅ Roles & Permissions seeded!');
        $this->command->newLine();

        // 3. Users & Workers
        $this->command->info('3️⃣  Seeding Users & Workers...');
        $this->call([
            SuperAdminSeeder::class,
            UserSeeder::class,
            WorkerSeeder::class,
        ]);
        $this->command->info('✅ Users & Workers seeded!');
        $this->command->newLine();

        // 4. Worker Shifts & Schedules
        $this->command->info('4️⃣  Seeding Worker Shifts & Schedules...');
        $this->call([
            WorkerShiftSeeder::class,
        ]);
        $this->command->info('✅ Worker Shifts seeded!');
        $this->command->newLine();

        // 5. Attendance (Enhanced with various scenarios)
        $this->command->info('5️⃣  Seeding Attendance Data (Enhanced)...');
        $this->call([
            EnhancedAttendanceSeeder::class,
        ]);
        $this->command->info('✅ Attendance seeded!');
        $this->command->newLine();

        // 6. Leave Requests (Enhanced with various statuses)
        $this->command->info('6️⃣  Seeding Leave Requests (Enhanced)...');
        $this->call([
            EnhancedLeaveRequestSeeder::class,
        ]);
        $this->command->info('✅ Leave Requests seeded!');
        $this->command->newLine();

        // 7. Worker Documents (Enhanced with various statuses)
        $this->command->info('7️⃣  Seeding Worker Documents (Enhanced)...');
        $this->call([
            EnhancedWorkerDocumentSeeder::class,
        ]);
        $this->command->info('✅ Worker Documents seeded!');
        $this->command->newLine();

        // 8. Shift Swap Requests (Comprehensive with various scenarios)
        $this->command->info('8️⃣  Seeding Shift Swap Requests (Comprehensive)...');
        $this->call([
            ComprehensiveShiftSwapSeeder::class,
        ]);
        $this->command->info('✅ Shift Swap Requests seeded!');
        $this->command->newLine();

        // 9. Business Trips (Comprehensive with various statuses)
        $this->command->info('9️⃣  Seeding Business Trips (Comprehensive)...');
        $this->call([
            ComprehensiveBusinessTripSeeder::class,
        ]);
        $this->command->info('✅ Business Trips seeded!');
        $this->command->newLine();

        // 11. Notifications
        $this->command->info('📬 Seeding Notifications...');
        $this->call([
            NotificationSeeder::class,
        ]);
        $this->command->info('✅ Notifications seeded!');
        $this->command->newLine();

        $this->command->info('🎉 Seeding komprehensif selesai!');
        $this->command->newLine();
        $this->printSummary();
    }

    /**
     * Truncate tables untuk reset data
     */
    private function truncateTables(): void
    {
        $tables = [
            'notifications',
            'business_trips',
            'shift_swap_requests',
            'shift_swap_audit_logs',
            'worker_documents',
            'leave_requests',
            'attendance_photos',
            'attendances',
            'worker_shifts',
            'shift_overrides',
            'workers',
            'users',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'permissions',
            'roles',
            'holidays',
            'department_document_types',
            'document_types',
            'leave_types',
            'shifts',
            'locations',
            'departments',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
    }

    /**
     * Print summary setelah seeding
     */
    private function printSummary(): void
    {
        $this->command->info('📊 Summary Data yang di-seed:');
        $this->command->table(
            ['Table', 'Count'],
            [
                ['Departments', DB::table('departments')->count()],
                ['Locations', DB::table('locations')->count()],
                ['Shifts', DB::table('shifts')->count()],
                ['Leave Types', DB::table('leave_types')->count()],
                ['Document Types', DB::table('document_types')->count()],
                ['Holidays', DB::table('holidays')->count()],
                ['Roles', DB::table('roles')->count()],
                ['Permissions', DB::table('permissions')->count()],
                ['Users', DB::table('users')->count()],
                ['Workers', DB::table('workers')->count()],
                ['Worker Shifts', DB::table('worker_shifts')->count()],
                ['Attendances', DB::table('attendances')->count()],
                ['Leave Requests', DB::table('leave_requests')->count()],
                ['Worker Documents', DB::table('worker_documents')->count()],
                ['Shift Swap Requests', DB::table('shift_swap_requests')->count()],
                ['Business Trips', DB::table('business_trips')->count()],
                ['Notifications', DB::table('notifications')->count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔐 Login Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'admin@rshdi.com', 'password'],
                ['HR', 'hr@rshdi.com', 'password'],
                ['Manager IT', 'manager.it@rshdi.com', 'password'],
                ['Manager Nursing', 'manager.nursing@rshdi.com', 'password'],
                ['Employee', 'employee1@rshdi.com', 'password'],
                ['Employee', 'employee2@rshdi.com', 'password'],
            ]
        );
    }
}
