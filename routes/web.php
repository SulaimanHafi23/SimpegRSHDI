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
use App\Http\Controllers\Admin\HolidayController;

// Dashboard Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\HR\HRDashboardController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\ShiftController as EmployeeShiftController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\OvertimeController as EmployeeOvertimeController;
use App\Http\Controllers\Employee\BusinessTripController as EmployeeBusinessTripController;
use App\Http\Controllers\Employee\DocumentController as EmployeeDocumentController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;

// Approval controllers
use App\Http\Controllers\Approval\BusinessTripApprovalController;

// Profile Controller
use App\Http\Controllers\ProfileController;

// API Controllers
use App\Http\Controllers\Api\WorkerShiftApiController;

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

// ========== WORLD TIME API ==========
Route::get('/api/world-time', function () {
    try {
        // Try World Time API first
        $response = \Illuminate\Support\Facades\Http::timeout(3)->get('https://worldtimeapi.org/api/timezone/Asia/Makassar');

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'success' => true,
                'datetime' => $data['datetime'],
                'timezone' => $data['timezone'],
                'utc_offset' => $data['utc_offset'],
                'source' => 'worldtimeapi'
            ]);
        }
    } catch (\Exception $e) {
        \Log::warning('World Time API failed, using server time', ['error' => $e->getMessage()]);
    }

    // Fallback: Use server time with correct timezone
    // Make sure server timezone is set correctly in config/app.php
    $now = \Carbon\Carbon::now('Asia/Makassar');

    return response()->json([
        'success' => true,
        'datetime' => $now->toIso8601String(),
        'timezone' => 'Asia/Makassar',
        'utc_offset' => '+08:00',
        'fallback' => true,
        'source' => 'server',
        'server_time' => $now->format('Y-m-d H:i:s')
    ]);
})->name('api.world-time');

