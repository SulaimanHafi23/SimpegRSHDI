<?php



use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

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

// ========== DOCUMENT CONTROLLER ==========
use App\Http\Controllers\WorkerDocument\WorkerDocumentController;

// ========== MASTER DATA CONTROLLERS ==========
use App\Http\Controllers\Master\ShiftController;
use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\Master\DocumentTypeController;
use App\Http\Controllers\Master\DepartmentDocumentTypeController;
use App\Http\Controllers\Master\LeaveTypeController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\AuditLogController;

// Dashboard Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\HR\HRDashboardController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\ShiftController as EmployeeShiftController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\BusinessTripController as EmployeeBusinessTripController;
use App\Http\Controllers\Employee\DocumentController as EmployeeDocumentController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;

// Approval controllers
use App\Http\Controllers\Approval\BusinessTripApprovalController;

// Profile Controller
use App\Http\Controllers\ProfileController;

// Approval Controllers
use App\Http\Controllers\Approval\DocumentApprovalController;

// Report Controller
use App\Http\Controllers\Report\ReportController;

// ========== AUTH ROUTES ==========
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        // Route based on first available dashboard permission
        if ($user->can('dashboard.manager')) {
            return redirect()->route('manager.dashboard');
        }

        if ($user->can('dashboard.hr')) {
            return redirect()->route('hr.dashboard');
        }

        if ($user->can('dashboard.admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->can('dashboard.employee')) {
            return redirect()->route('employee.dashboard');
        }

        // Default fallback
        return redirect()->route('employee.dashboard');
    }
    return view('welcome');
})->name('home');

Route::view('/privacy', 'public.privacy')->name('public.privacy');
Route::view('/terms', 'public.terms')->name('public.terms');
Route::view('/help', 'public.help')->name('public.help');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.post');

    // Password Reset Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('throttle:3,1')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ========== WORLD TIME API ==========
