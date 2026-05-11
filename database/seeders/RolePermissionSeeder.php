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
         *
         * Permission Structure:
         * - dashboard.admin: Access to admin dashboard with global statistics
         * - dashboard.employee: Access to employee dashboard with personal data
         * - *.manage: Full CRUD access to module
         * - *.view: Read-only access
         * - *.approve: Approval access for requests
         */
        $permissions = [
            // Dashboard Access
            'dashboard.admin',      // Admin dashboard with global stats, approvals, reports
            'dashboard.hr',         // HR-specific dashboard
            'dashboard.manager',    // Manager-specific dashboard
            'dashboard.employee',   // Employee dashboard with personal data only

            // Data Master - Configuration & Setup
            'department.manage',
            'shift.manage',
            'leave-type.manage',
            'document-type.manage',
            'department-document-type.manage',
            'holiday.manage',
            'master.manage',        // Generic master data management

            // Worker & Attendance Management
            'worker.manage',           // Full CRUD on all workers
            'worker.view',             // View own worker profile only
            'attendance.manage',       // Manage all attendance records
            'attendance.view',         // View own attendance only
            'attendance.checkin',      // Can check in/out
            'schedule.manage',         // Manage work schedules
            'schedule.view',           // View own schedule only
            'worker-document.manage',  // Manage all worker documents
            'worker-document.view',    // View own documents only

            // Leave Management
            'leave.manage',      // Full CRUD + approve/reject all leave requests
            'leave.approve',     // Can approve/reject leave requests
            'leave.request',     // Can submit own leave request
            'leave.view',        // View own leave requests only

            // Business Trip Management
            'business-trip.manage',   // Full CRUD + approve/reject all business trips
            'business-trip.approve',  // Can approve/reject business trip requests
            'business-trip.request',  // Can submit own business trip request
            'business-trip.view',     // View own business trip requests only

            // Shift Swap Management
            'shift-swap.manage',   // Full CRUD + approve/reject all shift swap
            'shift-swap.approve',  // Can approve/reject shift swap requests
            'shift-swap.request',  // Can submit own shift swap request
            'shift-swap.view',     // View own shift swap requests only

            // Notification Management
            'notification.manage', // Manage all notifications
            'notification.view',   // View own notifications only

            // Calendar Management
            'calendar.view',       // View calendar with events (holidays, leaves)

            // Profile Management
            'profile.view',        // View own profile
            'profile.edit',        // Edit own profile

            // Reports - Based on ReportController
            'report.view',           // View reports (attendance, leave, worker, document)
            'report.export',         // Export reports to CSV/Excel/PDF

            // Settings & Administration
            'role.manage',            // Manage roles and permissions
            'user.manage',            // Manage user accounts
            'system-settings.manage', // Manage system configurations
            'audit.view',             // View audit logs
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles and Assign Permissions

        /**
         * SUPER ADMIN ROLE
         * - Full system access
         * - All permissions including admin dashboard
         * - Can manage roles, users, and all configurations
         */
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions([
            'dashboard.admin', 'dashboard.hr', 'dashboard.manager', 'dashboard.employee',
            'department.manage', 'shift.manage', 'leave-type.manage', 'document-type.manage',
            'department-document-type.manage', 'holiday.manage', 'master.manage',
            'worker.manage', 'worker.view', 'attendance.manage', 'attendance.view', 'attendance.checkin',
            'schedule.manage', 'schedule.view', 'worker-document.manage', 'worker-document.view',
            'leave.manage', 'leave.approve', 'leave.request', 'leave.view',
            'business-trip.manage', 'business-trip.approve', 'business-trip.request', 'business-trip.view',
            'shift-swap.manage', 'shift-swap.approve', 'shift-swap.request', 'shift-swap.view',
            'notification.manage', 'notification.view', 'calendar.view', 'profile.view', 'profile.edit',
            'report.view', 'report.export', 'role.manage', 'user.manage', 'system-settings.manage', 'audit.view'
        ]);

        /**
         * HR ROLE
         */
        $hr = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $hr->syncPermissions([
            'dashboard.hr', 'dashboard.employee', 'department.manage', 'shift.manage',
            'document-type.manage', 'department-document-type.manage',
            'worker.manage', 'worker.view', 'attendance.manage', 'attendance.view', 'attendance.checkin',
            'schedule.manage', 'schedule.view', 'worker-document.manage', 'worker-document.view',
            'leave.approve', 'leave.request', 'leave.view',
            'business-trip.approve', 'business-trip.request', 'business-trip.view',
            'shift-swap.manage', 'shift-swap.approve', 'shift-swap.request', 'shift-swap.view',
            'notification.view', 'calendar.view', 'profile.view',
            'report.view', 'report.export', 'user.manage', 'audit.view'
        ]);

        /**
         * MANAGER ROLE
         */
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'dashboard.manager', 'dashboard.employee', 'leave-type.manage',
            'worker.manage', 'worker.view', 'attendance.manage', 'attendance.view', 'attendance.checkin',
            'schedule.manage', 'schedule.view', 'worker-document.manage', 'worker-document.view',
            'leave.approve', 'leave.request', 'leave.view',
            'business-trip.approve', 'business-trip.request', 'business-trip.view',
            'shift-swap.manage', 'shift-swap.approve', 'shift-swap.request', 'shift-swap.view',
            'notification.view', 'calendar.view', 'profile.view',
            'report.view', 'report.export', 'audit.view'
        ]);

        /**
         * EMPLOYEE ROLE
         */
        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'dashboard.employee', 'worker.view', 'attendance.view', 'attendance.checkin',
            'schedule.view', 'worker-document.view', 'leave.request', 'leave.view',
            'business-trip.request', 'business-trip.view', 'shift-swap.request', 'shift-swap.view',
            'notification.view', 'calendar.view', 'profile.view', 'profile.edit'
        ]);

        $this->command->info('✅ Role-based permissions created successfully!');
        $this->command->info('');
        $this->command->info('📊 Permission Summary:');
        $this->command->info('   Total Permissions: ' . count($permissions));
        $this->command->info('   - Dashboard: 2');
        $this->command->info('   - Master Data: 9');
        $this->command->info('   - Worker & Attendance: 9');
        $this->command->info('   - Leave Management: 4');
        $this->command->info('   - Reports: 2');
        $this->command->info('   - Settings: 2');
        $this->command->info('');
        $this->command->info('👥 Roles Created:');
        $this->command->info('   - Super Admin: ' . $superAdmin->permissions->count() . ' permissions (ALL)');
        $this->command->info('   - HR: ' . $hr->permissions->count() . ' permissions');
        $this->command->info('   - Manager: ' . $manager->permissions->count() . ' permissions');
        $this->command->info('   - Employee: ' . $employee->permissions->count() . ' permissions');
    }
}
