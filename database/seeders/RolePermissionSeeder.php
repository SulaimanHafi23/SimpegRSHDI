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
        
        // Role & Permission Management
        $rolePermissions = [
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'assign-permissions-to-roles',
        ];

        // Permission Management
        $permissionManagement = [
            'view-permissions',
            'assign-permissions-to-users',
        ];

        // Master Data Permissions
        $masterPermissions = [
            'view-master-data',
            'create-master-data',
            'edit-master-data',
            'delete-master-data',
        ];

        // Worker Management Permissions
        $workerPermissions = [
            'view-workers',
            'create-workers',
            'edit-workers',
            'delete-workers',
            'view-worker-profile',
            'view-worker-documents',
            'view-worker-attendance',
            'view-worker-leaves',
        ];

        // User Management Permissions
        $userPermissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'toggle-user-status',
        ];

        // Attendance Permissions
        $attendancePermissions = [
            'view-attendance',
            'create-attendance',
            'edit-attendance',
            'delete-attendance',
            'view-own-attendance',
            'view-attendance-reports',
        ];

        // Schedule Permissions
        $schedulePermissions = [
            'view-schedules',
            'create-schedules',
            'edit-schedules',
            'delete-schedules',
            'view-own-schedule',
            'bulk-create-schedules',
        ];

        // Leave Request Permissions
        $leavePermissions = [
            'view-leave-requests',
            'create-leave-requests',
            'edit-leave-requests',
            'delete-leave-requests',
            'approve-leave-requests',
            'reject-leave-requests',
            'view-own-leave-requests',
            'view-pending-leaves',
        ];

        // Overtime Permissions
        $overtimePermissions = [
            'view-overtimes',
            'create-overtimes',
            'edit-overtimes',
            'delete-overtimes',
            'approve-overtimes',
            'reject-overtimes',
            'view-own-overtimes',
            'view-pending-overtimes',
        ];

        // Business Trip Permissions
        $businessTripPermissions = [
            'view-business-trips',
            'create-business-trips',
            'edit-business-trips',
            'delete-business-trips',
            'approve-business-trips',
            'reject-business-trips',
            'view-own-business-trips',
            'view-pending-business-trips',
            'view-active-business-trips',
        ];

        // Business Trip Report Permissions
        $businessTripReportPermissions = [
            'view-business-trip-reports',
            'create-business-trip-reports',
            'edit-business-trip-reports',
            'delete-business-trip-reports',
            'approve-business-trip-reports',
            'reject-business-trip-reports',
        ];

        // Document Permissions
        $documentPermissions = [
            'view-documents',
            'upload-documents',
            'edit-documents',
            'delete-documents',
            'verify-documents',
            'reject-documents',
            'view-own-documents',
            'view-pending-documents',
            'download-documents',
        ];

        // Salary Permissions
        $salaryPermissions = [
            'view-salaries',
            'create-salaries',
            'edit-salaries',
            'delete-salaries',
            'view-own-salary',
            'export-salary-reports',
        ];

        // Report Permissions
        $reportPermissions = [
            'view-reports',
            'export-reports',
        ];

        // Dashboard Permissions
        $dashboardPermissions = [
            'view-dashboard',
            'view-admin-dashboard',
            'view-hr-dashboard',
            'view-manager-dashboard',
            'view-employee-dashboard',
        ];

        // Settings Permissions
        $settingsPermissions = [
            'view-settings',
            'edit-settings',
            'view-shifts',
            'create-shifts',
            'edit-shifts',
            'delete-shifts',
            'view-shift-patterns',
            'create-shift-patterns',
            'edit-shift-patterns',
            'delete-shift-patterns',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $rolePermissions,
            $permissionManagement,
            $masterPermissions,
            $workerPermissions,
            $userPermissions,
            $attendancePermissions,
            $schedulePermissions,
            $leavePermissions,
            $overtimePermissions,
            $businessTripPermissions,
            $businessTripReportPermissions,
            $documentPermissions,
            $salaryPermissions,
            $reportPermissions,
            $dashboardPermissions,
            $settingsPermissions
        );

        // Create permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // ========== CREATE ROLES ==========

        // 1. Super Admin - Full Access
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. HR - Human Resource Management
        $hr = Role::firstOrCreate(['name' => 'HR']);
        $hr->givePermissionTo([
            // Role & Permission
            'view-roles',
            'view-permissions',
            
            // Master Data
            'view-master-data',
            'create-master-data',
            'edit-master-data',
            
            // Workers
            'view-workers',
            'create-workers',
            'edit-workers',
            'view-worker-profile',
            'view-worker-documents',
            'view-worker-attendance',
            'view-worker-leaves',
            
            // Users
            'view-users',
            'create-users',
            'edit-users',
            'assign-roles',
            'toggle-user-status',
            
            // Attendance
            'view-attendance',
            'create-attendance',
            'edit-attendance',
            'view-attendance-reports',
            
            // Schedules
            'view-schedules',
            'create-schedules',
            'edit-schedules',
            'bulk-create-schedules',
            
            // Leave Requests
            'view-leave-requests',
            'approve-leave-requests',
            'reject-leave-requests',
            'view-pending-leaves',
            
            // Overtimes
            'view-overtimes',
            'approve-overtimes',
            'reject-overtimes',
            'view-pending-overtimes',
            
            // Business Trips
            'view-business-trips',
            'approve-business-trips',
            'reject-business-trips',
            'view-pending-business-trips',
            'view-active-business-trips',
            
            // Business Trip Reports
            'view-business-trip-reports',
            'approve-business-trip-reports',
            'reject-business-trip-reports',
            
            // Documents
            'view-documents',
            'verify-documents',
            'reject-documents',
            'view-pending-documents',
            'download-documents',
            
            // Salaries
            'view-salaries',
            'create-salaries',
            'edit-salaries',
            'export-salary-reports',
            
            // Settings
            'view-settings',
            'view-shifts',
            'create-shifts',
            'edit-shifts',
            'view-shift-patterns',
            'create-shift-patterns',
            'edit-shift-patterns',
            
            // Reports & Dashboard
            'view-reports',
            'export-reports',
            'view-dashboard',
            'view-hr-dashboard',
        ]);

        // 3. Manager - Department Management
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            // Workers
            'view-workers',
            'view-worker-profile',
            'view-worker-documents',
            'view-worker-attendance',
            'view-worker-leaves',
            
            // Attendance
            'view-attendance',
            'view-attendance-reports',
            
            // Schedules
            'view-schedules',
            
            // Leave Requests
            'view-leave-requests',
            'approve-leave-requests',
            'reject-leave-requests',
            'view-pending-leaves',
            
            // Overtimes
            'view-overtimes',
            'approve-overtimes',
            'reject-overtimes',
            'view-pending-overtimes',
            
            // Business Trips
            'view-business-trips',
            'approve-business-trips',
            'reject-business-trips',
            'view-pending-business-trips',
            'view-active-business-trips',
            
            // Business Trip Reports
            'view-business-trip-reports',
            'approve-business-trip-reports',
            'reject-business-trip-reports',
            
            // Documents
            'view-documents',
            'download-documents',
            
            // Reports & Dashboard
            'view-reports',
            'export-reports',
            'view-dashboard',
            'view-manager-dashboard',
        ]);

        // 4. Employee - Self Service
        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->givePermissionTo([
            // Own Data Only
            'view-own-attendance',
            'view-own-schedule',
            
            // Leave Requests
            'view-own-leave-requests',
            'create-leave-requests',
            'edit-leave-requests',
            
            // Overtimes
            'view-own-overtimes',
            'create-overtimes',
            'edit-overtimes',
            
            // Business Trips
            'view-own-business-trips',
            'create-business-trips',
            'edit-business-trips',
            
            // Business Trip Reports
            'create-business-trip-reports',
            'edit-business-trip-reports',
            
            // Documents
            'view-own-documents',
            'upload-documents',
            'edit-documents',
            'download-documents',
            
            // Salary
            'view-own-salary',
            
            // Dashboard
            'view-dashboard',
            'view-employee-dashboard',
        ]);

        $this->command->info('✅ Roles and Permissions created successfully!');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                ['Super Admin', $superAdmin->permissions->count()],
                ['HR', $hr->permissions->count()],
                ['Manager', $manager->permissions->count()],
                ['Employee', $employee->permissions->count()],
            ]
        );
    }
}
