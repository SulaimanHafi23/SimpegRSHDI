<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// ========== ROLE & USER CONTROLLERS ==========
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\User\UserController;

// ========== WORKER CONTROLLER ==========
use App\Http\Controllers\Worker\WorkerController;

// ========== ATTENDANCE CONTROLLER ==========
use App\Http\Controllers\Attendance\AttendanceController;

// ========== SCHEDULE CONTROLLER ==========
use App\Http\Controllers\WorkerShift\WorkerShiftController;
use App\Http\Controllers\ShiftOverride\ShiftOverrideController;

// ========== LEAVE CONTROLLER ==========
use App\Http\Controllers\Leave\LeaveRequestController;

// ========== OVERTIME CONTROLLER ==========
use App\Http\Controllers\Overtime\OvertimeRequestController;

// ========== DOCUMENT CONTROLLER ==========
use App\Http\Controllers\WorkerDocument\WorkerDocumentController;

// ========== MASTER DATA CONTROLLERS ==========
use App\Http\Controllers\Master\ShiftController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\GenderController;
use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\Master\DocumentTypeController;
use App\Http\Controllers\Master\DepartmentDocumentTypeController;
use App\Http\Controllers\Master\ReligionController;
use App\Http\Controllers\Master\LeaveTypeController;

// Dashboard Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;

// Profile Controller
use App\Http\Controllers\ProfileController;

// Approval Controllers
use App\Http\Controllers\Approval\LeaveApprovalController;
use App\Http\Controllers\Approval\OvertimeApprovalController;
use App\Http\Controllers\Approval\DocumentApprovalController;

// Report Controller
use App\Http\Controllers\Report\ReportController;