Route::middleware('auth')->get('/api/world-time', function () {
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
        Log::warning('World Time API failed, using server time', ['error' => $e->getMessage()]);
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

// ========== STORAGE FALLBACK (for files in disk: public) ==========
// This keeps existing URLs like /storage/... working even when symlink/public mapping is unavailable.
Route::get('/storage/{path}', function (string $path) {
    $normalizedPath = str_replace('\\', '/', $path);

    if (str_contains($normalizedPath, '..')) {
        abort(404);
    }

    $disk = Storage::disk('public');
    if (!$disk->exists($normalizedPath)) {
        abort(404);
    }

    return response()->file($disk->path($normalizedPath), [
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*')->name('storage.fallback');

// ========== AUTHENTICATED ROUTES ==========
Route::middleware(['auth', 'redirect_role'])->group(function () {

    // ========== DASHBOARDS ==========
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware('permission:dashboard.admin')->name('admin.dashboard');

    // HR Dashboard
    Route::get('/hr/dashboard', [HRDashboardController::class, 'index'])->middleware('permission:dashboard.hr')->name('hr.dashboard');

    // Manager Dashboard
    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->middleware('permission:dashboard.manager')->name('manager.dashboard');

    // ========== EMPLOYEE ROUTES ==========
    Route::prefix('employee')->name('employee.')->middleware('permission:dashboard.employee')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        // Attendance for employees
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [EmployeeAttendanceController::class, 'index'])->name('index');
            Route::get('/export', [EmployeeAttendanceController::class, 'export'])->name('export');
            Route::get('/export-pdf', [EmployeeAttendanceController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/check-in', [EmployeeAttendanceController::class, 'checkInForm'])->name('check-in-form');
            Route::post('/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('check-in');
            Route::get('/check-out', [EmployeeAttendanceController::class, 'checkOutForm'])->name('check-out-form');
            Route::post('/check-out/{id}', [EmployeeAttendanceController::class, 'checkOut'])->name('check-out');
            Route::get('/{id}/photo/{type}', [EmployeeAttendanceController::class, 'photo'])
                ->where('type', 'check_in|check_out')
                ->name('photo');
            Route::get('/{id}', [EmployeeAttendanceController::class, 'show'])->name('show');
        });

        // Shift swaps for employees
        Route::prefix('shift-swaps')->name('shift-swaps.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'export'])->name('export');
            Route::get('/create', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'store'])->name('store');
            Route::get('/api/worker-shifts-in-range', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'getWorkerShiftsInDateRange'])->name('api.worker-shifts-in-range');
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
            Route::get('/export', [EmployeeLeaveController::class, 'export'])->name('export');
            Route::get('/export-pdf', [EmployeeLeaveController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/create', [EmployeeLeaveController::class, 'create'])->name('create');
            Route::post('/', [EmployeeLeaveController::class, 'store'])->name('store');
            Route::get('/{id}', [EmployeeLeaveController::class, 'show'])->name('show');
            Route::delete('/{id}', [EmployeeLeaveController::class, 'cancel'])->name('cancel');
        });

        // Business Trip requests for employees
        Route::prefix('business-trips')->name('business-trips.')->group(function () {
            Route::get('/', [EmployeeBusinessTripController::class, 'index'])->name('index');
            Route::get('/export', [EmployeeBusinessTripController::class, 'export'])->name('export');
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
            Route::get('/{id}/preview', [EmployeeDocumentController::class, 'preview'])->name('preview');
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

        // Employee calendar content has been merged into the shift schedule page.
        Route::get('/calendar', [\App\Http\Controllers\Employee\CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [\App\Http\Controllers\Employee\CalendarController::class, 'events'])->name('calendar.events');
    });

    // ========== MANAGER ROUTES ==========
    Route::prefix('manager')->name('manager.')->middleware('permission:shift-swap.approve')->group(function () {
        // Shift swap approvals
        Route::prefix('shift-swap-approvals')->name('shift-swap-approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'export'])->name('export');
            Route::get('/{id}', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'reject'])->name('reject');
            Route::post('/{id}/execute', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'execute'])->name('execute');
            Route::post('/{id}/revert', [\App\Http\Controllers\Manager\ShiftSwapApprovalController::class, 'revert'])->name('revert');
        });
    });

    // ========== PROFILE ROUTES ==========
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });

    // ========== GLOBAL NOTIFICATIONS ==========
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('unread');
        Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

    // ========== APPROVAL ROUTES ==========
    Route::prefix('approvals')->name('approvals.')->middleware('permission:leave.approve')->group(function () {
        // Leave Approvals
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'approvalIndex'])->name('index');
            Route::get('/{id}', [LeaveRequestController::class, 'approvalShow'])->name('show');
            Route::post('/{id}/verify', [LeaveRequestController::class, 'approvalVerify'])->name('verify');
            Route::post('/{id}/approve', [LeaveRequestController::class, 'approvalApprove'])->name('approve');
            Route::post('/{id}/reject', [LeaveRequestController::class, 'approvalReject'])->name('reject');
        });
        // Document Approvals
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [DocumentApprovalController::class, 'show'])->name('show');
            Route::post('/{id}/verify', [DocumentApprovalController::class, 'verify'])->name('verify');
            Route::post('/{id}/reject', [DocumentApprovalController::class, 'reject'])->name('reject');
        });
    });

    // Business Trip Approvals (separate - needs business-trip.approve permission)
    Route::prefix('approvals/business-trips')->name('approvals.business-trips.')->middleware('permission:business-trip.approve')->group(function () {
        Route::get('/', [BusinessTripApprovalController::class, 'index'])->name('index');
        Route::get('/export', [BusinessTripApprovalController::class, 'export'])->name('export');
        Route::get('/{id}', [BusinessTripApprovalController::class, 'show'])->name('show');
        Route::post('/{id}/verify', [BusinessTripApprovalController::class, 'verify'])->name('verify');
        Route::post('/{id}/approve', [BusinessTripApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [BusinessTripApprovalController::class, 'reject'])->name('reject');
        Route::delete('/{id}', [BusinessTripApprovalController::class, 'destroy'])->name('destroy');
    });

    // ========== REPORT ROUTES ==========
    Route::middleware('permission:report.view')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/leaves', [ReportController::class, 'leaves'])->name('leaves');
        Route::get('/worker-documents', [ReportController::class, 'workerDocuments'])->name('worker-documents');

        // Export routes with format support (pdf, excel, csv)
        Route::get('/attendance/export', [ReportController::class, 'exportAttendance'])->name('attendance.export');
        Route::get('/leaves/export', [ReportController::class, 'exportLeaves'])->name('leaves.export');
        Route::get('/worker-documents/export', [ReportController::class, 'exportWorkerDocuments'])->name('worker-documents.export');
    });

    // ========== ROLE MANAGEMENT ==========
    Route::middleware(['permission:role.manage'])->group(function () {
        Route::resource('roles', RoleController::class)->names('admin.roles');
    });

    // ========== USER MANAGEMENT ==========
    Route::middleware('permission:user.manage')->resource('users', UserController::class)->names('admin.users');

    // ========== WORKER MANAGEMENT ==========
    Route::prefix('workers')->name('admin.workers.')->group(function () {
        Route::get('/', [WorkerController::class, 'index'])->name('index');
        Route::get('/create', [WorkerController::class, 'create'])->name('create');
        Route::get('/export', [WorkerController::class, 'export'])->name('export');
        Route::middleware('permission:worker.manage')->group(function () {
            Route::get('/template', [WorkerController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [WorkerController::class, 'import'])->name('import');
        });
        Route::post('/', [WorkerController::class, 'store'])->name('store');
        Route::get('/{id}', [WorkerController::class, 'show'])->name('show');
        Route::get('/{id}/attendance-history', [WorkerController::class, 'attendanceHistory'])->name('attendance-history');
        Route::get('/{id}/edit', [WorkerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkerController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/resign', [WorkerController::class, 'resign'])->name('resign');

        // Off-day management routes
        Route::prefix('{workerId}/off-days')->name('off-days.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Worker\WorkerOffDayController::class, 'index'])->name('index');
            Route::post('/patterns', [\App\Http\Controllers\Worker\WorkerOffDayController::class, 'storePattern'])->name('store-pattern');
            Route::delete('/patterns/{patternId}', [\App\Http\Controllers\Worker\WorkerOffDayController::class, 'destroyPattern'])->name('destroy-pattern');
            Route::post('/check-date', [\App\Http\Controllers\Worker\WorkerOffDayController::class, 'checkDate'])->name('check-date');
            Route::post('/range', [\App\Http\Controllers\Worker\WorkerOffDayController::class, 'getRange'])->name('range');
        });
    });

    // ========== ATTENDANCE MANAGEMENT ==========
    Route::prefix('attendance')->name('admin.attendance.')->group(function () {
        // Daftar pegawai untuk absensi
        Route::get('/workers', [AttendanceController::class, 'workerList'])->name('worker-list');
        // Riwayat absensi per pegawai
        Route::get('/history/{worker}', [AttendanceController::class, 'history'])->name('history');
        // Export riwayat absensi per pegawai (PDF/Excel)
        Route::get('/history/{worker}/export-daily', [AttendanceController::class, 'exportWorkerHistory'])->name('history.export-daily');
        // Statistik detail pegawai
        Route::get('/stats/{worker}', [AttendanceController::class, 'workerStats'])->name('worker-stats');
        // Export statistik pegawai
        Route::get('/stats/{worker}/export-pdf', [AttendanceController::class, 'exportStatsPdf'])->name('stats.export-pdf');
        Route::get('/stats/{worker}/export-excel', [AttendanceController::class, 'exportStatsExcel'])->name('stats.export-excel');
        // Export absensi pegawai (PDF/Excel)
        Route::get('/history/{worker}/export', [AttendanceController::class, 'exportWorkerAttendance'])->name('history.export');
        // Export Absensi Hari Ini
        Route::get('/today/export', [AttendanceController::class, 'exportTodayAttendance'])->name('today.export');
        // Index default tetap bisa untuk legacy
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        // Admin check-in/check-out untuk worker tertentu
        Route::get('/{worker}/check-in', [AttendanceController::class, 'checkInForm'])->name('check-in-form');
        Route::post('/{worker}/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::get('/{worker}/check-out', [AttendanceController::class, 'checkOutForm'])->name('check-out-form');
        Route::post('/{worker}/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
        Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
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
        Route::get('/generate', [WorkerShiftController::class, 'generate'])->name('generate');
        Route::post('/generate', [WorkerShiftController::class, 'generateStore'])->name('generate.store');
        Route::get('/calendar-data', [WorkerShiftController::class, 'calendarData'])->name('calendar-data');
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
        Route::get('/export', [LeaveRequestController::class, 'export'])->name('export');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [LeaveRequestController::class, 'show'])->name('show');
        Route::delete('/{id}', [LeaveRequestController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        Route::post('/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
        Route::get('/worker/{workerId}/balance', [LeaveRequestController::class, 'workerLeaveBalance'])->name('worker-balance');
    });

    // ========== WORKER DOCUMENT MANAGEMENT ==========
    Route::prefix('worker-documents')->name('admin.worker-documents.')->group(function () {
        Route::get('/', [WorkerDocumentController::class, 'index'])->name('index');
        Route::get('/create', [WorkerDocumentController::class, 'create'])->name('create');
        Route::post('/', [WorkerDocumentController::class, 'store'])->name('store');
        // AJAX: get allowed document types for a given worker (must be before /{id})
        Route::get('/document-types-for-worker', [WorkerDocumentController::class, 'documentTypesForWorker'])->name('document-types-for-worker');
        Route::get('/expired', [WorkerDocumentController::class, 'expired'])->name('expired');
        Route::get('/expiring', [WorkerDocumentController::class, 'expiring'])->name('expiring');
        Route::get('/worker/{workerId}', [WorkerDocumentController::class, 'workerDocuments'])->name('worker-documents');
        Route::get('/{id}', [WorkerDocumentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [WorkerDocumentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkerDocumentController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkerDocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/verify', [WorkerDocumentController::class, 'verify'])->name('verify');
        Route::post('/{id}/reject', [WorkerDocumentController::class, 'reject'])->name('reject');
        Route::get('/{id}/download', [WorkerDocumentController::class, 'download'])->name('download');
    });

    // ========== MASTER DATA MANAGEMENT ==========
    Route::prefix('master')->name('admin.master.')->middleware('permission:master.manage')->group(function () {

        // Departments (Pengganti Positions)
        Route::resource('departments', DepartmentController::class);

        // Shifts
        Route::resource('shifts', ShiftController::class);

        // Document Types
        Route::resource('document-types', DocumentTypeController::class);
    // Department <-> Document Type mappings
    Route::resource('department-document-types', DepartmentDocumentTypeController::class);

        // Leave Types
        Route::resource('leave-types', LeaveTypeController::class);
    });

    // ========== HOLIDAYS MANAGEMENT ==========
    Route::prefix('holidays')->name('admin.holidays.')->middleware(['auth', 'permission:holiday.manage'])->group(function () {
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

    // ========== AUDIT LOG ==========
    Route::prefix('audit-logs')->name('admin.audit-logs.')->middleware(['auth', 'permission:audit.view'])->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{id}', [AuditLogController::class, 'show'])->name('show');
    });
});

// ========== API ROUTES ==========
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/workers/{workerId}/future-shifts', [\App\Http\Controllers\Api\WorkerShiftApiController::class, 'getFutureShifts']);
    // Returns shift start/end time for a worker on a given date
    Route::get('/workers/{workerId}/shift-time', [\App\Http\Controllers\Api\WorkerShiftApiController::class, 'getShiftTime']);
});

// ========== FALLBACK ROUTE ==========
Route::fallback(function () {
    return view('errors.404');
});
