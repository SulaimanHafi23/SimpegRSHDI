<?php

use Illuminate\Support\Facades\Route;

// ========== AUTH CONTROLLER ==========
use App\Http\Controllers\Auth\LoginController;

// ========== ROLE & USER CONTROLLERS ==========
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\User\UserController;

// ========== WORKER CONTROLLER ==========
use App\Http\Controllers\Worker\WorkerController;

// ========== ATTENDANCE CONTROLLER ==========
use App\Http\Controllers\Attendance\AbsentController;

// ========== SCHEDULE CONTROLLER ==========
use App\Http\Controllers\Schedule\WorkerShiftScheduleController;

// ========== LEAVE CONTROLLER ==========
use App\Http\Controllers\Leave\LeaveRequestController;

// ========== OVERTIME CONTROLLER ==========
use App\Http\Controllers\Overtime\OvertimeController;

// ========== BUSINESS TRIP CONTROLLERS ==========
use App\Http\Controllers\BusinessTrip\BusinessTripController;
use App\Http\Controllers\BusinessTrip\BusinessTripReportController;

// ========== DOCUMENT CONTROLLER ==========
use App\Http\Controllers\Document\BerkasController;

// ========== MASTER DATA CONTROLLERS ==========
use App\Http\Controllers\Master\ShiftController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\GenderController;
use App\Http\Controllers\Master\PositionController;
use App\Http\Controllers\Master\DocumentTypeController;
use App\Http\Controllers\Master\ReligionController;
use App\Http\Controllers\Master\FileRequirementController;
use App\Http\Controllers\Master\ShiftPatternController;