// ========== AUTH ROUTES ==========
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ========== AUTHENTICATED ROUTES ==========
Route::middleware(['auth'])->group(function () {
    
    // ========== DASHBOARDS ==========
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');

    // ========== PROFILE ROUTES ==========
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });

    // ========== APPROVAL ROUTES ========== 
    // TODO: Create Approval Controllers
    // Route::prefix('approvals')->name('approvals.')->group(function () {
    //     // Leave Approvals
    //     Route::prefix('leaves')->name('leaves.')->group(function () {
    //         Route::get('/', [LeaveApprovalController::class, 'index'])->name('index');
    //         Route::get('/{id}', [LeaveApprovalController::class, 'show'])->name('show');
    //         Route::post('/{id}/approve', [LeaveApprovalController::class, 'approve'])->name('approve');
    //         Route::post('/{id}/reject', [LeaveApprovalController::class, 'reject'])->name('reject');
    //     });
    //     // Overtime Approvals
    //     Route::prefix('overtimes')->name('overtimes.')->group(function () {
    //         Route::get('/', [OvertimeApprovalController::class, 'index'])->name('index');
    //         Route::get('/{id}', [OvertimeApprovalController::class, 'show'])->name('show');
    //         Route::post('/{id}/approve', [OvertimeApprovalController::class, 'approve'])->name('approve');
    //         Route::post('/{id}/reject', [OvertimeApprovalController::class, 'reject'])->name('reject');
    //     });
    //     // Document Approvals
    //     Route::prefix('documents')->name('documents.')->group(function () {
    //         Route::get('/', [DocumentApprovalController::class, 'index'])->name('index');
    //         Route::get('/{id}', [DocumentApprovalController::class, 'show'])->name('show');
    //         Route::post('/{id}/verify', [DocumentApprovalController::class, 'verify'])->name('verify');
    //         Route::post('/{id}/reject', [DocumentApprovalController::class, 'reject'])->name('reject');
    //     });
    // });

    // ========== REPORT ROUTES ==========
    // ========== REPORT ROUTES ==========
    // Report pages (some were scaffolded but commented previously). Add specific routes as needed.
    Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
    Route::get('/reports/overtimes', [ReportController::class, 'overtimes'])->name('reports.overtimes');
    Route::get('/reports/worker-documents', [ReportController::class, 'workerDocuments'])->name('reports.worker-documents');

    // ========== ROLE MANAGEMENT ==========
    Route::resource('roles', RoleController::class)->names('admin.roles');

    // ========== USER MANAGEMENT ==========
    Route::resource('users', UserController::class)->names('admin.users');

    // ========== WORKER MANAGEMENT ==========
    Route::prefix('workers')->name('admin.workers.')->group(function () {
        Route::get('/', [WorkerController::class, 'index'])->name('index');
        Route::get('/create', [WorkerController::class, 'create'])->name('create');
        Route::get('/export', [WorkerController::class, 'export'])->name('export');
        Route::post('/import', [WorkerController::class, 'import'])->name('import');
        Route::post('/', [WorkerController::class, 'store'])->name('store');
        Route::get('/{id}', [WorkerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [WorkerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkerController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/resign', [WorkerController::class, 'resign'])->name('resign');
    });

    // ========== ATTENDANCE MANAGEMENT ==========
    Route::prefix('attendance')->name('admin.attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::put('/check-out/{id}', [AttendanceController::class, 'checkOut'])->name('check-out');
        Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy');
        Route::get('/report/daily', [AttendanceController::class, 'dailyReport'])->name('report.daily');
        Route::get('/report/monthly', [AttendanceController::class, 'monthlyReport'])->name('report.monthly');
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
    });

    // ========== WORKER SHIFT MANAGEMENT ==========
    Route::prefix('worker-shifts')->name('admin.worker-shifts.')->group(function () {
        Route::get('/', [WorkerShiftController::class, 'index'])->name('index');
        Route::get('/create', [WorkerShiftController::class, 'create'])->name('create');
        Route::post('/', [WorkerShiftController::class, 'store'])->name('store');
        Route::get('/{id}', [WorkerShiftController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [WorkerShiftController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkerShiftController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkerShiftController::class, 'destroy'])->name('destroy');
        Route::get('/worker/{workerId}', [WorkerShiftController::class, 'workerShifts'])->name('worker-shifts');
    });

    // ========== SHIFT OVERRIDE MANAGEMENT ==========
    Route::prefix('shift-overrides')->name('admin.shift-overrides.')->group(function () {
        Route::get('/', [ShiftOverrideController::class, 'index'])->name('index');
        Route::get('/create', [ShiftOverrideController::class, 'create'])->name('create');
        Route::post('/', [ShiftOverrideController::class, 'store'])->name('store');
        Route::get('/{id}', [ShiftOverrideController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ShiftOverrideController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ShiftOverrideController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShiftOverrideController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-create', [ShiftOverrideController::class, 'bulkCreate'])->name('bulk-create');
    });
    
    // ========== LEAVE REQUEST MANAGEMENT ==========
    Route::prefix('leaves')->name('admin.leave.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [LeaveRequestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LeaveRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LeaveRequestController::class, 'update'])->name('update');
        Route::delete('/{id}', [LeaveRequestController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        Route::post('/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
        Route::get('/worker/{workerId}/balance', [LeaveRequestController::class, 'workerLeaveBalance'])->name('worker-balance');
    });
    
    // ========== OVERTIME MANAGEMENT ==========
    Route::prefix('overtimes')->name('admin.overtime.')->group(function () {
        Route::get('/', [OvertimeRequestController::class, 'index'])->name('index');
        Route::get('/create', [OvertimeRequestController::class, 'create'])->name('create');
        Route::post('/', [OvertimeRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [OvertimeRequestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OvertimeRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OvertimeRequestController::class, 'update'])->name('update');
        Route::delete('/{id}', [OvertimeRequestController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [OvertimeRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [OvertimeRequestController::class, 'reject'])->name('reject');
        Route::post('/bulk-approve', [OvertimeRequestController::class, 'bulkApprove'])->name('bulk-approve');
    });
    
    // ========== WORKER DOCUMENT MANAGEMENT ==========
    Route::prefix('worker-documents')->name('admin.worker-documents.')->group(function () {
        Route::get('/', [WorkerDocumentController::class, 'index'])->name('index');
        Route::get('/create', [WorkerDocumentController::class, 'create'])->name('create');
        Route::post('/', [WorkerDocumentController::class, 'store'])->name('store');
        Route::get('/{id}', [WorkerDocumentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [WorkerDocumentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkerDocumentController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkerDocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/verify', [WorkerDocumentController::class, 'verify'])->name('verify');
        Route::post('/{id}/reject', [WorkerDocumentController::class, 'reject'])->name('reject');
        Route::get('/{id}/download', [WorkerDocumentController::class, 'download'])->name('download');
        Route::get('/worker/{workerId}', [WorkerDocumentController::class, 'workerDocuments'])->name('worker-documents');
        Route::get('/expired', [WorkerDocumentController::class, 'expired'])->name('expired');
        Route::get('/expiring', [WorkerDocumentController::class, 'expiring'])->name('expiring');
        // AJAX: get allowed document types for a given worker
        Route::get('/document-types-for-worker', [WorkerDocumentController::class, 'documentTypesForWorker'])->name('document-types-for-worker');
    });

    // ========== MASTER DATA MANAGEMENT ==========
    Route::prefix('master')->name('admin.master.')->middleware(['auth'])->group(function () {
        
        // Departments (Pengganti Positions)
        Route::resource('departments', DepartmentController::class);
        
        // Shifts
        Route::resource('shifts', ShiftController::class);
        
        // Locations
        Route::resource('locations', LocationController::class);
        
        // Genders
        Route::resource('genders', GenderController::class);
        
        // Religions
        Route::resource('religions', ReligionController::class);
        
        // Document Types
        Route::resource('document-types', DocumentTypeController::class);
    // Department <-> Document Type mappings
    Route::resource('department-document-types', DepartmentDocumentTypeController::class);
        
        // Leave Types
        Route::resource('leave-types', LeaveTypeController::class);
    });
});

// ========== FALLBACK ROUTE ==========
Route::fallback(function () {
    return view('errors.404');
});
