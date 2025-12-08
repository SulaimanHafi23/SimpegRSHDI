<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== CREATE PERMISSIONS ==========
        
        $permissions = [
            // System
            'system.manage',
            'system.config',
            'audit.view',
            
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Workers
            'workers.view',
            'workers.create',
            'workers.edit',
            'workers.delete',
            
            // Documents
            'documents.view',
            'documents.upload',
            'documents.verify',
            'documents.delete',
            
            // Shifts
            'shifts.view',
            'shifts.manage',
            'roster.manage',
            
            // Attendance
            'attendance.view',
            'attendance.view-all',
            'attendance.manual-input',
            
            // Leave
            'leave.view',
            'leave.request',
            'leave.approve',
            
            // Overtime
            'overtime.view',
            'overtime.request',
            'overtime.approve',
            
            // Business Trip
            'business-trip.view',
            'business-trip.request',
            'business-trip.approve',
            
            // Salary
            'salary.view',
            'salary.view-all',
            'salary.manage',
            'salary.process',
            
            // Reports
            'reports.hr',
            'reports.payroll',
            'reports.executive',
            'reports.export',
            
            // Promotion
            'promotion.check',
            'promotion.process',
            'promotion.approve',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $this->command->info('✅ Permissions created: ' . count($permissions));

        // ========== CREATE ROLES & ASSIGN PERMISSIONS ==========

        // 1. SUPER ADMIN
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());
        $this->command->info('✅ Super Admin role created (all permissions)');

        // 2. HR
        $hr = Role::create(['name' => 'HR']);
        $hr->givePermissionTo([
            'workers.view', 'workers.create', 'workers.edit',
            'documents.view', 'documents.verify',
            'shifts.view', 'shifts.manage', 'roster.manage',
            'attendance.view-all', 'attendance.manual-input',
            'leave.view', 'leave.approve',
            'overtime.view', 'overtime.approve',
            'business-trip.view', 'business-trip.approve',
            'promotion.check', 'promotion.process',
            'reports.hr', 'reports.export',
        ]);
        $this->command->info('✅ HR role created');

        // 3. FINANCE/PAYROLL
        $finance = Role::create(['name' => 'Finance']);
        $finance->givePermissionTo([
            'workers.view',
            'attendance.view-all',
            'overtime.view',
            'salary.view-all', 'salary.manage', 'salary.process',
            'reports.payroll', 'reports.export',
        ]);
        $this->command->info('✅ Finance role created');

        // 4. MANAGER
        $manager = Role::create(['name' => 'Manager']);
        $manager->givePermissionTo([
            'workers.view',
            'attendance.view-all',
            'leave.view', 'leave.approve',
            'overtime.view', 'overtime.approve',
            'business-trip.view', 'business-trip.approve',
            'reports.hr',
        ]);
        $this->command->info('✅ Manager role created');

        // 5. DIREKTUR
        $direktur = Role::create(['name' => 'Direktur']);
        $direktur->givePermissionTo([
            'workers.view',
            'attendance.view-all',
            'leave.view', 'leave.approve',
            'overtime.view', 'overtime.approve',
            'business-trip.view', 'business-trip.approve',
            'salary.view-all',
            'promotion.approve',
            'reports.hr', 'reports.payroll', 'reports.executive', 'reports.export',
        ]);
        $this->command->info('✅ Direktur role created');

        // 6. USER (Pegawai Biasa)
        $user = Role::create(['name' => 'User']);
        $user->givePermissionTo([
            'attendance.view',
            'documents.view', 'documents.upload',
            'leave.request',
            'overtime.request',
            'business-trip.request',
            'salary.view',
        ]);
        $this->command->info('✅ User role created');

        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   Total Permissions: ' . Permission::count());
        $this->command->info('   Total Roles: ' . Role::count());
    }
}