// ========== AUTH ROUTES ==========
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ========== AUTHENTICATED ROUTES ==========
Route::middleware(['auth'])->group(function () {
    
    // ========== DASHBOARD ==========
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // ========== ROLE MANAGEMENT ==========
    Route::prefix('roles')->name('admin.roles.')->group(function () {
        Route::middleware('permission:view-roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show');
        });
        
        Route::middleware('permission:create-roles')->group(function () {
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:edit-roles')->group(function () {
            Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RoleController::class, 'update'])->name('update');
            Route::get('/{id}/permissions', [RoleController::class, 'editPermissions'])->name('edit-permissions');
            Route::put('/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('update-permissions');
        });
        
        Route::delete('/{id}', [RoleController::class, 'destroy'])
            ->middleware('permission:delete-roles')
            ->name('destroy');
    });

    // ========== USER MANAGEMENT ==========
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::middleware('permission:view-users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
        });
        
        Route::middleware('permission:create-users')->group(function () {
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:edit-users')->group(function () {
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
        });
        
        Route::delete('/{id}', [UserController::class, 'destroy'])
            ->middleware('permission:delete-users')
            ->name('destroy');
        
        Route::middleware('permission:manage-user-roles')->group(function () {
            Route::get('/{id}/roles', [UserController::class, 'editRoles'])->name('edit-roles');
            Route::put('/{id}/roles', [UserController::class, 'updateRoles'])->name('update-roles');
        });
        
        Route::middleware('permission:manage-user-permissions')->group(function () {
            Route::get('/{id}/permissions', [UserController::class, 'editPermissions'])->name('edit-permissions');
            Route::put('/{id}/permissions', [UserController::class, 'updatePermissions'])->name('update-permissions');
        });
        
        Route::middleware('permission:reset-user-password')->group(function () {
            Route::get('/{id}/password', [UserController::class, 'editPassword'])->name('edit-password');
            Route::put('/{id}/password', [UserController::class, 'updatePassword'])->name('update-password');
        });
    });

    // ========== WORKER MANAGEMENT ==========
    Route::prefix('workers')->name('admin.workers.')->group(function () {
        Route::middleware('permission:view-workers')->group(function () {
            Route::get('/', [WorkerController::class, 'index'])->name('index');
            Route::get('/{id}', [WorkerController::class, 'show'])->name('show');
        });
        
        Route::middleware('permission:create-workers')->group(function () {
            Route::get('/create', [WorkerController::class, 'create'])->name('create');
            Route::post('/', [WorkerController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:edit-workers')->group(function () {
            Route::get('/{id}/edit', [WorkerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [WorkerController::class, 'update'])->name('update');
        });
        
        Route::delete('/{id}', [WorkerController::class, 'destroy'])
            ->middleware('permission:delete-workers')
            ->name('destroy');
    });

    // ========== ATTENDANCE MANAGEMENT ==========
    Route::prefix('attendance')->name('admin.attendance.')->group(function () {
        Route::middleware('permission:view-attendance,view-own-attendance')->group(function () {
            Route::get('/', [AbsentController::class, 'index'])->name('index');
            Route::get('/{id}', [AbsentController::class, 'show'])->name('show');
            Route::get('/report/daily', [AbsentController::class, 'dailyReport'])->name('report.daily');
            Route::get('/report/monthly', [AbsentController::class, 'monthlyReport'])->name('report.monthly');
        });
        
        Route::middleware('permission:create-attendance')->group(function () {
            Route::get('/create', [AbsentController::class, 'create'])->name('create');
            Route::post('/', [AbsentController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:edit-attendance')->group(function () {
            Route::get('/{id}/edit', [AbsentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AbsentController::class, 'update'])->name('update');
        });
        
        Route::delete('/{id}', [AbsentController::class, 'destroy'])
            ->middleware('permission:delete-attendance')
            ->name('destroy');
        
        Route::middleware('permission:approve-attendance')->group(function () {
            Route::post('/{id}/approve', [AbsentController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [AbsentController::class, 'reject'])->name('reject');
        });
    });

    // ========== SCHEDULE MANAGEMENT ==========
    Route::prefix('schedules')->name('admin.schedules.')->group(function () {
        Route::middleware('permission:view-schedules,view-own-schedule')->group(function () {
            Route::get('/', [WorkerShiftScheduleController::class, 'index'])->name('index');
            Route::get('/{id}', [WorkerShiftScheduleController::class, 'show'])->name('show');
            Route::get('/calendar', [WorkerShiftScheduleController::class, 'calendar'])->name('calendar');
            Route::get('/worker/{workerId}', [WorkerShiftScheduleController::class, 'workerSchedule'])->name('worker-schedule');
        });
        
        Route::middleware('permission:create-schedules')->group(function () {
            Route::get('/create', [WorkerShiftScheduleController::class, 'create'])->name('create');
            Route::post('/', [WorkerShiftScheduleController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:edit-schedules')->group(function () {
            Route::get('/{id}/edit', [WorkerShiftScheduleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [WorkerShiftScheduleController::class, 'update'])->name('update');
        });
        
        Route::delete('/{id}', [WorkerShiftScheduleController::class, 'destroy'])
            ->middleware('permission:delete-schedules')
            ->name('destroy');
        
        Route::post('/bulk-create', [WorkerShiftScheduleController::class, 'bulkCreate'])
            ->middleware('permission:bulk-create-schedules')
            ->name('bulk-create');
    });
    
    // ========== LEAVE REQUEST MANAGEMENT ==========
    Route::prefix('leaves')->name('leave.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('{id}', [LeaveRequestController::class, 'show'])->name('show');
        Route::get('{id}/edit', [LeaveRequestController::class, 'edit'])->name('edit');
        Route::put('{id}', [LeaveRequestController::class, 'update'])->name('update');
        Route::delete('{id}', [LeaveRequestController::class, 'destroy'])->name('destroy');
        
        // Approval routes
        Route::post('{id}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        Route::get('pending', [LeaveRequestController::class, 'pending'])->name('pending');
        
        // Worker leave quota
        Route::get('worker/{workerId}/quota', [LeaveRequestController::class, 'workerLeaveQuota'])->name('worker-quota');
        
        // Download attachment
        Route::get('{id}/download', [LeaveRequestController::class, 'downloadAttachment'])->name('download-attachment');
    });
    
    // ========== OVERTIME MANAGEMENT ==========
    Route::prefix('overtimes')->name('overtime.')->group(function () {
        Route::get('/', [OvertimeController::class, 'index'])->name('index');
        Route::get('create', [OvertimeController::class, 'create'])->name('create');
        Route::post('/', [OvertimeController::class, 'store'])->name('store');
        Route::get('{id}', [OvertimeController::class, 'show'])->name('show');
        Route::get('{id}/edit', [OvertimeController::class, 'edit'])->name('edit');
        Route::put('{id}', [OvertimeController::class, 'update'])->name('update');
        Route::delete('{id}', [OvertimeController::class, 'destroy'])->name('destroy');
        
        // Approval routes
        Route::post('{id}/approve', [OvertimeController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [OvertimeController::class, 'reject'])->name('reject');
        Route::get('pending', [OvertimeController::class, 'pending'])->name('pending');
        
        // Worker overtime report
        Route::get('worker/{workerId}/report', [OvertimeController::class, 'workerOvertimeReport'])->name('worker-report');
        
        // Download attachment
        Route::get('{id}/download', [OvertimeController::class, 'downloadAttachment'])->name('download-attachment');
    });
    
    // ========== BUSINESS TRIP MANAGEMENT ==========
    Route::prefix('business-trips')->name('business-trip.')->group(function () {
        Route::get('/', [BusinessTripController::class, 'index'])->name('index');
        Route::get('create', [BusinessTripController::class, 'create'])->name('create');
        Route::post('/', [BusinessTripController::class, 'store'])->name('store');
        Route::get('{id}', [BusinessTripController::class, 'show'])->name('show');
        Route::get('{id}/edit', [BusinessTripController::class, 'edit'])->name('edit');
        Route::put('{id}', [BusinessTripController::class, 'update'])->name('update');
        Route::delete('{id}', [BusinessTripController::class, 'destroy'])->name('destroy');
        
        // Approval routes
        Route::post('{id}/approve', [BusinessTripController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [BusinessTripController::class, 'reject'])->name('reject');
        Route::get('pending', [BusinessTripController::class, 'pending'])->name('pending');
        Route::get('active', [BusinessTripController::class, 'active'])->name('active');
        
        // Worker summary
        Route::get('worker/{workerId}/summary', [BusinessTripController::class, 'workerSummary'])->name('worker-summary');
    });
    
    // ========== BUSINESS TRIP REPORT MANAGEMENT ==========
    Route::prefix('business-trip-reports')->name('business-trip-report.')->group(function () {
        Route::get('{id}', [BusinessTripReportController::class, 'show'])->name('show');
        Route::get('create/{businessTripId}', [BusinessTripReportController::class, 'create'])->name('create');
        Route::post('/', [BusinessTripReportController::class, 'store'])->name('store');
        Route::get('{id}/edit', [BusinessTripReportController::class, 'edit'])->name('edit');
        Route::put('{id}', [BusinessTripReportController::class, 'update'])->name('update');
        Route::delete('{id}', [BusinessTripReportController::class, 'destroy'])->name('destroy');
        
        // Review routes
        Route::post('{id}/approve', [BusinessTripReportController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [BusinessTripReportController::class, 'reject'])->name('reject');
        Route::get('pending-review', [BusinessTripReportController::class, 'pendingReview'])->name('pending-review');
        
        // Download attachment
        Route::get('{id}/download', [BusinessTripReportController::class, 'downloadAttachment'])->name('download-attachment');
    });
    
    // ========== DOCUMENT (BERKAS) MANAGEMENT ==========
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [BerkasController::class, 'index'])->name('index');
        Route::get('create', [BerkasController::class, 'create'])->name('create');
        Route::post('/', [BerkasController::class, 'store'])->name('store');
        Route::get('{id}', [BerkasController::class, 'show'])->name('show');
        Route::get('{id}/edit', [BerkasController::class, 'edit'])->name('edit');
        Route::put('{id}', [BerkasController::class, 'update'])->name('update');
        Route::delete('{id}', [BerkasController::class, 'destroy'])->name('destroy');
        
        // Verification routes
        Route::post('{id}/verify', [BerkasController::class, 'verify'])->name('verify');
        Route::post('{id}/reject', [BerkasController::class, 'reject'])->name('reject');
        Route::get('pending', [BerkasController::class, 'pending'])->name('pending');
        
        // Worker documents
        Route::get('worker/{workerId}', [BerkasController::class, 'workerDocuments'])->name('worker-documents');
        Route::get('worker/{workerId}/check-completeness', [BerkasController::class, 'checkCompleteness'])->name('check-completeness');
        
        // Download & Preview
        Route::get('{id}/download', [BerkasController::class, 'download'])->name('download');
        Route::get('{id}/preview', [BerkasController::class, 'preview'])->name('preview');
    });

    // ========== MASTER DATA MANAGEMENT ==========
    Route::prefix('master')->name('admin.master.')->group(function () {
        
        // Shifts
        Route::resource('shifts', ShiftController::class);
        
        // Locations
        Route::resource('locations', LocationController::class);
        
        // Genders
        Route::resource('genders', GenderController::class);
        
        // Positions
        Route::resource('positions', PositionController::class);
        
        // Document Types
        Route::resource('document-types', DocumentTypeController::class);
        
        // Religions
        Route::resource('religions', ReligionController::class);
        
        // File Requirements
        Route::resource('file-requirements', FileRequirementController::class);
        
        // Shift Patterns
        Route::resource('shift-patterns', ShiftPatternController::class);
    });
});

// ========== FALLBACK ROUTE ==========
Route::fallback(function () {
    return view('errors.404');
});