// ========== AUTHENTICATED ROUTES ==========
Route::middleware(['auth', 'redirect_role'])->group(function () {

    // ========== DASHBOARDS ==========
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware('role_or_permission:Super Admin|HR|Manager|dashboard.admin')->name('admin.dashboard');

    // HR Dashboard
    Route::get('/hr/dashboard', [HRDashboardController::class, 'index'])->middleware('role:HR')->name('hr.dashboard');

    // Manager Dashboard
    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->middleware('role:Manager')->name('manager.dashboard');

    // ========== EMPLOYEE ROUTES ==========
    Route::prefix('employee')->name('employee.')->middleware('role_or_permission:Employee|Manager|dashboard.employee')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        // Attendance for employees
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [EmployeeAttendanceController::class, 'index'])->name('index');
            Route::get('/export-pdf', [EmployeeAttendanceController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/check-in', [EmployeeAttendanceController::class, 'checkInForm'])->name('check-in-form');
            Route::post('/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('check-in');
            Route::get('/check-out', [EmployeeAttendanceController::class, 'checkOutForm'])->name('check-out-form');
            Route::post('/check-out/{id}', [EmployeeAttendanceController::class, 'checkOut'])->name('check-out');
            Route::get('/{id}', [EmployeeAttendanceController::class, 'show'])->name('show');
        });

        // Shift swaps for employees
        Route::prefix('shift-swaps')->name('shift-swaps.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'store'])->name('store');
            Route::post('/{id}/accept', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'accept'])->name('accept');
            Route::post('/{id}/reject', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'reject'])->name('reject');
            Route::post('/{id}/cancel', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'cancel'])->name('cancel');
            Route::get('/{id}/accept-open', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'showAcceptOpen'])->name('accept-open');
            Route::post('/{id}/accept-open', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'acceptOpen'])->name('accept-open.store');
        });

        // Shifts for employees
        Route::prefix('shifts')->name('shifts.')->group(function () {
            Route::get('/', [EmployeeShiftController::class, 'index'])->name('index');
            Route::get('/show', [EmployeeShiftController::class, 'show'])->name('show');
        });

        // Leave requests for employees
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [EmployeeLeaveController::class, 'index'])->name('index');
            Route::get('/export-pdf', [EmployeeLeaveController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/create', [EmployeeLeaveController::class, 'create'])->name('create');
            Route::post('/', [EmployeeLeaveController::class, 'store'])->name('store');
            Route::get('/{id}', [EmployeeLeaveController::class, 'show'])->name('show');
            Route::delete('/{id}', [EmployeeLeaveController::class, 'cancel'])->name('cancel');
        });

        // Overtime requests for employees
        Route::prefix('overtimes')->name('overtimes.')->group(function () {
            Route::get('/', [EmployeeOvertimeController::class, 'index'])->name('index');
            Route::get('/export-pdf', [EmployeeOvertimeController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/create', [EmployeeOvertimeController::class, 'create'])->name('create');
            Route::post('/', [EmployeeOvertimeController::class, 'store'])->name('store');
            Route::get('/{id}', [EmployeeOvertimeController::class, 'show'])->name('show');
            Route::delete('/{id}', [EmployeeOvertimeController::class, 'cancel'])->name('cancel');
        });

        // Business Trip requests for employees
        Route::prefix('business-trips')->name('business-trips.')->group(function () {
            Route::get('/', [EmployeeBusinessTripController::class, 'index'])->name('index');
            Route::get('/create', [EmployeeBusinessTripController::class, 'create'])->name('create');
            Route::post('/', [EmployeeBusinessTripController::class, 'store'])->name('store');
            Route::get('/{id}', [EmployeeBusinessTripController::class, 'show'])->name('show');
            Route::delete('/{id}', [EmployeeBusinessTripController::class, 'cancel'])->name('cancel');
        });

        // Documents for employees
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [EmployeeDocumentController::class, 'index'])->name('index');
            Route::get('/create', [EmployeeDocumentController::class, 'create'])->name('create');
            Route::post('/', [EmployeeDocumentController::class, 'store'])->name('store');
            Route::get('/{id}', [EmployeeDocumentController::class, 'show'])->name('show');
            Route::get('/{id}/download', [EmployeeDocumentController::class, 'download'])->name('download');
            Route::delete('/{id}', [EmployeeDocumentController::class, 'destroy'])->name('destroy');
        });

        // Profile for employees
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [EmployeeProfileController::class, 'show'])->name('show');
            Route::get('/edit', [EmployeeProfileController::class, 'edit'])->name('edit');
            Route::put('/', [EmployeeProfileController::class, 'update'])->name('update');
            Route::put('/password', [EmployeeProfileController::class, 'updatePassword'])->name('update-password');
        });

        // Notifications for employees
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Employee\NotificationController::class, 'index'])->name('index');
            Route::get('/unread', [\App\Http\Controllers\Employee\NotificationController::class, 'getUnread'])->name('unread');
            Route::get('/unread-count', [\App\Http\Controllers\Employee\NotificationController::class, 'getUnreadCount'])->name('unread-count');
            Route::post('/{id}/mark-read', [\App\Http\Controllers\Employee\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [\App\Http\Controllers\Employee\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [\App\Http\Controllers\Employee\NotificationController::class, 'destroy'])->name('destroy');
        });

        // Calendar for employees
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Employee\CalendarController::class, 'index'])->name('index');
            Route::get('/events', [\App\Http\Controllers\Employee\CalendarController::class, 'events'])->name('events');
        });
    });

    // ========== MANAGER ROUTES ==========
    Route::prefix('manager')->name('manager.')->middleware('role:Manager|HR|Super Admin')->group(function () {
        // Shift swap approvals
        Route::prefix('shift-swap-approvals')->name('shift-swap-approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'reject'])->name('reject');
            Route::post('/{id}/execute', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'execute'])->name('execute');
        });
    });

    // ========== PROFILE ROUTES ==========
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });

    // ========== APPROVAL ROUTES ==========
    Route::prefix('approvals')->name('approvals.')->middleware('role:Manager|HR|Super Admin')->group(function () {
        // Leave Approvals
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [LeaveApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [LeaveApprovalController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [LeaveApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [LeaveApprovalController::class, 'reject'])->name('reject');
        });
        // Overtime Approvals
        Route::prefix('overtimes')->name('overtimes.')->group(function () {
            Route::get('/', [OvertimeApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [OvertimeApprovalController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [OvertimeApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [OvertimeApprovalController::class, 'reject'])->name('reject');
        });
        // Document Approvals
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [DocumentApprovalController::class, 'show'])->name('show');
            Route::post('/{id}/verify', [DocumentApprovalController::class, 'verify'])->name('verify');
            Route::post('/{id}/reject', [DocumentApprovalController::class, 'reject'])->name('reject');
        });
    });

    // Business Trip Approvals (separate - needs Manager|HR|Super Admin role)
    Route::prefix('approvals/business-trips')->name('approvals.business-trips.')->middleware('role:Manager|HR|Super Admin')->group(function () {
        Route::get('/', [BusinessTripApprovalController::class, 'index'])->name('index');
        Route::get('/{id}', [BusinessTripApprovalController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [BusinessTripApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [BusinessTripApprovalController::class, 'reject'])->name('reject');
    });

    // ========== REPORT ROUTES ==========
    // ========== REPORT ROUTES ==========
    // Report pages (some were scaffolded but commented previously). Add specific routes as needed.
    Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
    Route::get('/reports/overtimes', [ReportController::class, 'overtimes'])->name('reports.overtimes');
    Route::get('/reports/worker-documents', [ReportController::class, 'workerDocuments'])->name('reports.worker-documents');
    Route::get('/reports/leaves/export', [ReportController::class, 'exportLeaves'])->name('reports.leaves.export');
    Route::get('/reports/overtimes/export', [ReportController::class, 'exportOvertimes'])->name('reports.overtimes.export');

    // ========== ROLE MANAGEMENT ==========
    Route::middleware(['permission:role.manage'])->group(function () {
        Route::resource('roles', RoleController::class)->names('admin.roles');
    });

    // ========== USER MANAGEMENT ==========
    Route::resource('users', UserController::class)->names('admin.users');

    // ========== WORKER MANAGEMENT ==========
    Route::prefix('workers')->name('admin.workers.')->group(function () {
        Route::get('/', [WorkerController::class, 'index'])->name('index');
        Route::get('/create', [WorkerController::class, 'create'])->name('create');
        Route::get('/export', [WorkerController::class, 'export'])->name('export');
        Route::get('/template', [WorkerController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [WorkerController::class, 'import'])->name('import');
        Route::post('/', [WorkerController::class, 'store'])->name('store');
        Route::get('/{id}', [WorkerController::class, 'show'])->name('show');
        Route::get('/{id}/attendance-history', [WorkerController::class, 'attendanceHistory'])->name('attendance-history');
        Route::get('/{id}/edit', [WorkerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkerController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/resign', [WorkerController::class, 'resign'])->name('resign');
    });

    // ========== ATTENDANCE MANAGEMENT ==========
    Route::prefix('attendance')->name('admin.attendance.')->group(function () {
        // Daftar pegawai untuk absensi
        Route::get('/workers', [AttendanceController::class, 'workerList'])->name('worker-list');
        // Riwayat absensi per pegawai
        Route::get('/history/{worker}', [AttendanceController::class, 'history'])->name('history');
        // Statistik detail pegawai
        Route::get('/stats/{worker}', [AttendanceController::class, 'workerStats'])->name('worker-stats');
        // Export statistik pegawai
        Route::get('/stats/{worker}/export-pdf', [AttendanceController::class, 'exportStatsPdf'])->name('stats.export-pdf');
        Route::get('/stats/{worker}/export-excel', [AttendanceController::class, 'exportStatsExcel'])->name('stats.export-excel');
        // Export absensi pegawai (PDF/Excel)
        Route::get('/history/{worker}/export', [AttendanceController::class, 'exportWorkerAttendance'])->name('history.export');
            Route::get('/history/{worker_id}', [AttendanceController::class, 'history'])->name('history');
        // Index default tetap bisa untuk legacy
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        // Admin check-in/check-out untuk worker tertentu
        Route::get('/{worker}/check-in', [AttendanceController::class, 'checkInForm'])->name('check-in-form');
        Route::post('/{worker}/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::get('/{worker}/check-out', [AttendanceController::class, 'checkOutForm'])->name('check-out-form');
        Route::post('/{worker}/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
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

    // ========== HOLIDAYS MANAGEMENT ==========
    Route::prefix('holidays')->name('admin.holidays.')->middleware(['auth', 'role:Super Admin|HR'])->group(function () {
        Route::get('/', [HolidayController::class, 'index'])->name('index');
        Route::get('/create', [HolidayController::class, 'create'])->name('create');
        Route::post('/', [HolidayController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [HolidayController::class, 'edit'])->name('edit');
        Route::put('/{id}', [HolidayController::class, 'update'])->name('update');
        Route::delete('/{id}', [HolidayController::class, 'destroy'])->name('destroy');
        Route::get('/bulk-create', [HolidayController::class, 'bulkCreate'])->name('bulk-create');
        Route::post('/bulk-store', [HolidayController::class, 'bulkStore'])->name('bulk-store');
        Route::get('/auto-generate', [HolidayController::class, 'autoGenerate'])->name('auto-generate');
        Route::post('/auto-generate', [HolidayController::class, 'storeAutoGenerate'])->name('auto-generate.store');
    });
});

// ========== API ROUTES ==========
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/workers/{workerId}/future-shifts', [\App\Http\Controllers\Api\WorkerShiftApiController::class, 'getFutureShifts']);
});

// ========== FALLBACK ROUTE ==========
Route::fallback(function () {
    return view('errors.404');
});
