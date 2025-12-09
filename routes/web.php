<?php

use App\Http\Controllers\Auth\LoginController;
// use App\Http\Controllers\DashboardRedirectController; // TODO: Create this controller
// use App\Http\Controllers\Admin\DashboardController as AdminDashboardController; // TODO: Create
// use App\Http\Controllers\Admin\Master\ReligionController; // TODO: Create
// use App\Http\Controllers\Admin\Master\GenderController; // TODO: Create
// use App\Http\Controllers\Admin\Master\PositionController; // TODO: Create
// use App\Http\Controllers\Admin\Master\LocationController; // TODO: Create
// use App\Http\Controllers\Admin\Master\DocumentTypeController; // TODO: Create
// use App\Http\Controllers\Admin\Settings\FileRequirementController; // TODO: Create
// use App\Http\Controllers\Admin\Settings\ShiftController; // TODO: Create
// use App\Http\Controllers\Admin\Settings\ShiftPatternController; // TODO: Create
// use App\Http\Controllers\Admin\Settings\RoleController; // TODO: Create
// use App\Http\Controllers\Admin\Settings\PermissionController; // TODO: Create
// use App\Http\Controllers\Admin\WorkerController; // TODO: Create
// use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController; // TODO: Create
// use App\Http\Controllers\Admin\Approval\LeaveApprovalController; // TODO: Create
// use App\Http\Controllers\Admin\Approval\OvertimeApprovalController; // TODO: Create
// use App\Http\Controllers\Admin\Approval\BusinessTripApprovalController; // TODO: Create
// use App\Http\Controllers\Admin\Approval\DocumentApprovalController; // TODO: Create
// use App\Http\Controllers\Admin\PayrollController as AdminPayrollController; // TODO: Create
// use App\Http\Controllers\Admin\ReportController; // TODO: Create
// use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController; // TODO: Create
// use App\Http\Controllers\Employee\AttendanceController; // TODO: Create
// use App\Http\Controllers\Employee\ScheduleController; // TODO: Create
// use App\Http\Controllers\Employee\LeaveRequestController; // TODO: Create
// use App\Http\Controllers\Employee\OvertimeRequestController; // TODO: Create
// use App\Http\Controllers\Employee\BusinessTripRequestController; // TODO: Create
// use App\Http\Controllers\Employee\DocumentController; // TODO: Create
// use App\Http\Controllers\Employee\PayrollController; // TODO: Create
// use App\Http\Controllers\Employee\ProfileController; // TODO: Create
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========== HOME / WELCOME PAGE ==========
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// ========== PUBLIC AUTH ROUTES (GUEST ONLY) ==========
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');

    // Forgot Password
    Route::get('forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('reset-password/{token}', [LoginController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [LoginController::class, 'resetPassword'])->name('password.update');
});

