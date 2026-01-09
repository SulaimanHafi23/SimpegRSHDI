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
         * Comprehensive Permissions System
         * Format: module.action
         */
        $permissions = [
            // ========== DASHBOARD ==========
            'dashboard.view',
            'dashboard.admin',
            'dashboard.hr',
            'dashboard.manager',
            'dashboard.employee',

            // ========== DATA MASTER ==========
            // Religion
            'religion.view',
            'religion.create',
            'religion.edit',
            'religion.delete',
            'religion.manage',

            // Gender
            'gender.view',
            'gender.create',
            'gender.edit',
            'gender.delete',
            'gender.manage',

            // Department
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
            'department.manage',

            // Location
            'location.view',
            'location.create',
            'location.edit',
            'location.delete',
            'location.manage',

            // Shift
            'shift.view',
            'shift.create',
            'shift.edit',
            'shift.delete',
            'shift.manage',

            // Leave Type
            'leave-type.view',
            'leave-type.create',
            'leave-type.edit',
            'leave-type.delete',
            'leave-type.manage',

            // Document Type
            'document-type.view',
            'document-type.create',
            'document-type.edit',
            'document-type.delete',
            'document-type.manage',

            // Department Document Type
            'department-document-type.view',
            'department-document-type.create',
            'department-document-type.edit',
            'department-document-type.delete',
            'department-document-type.manage',

            // ========== MANAJEMEN ==========
            // Workers
            'worker.view',
            'worker.create',
            'worker.edit',
            'worker.delete',
            'worker.resign',
            'worker.export',
            'worker.import',
            'worker.manage',

            // Attendance
            'attendance.view',
            'attendance.view-all',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.checkin',
            'attendance.checkout',
            'attendance.export',
            'attendance.manage',

            // Schedule / Worker Shifts
            'schedule.view',
            'schedule.view-all',
            'schedule.create',
            'schedule.edit',
            'schedule.delete',
            'schedule.override',
            'schedule.manage',

            // Worker Documents
            'worker-document.view',
            'worker-document.view-all',
            'worker-document.upload',
            'worker-document.verify',
            'worker-document.reject',
            'worker-document.delete',
            'worker-document.download',
            'worker-document.manage',

            // Payroll
            'payroll.view',
            'payroll.view-all',
            'payroll.create',
            'payroll.edit',
            'payroll.delete',
            'payroll.process',
            'payroll.export',
            'payroll.manage',

            // ========== PERSETUJUAN ==========
            // Leave Approval
            'leave.view',
            'leave.view-all',
            'leave.request',
            'leave.approve',
            'leave.reject',
            'leave.cancel',
            'leave.export',
            'leave.manage',

            // Overtime Approval
            'overtime.view',
            'overtime.view-all',
            'overtime.request',
            'overtime.approve',
            'overtime.reject',
            'overtime.cancel',
            'overtime.export',
            'overtime.manage',

            // Shift Swap Approval
            'shift-swap.view',
            'shift-swap.view-all',
            'shift-swap.request',
            'shift-swap.approve',
            'shift-swap.reject',
            'shift-swap.execute',
            'shift-swap.cancel',
            'shift-swap.manage',

            // Business Trip Approval
            'business-trip.view',
            'business-trip.view-all',
            'business-trip.request',
            'business-trip.approve',
            'business-trip.reject',
            'business-trip.cancel',
            'business-trip.export',
            'business-trip.manage',

            // ========== LAPORAN ==========
            'report.view',
            'report.attendance',
            'report.leave',
            'report.overtime',
            'report.worker-document',
            'report.payroll',
            'report.export',

            // ========== PENGATURAN ==========
            // Holidays
            'holiday.view',
            'holiday.create',
            'holiday.edit',
            'holiday.delete',
            'holiday.manage',

            // Roles
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.assign-permission',
            'role.manage',

            // Users
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.activate',
            'user.deactivate',
            'user.reset-password',
            'user.manage',

            // Salary Components
            'salary-component.view',
            'salary-component.create',
            'salary-component.edit',
            'salary-component.delete',
            'salary-component.manage',

            // ========== EMPLOYEE SELF-SERVICE ==========
            'profile.view',
            'profile.edit',
            'profile.change-password',
            'notification.view',
            'notification.mark-read',
            'calendar.view',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles and Assign Permissions

        // ========== SUPER ADMIN - ALL PERMISSIONS ==========
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ========== HR - FULL WORKER & MASTER DATA MANAGEMENT ==========
        $hr = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $hr->syncPermissions([
            // Dashboard
            'dashboard.view',
            'dashboard.hr',

            // Master Data - Full access
            'religion.view', 'religion.create', 'religion.edit', 'religion.delete', 'religion.manage',
            'gender.view', 'gender.create', 'gender.edit', 'gender.delete', 'gender.manage',
            'department.view', 'department.create', 'department.edit', 'department.delete', 'department.manage',
            'location.view', 'location.create', 'location.edit', 'location.delete', 'location.manage',
            'shift.view', 'shift.create', 'shift.edit', 'shift.delete', 'shift.manage',
            'leave-type.view', 'leave-type.create', 'leave-type.edit', 'leave-type.delete', 'leave-type.manage',
            'document-type.view', 'document-type.create', 'document-type.edit', 'document-type.delete', 'document-type.manage',
            'department-document-type.view', 'department-document-type.create', 'department-document-type.edit', 'department-document-type.delete', 'department-document-type.manage',

            // Worker Management - Full access
            'worker.view', 'worker.create', 'worker.edit', 'worker.delete', 'worker.resign', 'worker.export', 'worker.import', 'worker.manage',

            // Attendance - Full access
            'attendance.view', 'attendance.view-all', 'attendance.create', 'attendance.edit', 'attendance.delete', 'attendance.export', 'attendance.manage',

            // Schedule - Full access
            'schedule.view', 'schedule.view-all', 'schedule.create', 'schedule.edit', 'schedule.delete', 'schedule.override', 'schedule.manage',

            // Worker Documents - Full access
            'worker-document.view', 'worker-document.view-all', 'worker-document.upload', 'worker-document.verify', 'worker-document.reject', 'worker-document.delete', 'worker-document.download', 'worker-document.manage',

            // Payroll - Full access
            'payroll.view', 'payroll.view-all', 'payroll.create', 'payroll.edit', 'payroll.delete', 'payroll.process', 'payroll.export', 'payroll.manage',

            // Approvals - Full access
            'leave.view', 'leave.view-all', 'leave.approve', 'leave.reject', 'leave.export', 'leave.manage',
            'overtime.view', 'overtime.view-all', 'overtime.approve', 'overtime.reject', 'overtime.export', 'overtime.manage',
            'shift-swap.view', 'shift-swap.view-all', 'shift-swap.approve', 'shift-swap.reject', 'shift-swap.execute', 'shift-swap.manage',
            'business-trip.view', 'business-trip.view-all', 'business-trip.approve', 'business-trip.reject', 'business-trip.export', 'business-trip.manage',

            // Reports - Full access
            'report.view', 'report.attendance', 'report.leave', 'report.overtime', 'report.worker-document', 'report.payroll', 'report.export',

            // Settings
            'holiday.view', 'holiday.create', 'holiday.edit', 'holiday.delete', 'holiday.manage',
            'user.view', 'user.create', 'user.edit', 'user.delete', 'user.activate', 'user.deactivate', 'user.reset-password', 'user.manage',
            'salary-component.view', 'salary-component.create', 'salary-component.edit', 'salary-component.delete', 'salary-component.manage',
        ]);

        // ========== MANAGER - APPROVAL & VIEW ACCESS ==========
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            // Dashboard
            'dashboard.view',
            'dashboard.manager',

            // Master Data - View only
            'department.view',
            'location.view',
            'shift.view',
            'leave-type.view',

            // Worker - View only
            'worker.view',
            'worker.export',

            // Attendance - View all
            'attendance.view', 'attendance.view-all', 'attendance.export',

            // Schedule - View all
            'schedule.view', 'schedule.view-all',

            // Approvals - Full approval access
            'leave.view', 'leave.view-all', 'leave.approve', 'leave.reject', 'leave.manage',
            'overtime.view', 'overtime.view-all', 'overtime.approve', 'overtime.reject', 'overtime.manage',
            'shift-swap.view', 'shift-swap.view-all', 'shift-swap.approve', 'shift-swap.reject', 'shift-swap.execute', 'shift-swap.manage',
            'business-trip.view', 'business-trip.view-all', 'business-trip.approve', 'business-trip.reject', 'business-trip.manage',

            // Reports - View access
            'report.view', 'report.attendance', 'report.leave', 'report.overtime', 'report.export',
        ]);

        // ========== EMPLOYEE - BASIC SELF-SERVICE ==========
        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            // Dashboard
            'dashboard.view',
            'dashboard.employee',

            // Self-service
            'attendance.view', 'attendance.checkin', 'attendance.checkout', 'attendance.export',
            'schedule.view',
            'leave.view', 'leave.request', 'leave.cancel',
            'overtime.view', 'overtime.request', 'overtime.cancel',
            'shift-swap.view', 'shift-swap.request', 'shift-swap.cancel',
            'business-trip.view', 'business-trip.request', 'business-trip.cancel',
            'worker-document.view', 'worker-document.upload', 'worker-document.download',
            'payroll.view',

            // Profile & notifications
            'profile.view', 'profile.edit', 'profile.change-password',
            'notification.view', 'notification.mark-read',
            'calendar.view',
        ]);

        $this->command->info('✅ Comprehensive permissions and roles created successfully!');
        $this->command->info('📊 Total Permissions: ' . count($permissions));
        $this->command->info('👥 Roles: Super Admin (all), HR (full management), Manager (approvals), Employee (self-service)');
    }
}
