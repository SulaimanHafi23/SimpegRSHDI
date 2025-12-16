<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // User
        $this->app->bind(
            \App\Repositories\Contracts\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserRepository::class
        );

        // Worker
        $this->app->bind(
            \App\Repositories\Contracts\Worker\WorkerRepositoryInterface::class,
            \App\Repositories\Worker\WorkerRepository::class
        );

        // Attendance
        $this->app->bind(
            \App\Repositories\Contracts\Attendance\AttendanceRepositoryInterface::class,
            \App\Repositories\Attendance\AttendanceRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\Contracts\Attendance\AttendancePhotoRepositoryInterface::class,
            \App\Repositories\Attendance\AttendancePhotoRepository::class
        );

        // Worker Shift
        $this->app->bind(
            \App\Repositories\Contracts\WorkerShift\WorkerShiftRepositoryInterface::class,
            \App\Repositories\WorkerShift\WorkerShiftRepository::class
        );

        // Shift Override
        $this->app->bind(
            \App\Repositories\Contracts\ShiftOverride\ShiftOverrideRepositoryInterface::class,
            \App\Repositories\ShiftOverride\ShiftOverrideRepository::class
        );

        // Leave
        $this->app->bind(
            \App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface::class,
            \App\Repositories\Leave\LeaveRequestRepository::class
        );

        // Overtime
        $this->app->bind(
            \App\Repositories\Contracts\Overtime\OvertimeRequestRepositoryInterface::class,
            \App\Repositories\Overtime\OvertimeRequestRepository::class
        );

        // Worker Document
        $this->app->bind(
            \App\Repositories\Contracts\WorkerDocument\WorkerDocumentRepositoryInterface::class,
            \App\Repositories\WorkerDocument\WorkerDocumentRepository::class
        );

        // Role & Permission
        $this->app->bind(
            \App\Repositories\Contracts\Role\RoleRepositoryInterface::class,
            \App\Repositories\Role\RoleRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Permission\PermissionRepositoryInterface::class,
            \App\Repositories\Permission\PermissionRepository::class
        );

        // Master - Religion
        $this->app->bind(
            \App\Repositories\Contracts\Master\ReligionRepositoryInterface::class,
            \App\Repositories\Master\ReligionRepository::class
        );

        // Master - Gender
        $this->app->bind(
            \App\Repositories\Contracts\Master\GenderRepositoryInterface::class,
            \App\Repositories\Master\GenderRepository::class
        );

        // Master - Department
        $this->app->bind(
            \App\Repositories\Contracts\Master\DepartmentRepositoryInterface::class,
            \App\Repositories\Master\DepartmentRepository::class
        );

        // Master - Location
        $this->app->bind(
            \App\Repositories\Contracts\Master\LocationRepositoryInterface::class,
            \App\Repositories\Master\LocationRepository::class
        );

        // Master - Document Type
        $this->app->bind(
            \App\Repositories\Contracts\Master\DocumentTypeRepositoryInterface::class,
            \App\Repositories\Master\DocumentTypeRepository::class
        );

        // Master - Shift
        $this->app->bind(
            \App\Repositories\Contracts\Master\ShiftRepositoryInterface::class,
            \App\Repositories\Master\ShiftRepository::class
        );

        // Master - Leave Type
        $this->app->bind(
            \App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface::class,
            \App\Repositories\Master\LeaveTypeRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}