// ========== PROTECTED ROUTES (AUTHENTICATED USERS) ==========
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard redirect based on role
    Route::get('dashboard', function () {
        $user = Auth::user();

        // Admin roles redirect to admin dashboard
        if ($user && $user->hasAnyRole(['Super Admin', 'HR', 'Manager'])) {
            return redirect()->route('admin.dashboard');
        }

        // Default to employee dashboard
        return redirect()->route('employee.dashboard');
    })->name('dashboard');

    // ========== ADMIN ROUTES ==========
    Route::middleware(['role:Super Admin|HR|Manager'])->prefix('admin')->name('admin.')->group(function () {

        // Dashboard
        Route::get('dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        // Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard'); // TODO: Uncomment when controller ready

        // ========== MASTER DATA ==========
        Route::prefix('master')->name('master.')->group(function () {
            // TODO: Uncomment when controllers ready
            // Route::resource('religions', ReligionController::class);
            // Route::resource('genders', GenderController::class);
            // Route::resource('positions', PositionController::class);
            // Route::resource('locations', LocationController::class);
            // Route::resource('document-types', DocumentTypeController::class);
        });

        // ========== SETTINGS ==========
        Route::prefix('settings')->name('settings.')->group(function () {
            // TODO: Uncomment when controllers ready
            // Route::resource('file-requirements', FileRequirementController::class);
            // Route::resource('shifts', ShiftController::class);
            // Route::resource('shift-patterns', ShiftPatternController::class);
            // Route::resource('roles', RoleController::class);
            // Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
            // Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
            // Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        });

        // ========== WORKERS MANAGEMENT ==========
        Route::prefix('workers')->name('workers.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('import', [WorkerController::class, 'showImportForm'])->name('import');
            // Route::post('import', [WorkerController::class, 'import'])->name('import.post');
            // Route::get('export', [WorkerController::class, 'export'])->name('export');
            // Route::get('/', [WorkerController::class, 'index'])->name('index');
            // Route::get('create', [WorkerController::class, 'create'])->name('create');
            // Route::post('/', [WorkerController::class, 'store'])->name('store');
            // Route::get('{worker}', [WorkerController::class, 'show'])->name('show');
            // Route::get('{worker}/edit', [WorkerController::class, 'edit'])->name('edit');
            // Route::put('{worker}', [WorkerController::class, 'update'])->name('update');
            // Route::delete('{worker}', [WorkerController::class, 'destroy'])->name('destroy');
            // Route::get('{worker}/schedule', [WorkerController::class, 'schedule'])->name('schedule');
            // Route::post('{worker}/schedule', [WorkerController::class, 'updateSchedule'])->name('schedule.update');
        });

        // ========== ATTENDANCE MONITORING ==========
        Route::prefix('attendance')->name('attendance.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('/', [AdminAttendanceController::class, 'index'])->name('index');
            // Route::get('{attendance}', [AdminAttendanceController::class, 'show'])->name('show');
            // Route::get('report/view', [AdminAttendanceController::class, 'report'])->name('report');
            // Route::get('report/export', [AdminAttendanceController::class, 'export'])->name('export');
        });

        // ========== APPROVALS CENTER ==========
        Route::prefix('approvals')->name('approvals.')->group(function () {

            // Leave Approvals
            Route::prefix('leaves')->name('leaves.')->group(function () {
                // TODO: Uncomment when controller ready
                // Route::get('/', [LeaveApprovalController::class, 'index'])->name('index');
                // Route::get('{leave}', [LeaveApprovalController::class, 'show'])->name('show');
                // Route::post('{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('approve');
                // Route::post('{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('reject');
            });

            // Overtime Approvals
            Route::prefix('overtimes')->name('overtimes.')->group(function () {
                // TODO: Uncomment when controller ready
                // Route::get('/', [OvertimeApprovalController::class, 'index'])->name('index');
                // Route::get('{overtime}', [OvertimeApprovalController::class, 'show'])->name('show');
                // Route::post('{overtime}/approve', [OvertimeApprovalController::class, 'approve'])->name('approve');
                // Route::post('{overtime}/reject', [OvertimeApprovalController::class, 'reject'])->name('reject');
            });

            // Business Trip Approvals
            Route::prefix('business-trips')->name('business-trips.')->group(function () {
                // TODO: Uncomment when controller ready
                // Route::get('/', [BusinessTripApprovalController::class, 'index'])->name('index');
                // Route::get('{businessTrip}', [BusinessTripApprovalController::class, 'show'])->name('show');
                // Route::post('{businessTrip}/approve', [BusinessTripApprovalController::class, 'approve'])->name('approve');
                // Route::post('{businessTrip}/reject', [BusinessTripApprovalController::class, 'reject'])->name('reject');
                // Route::get('{businessTrip}/report', [BusinessTripApprovalController::class, 'reviewReport'])->name('report.review');
            });

            // Document Verification
            Route::prefix('documents')->name('documents.')->group(function () {
                // TODO: Uncomment when controller ready
                // Route::get('/', [DocumentApprovalController::class, 'index'])->name('index');
                // Route::get('{document}', [DocumentApprovalController::class, 'show'])->name('show');
                // Route::post('{document}/verify', [DocumentApprovalController::class, 'verify'])->name('verify');
                // Route::post('{document}/reject', [DocumentApprovalController::class, 'reject'])->name('reject');
            });
        });

        // ========== PAYROLL MANAGEMENT ==========
        Route::prefix('payroll')->name('payroll.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('generate', [AdminPayrollController::class, 'showGenerateForm'])->name('generate');
            // Route::post('generate', [AdminPayrollController::class, 'generate'])->name('generate.post');
            // Route::get('export', [AdminPayrollController::class, 'export'])->name('export');
            // Route::get('/', [AdminPayrollController::class, 'index'])->name('index');
            // Route::get('create', [AdminPayrollController::class, 'create'])->name('create');
            // Route::post('/', [AdminPayrollController::class, 'store'])->name('store');
            // Route::get('{payroll}', [AdminPayrollController::class, 'show'])->name('show');
            // Route::get('{payroll}/edit', [AdminPayrollController::class, 'edit'])->name('edit');
            // Route::put('{payroll}', [AdminPayrollController::class, 'update'])->name('update');
            // Route::delete('{payroll}', [AdminPayrollController::class, 'destroy'])->name('destroy');
        });

        // ========== REPORTS ==========
        Route::prefix('reports')->name('reports.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('attendance', [ReportController::class, 'attendance'])->name('attendance');
            // Route::get('leaves', [ReportController::class, 'leaves'])->name('leaves');
            // Route::get('overtimes', [ReportController::class, 'overtimes'])->name('overtimes');
            // Route::get('business-trips', [ReportController::class, 'businessTrips'])->name('business-trips');
            // Route::get('payroll', [ReportController::class, 'payroll'])->name('payroll');
            // Route::get('summary', [ReportController::class, 'summary'])->name('summary');
        });
    }); // End Admin Routes

    // ========== EMPLOYEE ROUTES ==========
    Route::middleware(['role:Employee|Super Admin|HR|Manager'])->prefix('employee')->name('employee.')->group(function () {

        // Dashboard
        Route::get('dashboard', function () {
            return view('employee.dashboard');
        })->name('dashboard');
        // Route::get('dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard'); // TODO: Uncomment when controller ready

        // ========== ATTENDANCE ==========
        Route::prefix('attendance')->name('attendance.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('/', [AttendanceController::class, 'index'])->name('index');
            // Route::get('create', [AttendanceController::class, 'create'])->name('create');
            // Route::post('/', [AttendanceController::class, 'store'])->name('store');
            // Route::get('{attendance}', [AttendanceController::class, 'show'])->name('show');
        });

        // ========== SCHEDULE ==========
        Route::prefix('schedule')->name('schedule.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('/', [ScheduleController::class, 'index'])->name('index');
            // Route::get('calendar', [ScheduleController::class, 'calendar'])->name('calendar');
        });

        // ========== REQUESTS ==========
        Route::prefix('requests')->name('requests.')->group(function () {

            // Leave Requests
            // TODO: Uncomment when controller ready
            // Route::resource('leaves', LeaveRequestController::class)->except(['destroy']);
            // Route::delete('leaves/{leave}', [LeaveRequestController::class, 'destroy'])->name('leaves.destroy');

            // Overtime Requests
            // TODO: Uncomment when controller ready
            // Route::resource('overtimes', OvertimeRequestController::class)->except(['destroy']);
            // Route::delete('overtimes/{overtime}', [OvertimeRequestController::class, 'destroy'])->name('overtimes.destroy');

            // Business Trip Requests
            Route::prefix('business-trips')->name('business-trips.')->group(function () {
                // TODO: Uncomment when controller ready
                // Route::get('/', [BusinessTripRequestController::class, 'index'])->name('index');
                // Route::get('create', [BusinessTripRequestController::class, 'create'])->name('create');
                // Route::post('/', [BusinessTripRequestController::class, 'store'])->name('store');
                // Route::get('{businessTrip}', [BusinessTripRequestController::class, 'show'])->name('show');
                // Route::get('{businessTrip}/edit', [BusinessTripRequestController::class, 'edit'])->name('edit');
                // Route::put('{businessTrip}', [BusinessTripRequestController::class, 'update'])->name('update');
                // Route::delete('{businessTrip}', [BusinessTripRequestController::class, 'destroy'])->name('destroy');
                // Route::get('{businessTrip}/report/create', [BusinessTripRequestController::class, 'createReport'])->name('report.create');
                // Route::post('{businessTrip}/report', [BusinessTripRequestController::class, 'storeReport'])->name('report.store');
                // Route::get('{businessTrip}/report/edit', [BusinessTripRequestController::class, 'editReport'])->name('report.edit');
                // Route::put('{businessTrip}/report', [BusinessTripRequestController::class, 'updateReport'])->name('report.update');
            });
        });

        // ========== DOCUMENTS ==========
        Route::prefix('documents')->name('documents.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('/', [DocumentController::class, 'index'])->name('index');
            // Route::get('upload', [DocumentController::class, 'showUploadForm'])->name('upload');
            // Route::post('/', [DocumentController::class, 'store'])->name('store');
            // Route::get('{document}', [DocumentController::class, 'show'])->name('show');
            // Route::delete('{document}', [DocumentController::class, 'destroy'])->name('destroy');
        });

        // ========== PAYROLL ==========
        Route::prefix('payroll')->name('payroll.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('/', [PayrollController::class, 'index'])->name('index');
            // Route::get('{payroll}', [PayrollController::class, 'show'])->name('show');
            // Route::get('{payroll}/download', [PayrollController::class, 'download'])->name('download');
        });

        // ========== PROFILE ==========
        Route::prefix('profile')->name('profile.')->group(function () {
            // TODO: Uncomment when controller ready
            // Route::get('/', [ProfileController::class, 'index'])->name('index');
            // Route::get('edit', [ProfileController::class, 'edit'])->name('edit');
            // Route::put('/', [ProfileController::class, 'update'])->name('update');
            // Route::get('change-password', [ProfileController::class, 'showChangePasswordForm'])->name('change-password');
            // Route::put('change-password', [ProfileController::class, 'changePassword'])->name('change-password.update');
        });
    }); // End Employee Routes
}); // End Auth Middleware
