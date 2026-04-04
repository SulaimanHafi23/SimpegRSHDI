<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Master Data Repositories
        $this->app->bind(
            \App\Repositories\Contracts\Master\DepartmentRepositoryInterface::class,
            \App\Repositories\Master\DepartmentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Master\ShiftRepositoryInterface::class,
            \App\Repositories\Master\ShiftRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Master\DocumentTypeRepositoryInterface::class,
            \App\Repositories\Master\DocumentTypeRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface::class,
            \App\Repositories\Master\LeaveTypeRepository::class
        );

        // Other Repositories
        $this->app->bind(
            \App\Repositories\Contracts\Worker\WorkerRepositoryInterface::class,
            \App\Repositories\Worker\WorkerRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Attendance\AttendanceRepositoryInterface::class,
            \App\Repositories\Attendance\AttendanceRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\WorkerShift\WorkerShiftRepositoryInterface::class,
            \App\Repositories\WorkerShift\WorkerShiftRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ShiftOverride\ShiftOverrideRepositoryInterface::class,
            \App\Repositories\ShiftOverride\ShiftOverrideRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface::class,
            \App\Repositories\Leave\LeaveRequestRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\WorkerDocument\WorkerDocumentRepositoryInterface::class,
            \App\Repositories\WorkerDocument\WorkerDocumentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Role\RoleRepositoryInterface::class,
            \App\Repositories\Role\RoleRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Permission\PermissionRepositoryInterface::class,
            \App\Repositories\Permission\PermissionRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log lazy loading violations instead of throwing exceptions
        // This helps detect N+1 queries without crashing the app
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            Log::warning("Lazy loading [{$relation}] on model [" . get_class($model) . "]");
        });

        // Permission Blade Directives
        Blade::if('can', function ($permission) {
            return Auth::check() && Auth::user()->can($permission);
        });

        Blade::if('canany', function (...$permissions) {
            return Auth::check() && Auth::user()->hasAnyPermission($permissions);
        });

        Blade::if('role', function ($role) {
            return Auth::check() && Auth::user()->hasRole($role);
        });

        Blade::if('roleany', function (...$roles) {
            return Auth::check() && Auth::user()->hasAnyRole($roles);
        });

        Blade::if('hasallpermissions', function (...$permissions) {
            return Auth::check() && Auth::user()->hasAllPermissions($permissions);
        });

        // Check if user is Super Admin
        Blade::if('superadmin', function () {
            return Auth::check() && Auth::user()->hasRole('Super Admin');
        });

        // Check if user is HR
        Blade::if('hr', function () {
            return Auth::check() && Auth::user()->hasRole('HR');
        });

        // Check if user is Manager
        Blade::if('manager', function () {
            return Auth::check() && Auth::user()->hasRole('Manager');
        });

        // Check if user is Employee
        Blade::if('employee', function () {
            return Auth::check() && Auth::user()->hasRole('Employee');
        });
    }
}
