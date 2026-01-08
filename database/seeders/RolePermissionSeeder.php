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

        /**
         * Module-based Permissions
         * Format: module.manage (can fully manage the module)
         */
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Data Master
            'religion.manage',
            'gender.manage',
            'department.manage',
            'location.manage',
            'shift.manage',
            'leave-type.manage',
            'document-type.manage',
            'department-document-type.manage',

            // Management
            'worker.manage',
            'attendance.manage',
            'schedule.manage',
            'worker-document.manage',

            // Approval
            'leave.manage',
            'overtime.manage',
            'shift-swap.manage',
            'business-trip.manage',
            
            // Reports
            'report.view',

            // Settings
            'holiday.manage',
            'role.manage',
            'user.manage',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles and Assign Permissions
        
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // HR - Full access to worker management and master data
        $hr = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $hr->syncPermissions([
            'dashboard.view',
            // Master Data
            'religion.manage',
            'gender.manage',
            'department.manage',
            'location.manage',
            'shift.manage',
            'leave-type.manage',
            'document-type.manage',
            'department-document-type.manage',
            // Management
            'worker.manage',
            'attendance.manage',
            'schedule.manage',
            'worker-document.manage',
            // Approval
            'leave.manage',
            'overtime.manage',
            'business-trip.manage',
            // Reports
            'report.view',
            // Settings
            'holiday.manage',
            'user.manage',
        ]);

        // Manager - Approval and view access
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'dashboard.view',
            // View only for master data
            'department.manage',
            'shift.manage',
            // Management
            'worker.manage',
            'attendance.manage',
            'schedule.manage',
            // Approval
            'leave.manage',
            'overtime.manage',
            'shift-swap.manage',
            'business-trip.manage',
            // Reports
            'report.view',
        ]);

        // Employee - Basic access
        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'dashboard.view',
        ]);

        $this->command->info('✅ Module-based permissions and roles created successfully!');
        $this->command->info('📊 Total Permissions: ' . count($permissions));
        $this->command->info('👥 Roles: Super Admin, HR, Manager, Employee');
    }
}
