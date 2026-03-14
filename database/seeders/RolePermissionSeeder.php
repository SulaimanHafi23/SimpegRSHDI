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
            'dashboard.employee',   // Employee dashboard with personal data only

            // Data Master - Configuration & Setup
            'religion.manage',
            'gender.manage',
            'department.manage',
            'location.manage',
            'shift.manage',
            'leave-type.manage',
            'document-type.manage',
            'department-document-type.manage',
            'holiday.manage',

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

            // Overtime Management
            'overtime.manage',   // Full CRUD + approve/reject all overtime
            'overtime.approve',  // Can approve/reject overtime requests
            'overtime.request',  // Can submit own overtime request
            'overtime.view',     // View own overtime requests only

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
            'calendar.view',       // View calendar with events (holidays, leaves, overtimes)

            // Profile Management
            'profile.view',        // View own profile
            'profile.edit',        // Edit own profile

            // Reports - Based on ReportController
            'report.view',           // View reports (attendance, leave, overtime, worker, document)
            'report.export',         // Export reports to CSV/Excel/PDF

            // Settings & Administration
            'role.manage',            // Manage roles and permissions
            'user.manage',            // Manage user accounts
            'system-settings.manage', // Manage system configurations
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
        $superAdmin->syncPermissions(Permission::all());

        /**
         * HR ROLE
         * - Full worker lifecycle management
         * - Master data configuration
         * - Attendance and document management
         * - Leave and overtime approval
         * - Reports access
         * - User management (except roles)
         */
        $hr = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $hr->syncPermissions([
            // Dashboard
            'dashboard.admin',

            // Master Data - Full Configuration Access
            'religion.manage',
            'gender.manage',
            'department.manage',
            'location.manage',
            'shift.manage',
            'leave-type.manage',
            'document-type.manage',
            'department-document-type.manage',
            'holiday.manage',

            // Worker Management - Full Access
            'worker.manage',
            'attendance.manage',
            'schedule.manage',
            'worker-document.manage',

            // Leave & Overtime - Manage & Approve
            'leave.manage',
            'leave.approve',
            'overtime.manage',
            'overtime.approve',

            // Business Trip - Manage & Approve
            'business-trip.manage',
            'business-trip.approve',

            // Shift Swap - Manage & Approve
            'shift-swap.manage',
            'shift-swap.approve',

            // Notifications - Manage
            'notification.manage',

            // Reports - Full Access
            'report.view',
            'report.export',

            // User Management (not roles)
            'user.manage',
        ]);

        /**
         * MANAGER ROLE
         * - Team oversight and approval authority
         * - View workers and schedules
         * - Approve leave and overtime
         * - View reports
         * - Full management access for leaves, overtimes, business trips, shift swaps
         */
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            // Dashboard
            'dashboard.admin',

            // Worker & Attendance - View Only
            'worker.manage',      // Can view worker lists
            'attendance.manage',  // Can view attendance records
            'schedule.manage',    // Can view and adjust schedules

            // Full Management & Approval Authority
            'leave.manage',       // Full CRUD + approve/reject all leave requests
            'leave.approve',
            'leave.view',
            'overtime.manage',    // Full CRUD + approve/reject all overtime
            'overtime.approve',
            'overtime.view',
            'business-trip.manage',  // Full CRUD + approve/reject all business trips
            'business-trip.approve',
            'business-trip.view',
            'shift-swap.manage',  // Full CRUD + approve/reject all shift swaps
            'shift-swap.approve',
            'shift-swap.view',
            'worker-document.manage', // Manage worker documents

            // Holiday management
            'holiday.manage',

            // Notifications
            'notification.view',

            // Calendar
            'calendar.view',

            // Profile
            'profile.view',
            'profile.edit',

            // Reports - View Only
            'report.view',
            'report.export',
        ]);

        /**
         * EMPLOYEE ROLE
         * - Personal data access only
         * - Submit requests (leave, overtime)
         * - View own records and schedule
         * - Check in/out attendance
         * - View own documents
         */
        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            // Dashboard
            'dashboard.employee',

            // Personal Profile & Documents
            'worker.view',              // View own profile
            'worker-document.view',     // View own documents

            // Attendance
            'attendance.checkin',       // Can check in/out
            'attendance.view',          // View own attendance history

            // Schedule
            'schedule.view',            // View own work schedule

            // Request Submissions
            'leave.request',            // Submit leave requests
            'leave.view',               // View own leave requests
            'overtime.request',         // Submit overtime requests
            'overtime.view',            // View own overtime requests
            'business-trip.request',    // Submit business trip requests
            'business-trip.view',       // View own business trip requests
            'shift-swap.request',       // Submit shift swap requests
            'shift-swap.view',          // View own shift swap requests

            // Notifications & Calendar
            'notification.view',        // View own notifications

            // Calendar & Profile
            'calendar.view',            // View calendar
            'profile.view',             // View own profile
            'profile.edit',             // Edit own profile
        ]);

        $this->command->info('✅ Role-based permissions created successfully!');
        $this->command->info('');
        $this->command->info('📊 Permission Summary:');
        $this->command->info('   Total Permissions: ' . count($permissions));
        $this->command->info('   - Dashboard: 2');
        $this->command->info('   - Master Data: 9');
        $this->command->info('   - Worker & Attendance: 9');
        $this->command->info('   - Leave Management: 4');
        $this->command->info('   - Overtime Management: 4');
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
