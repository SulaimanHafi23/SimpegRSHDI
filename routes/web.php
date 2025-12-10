<?php

use App\Http\Controllers\Auth\LoginController;

// Master Controllers
use App\Http\Controllers\Admin\Master\DocumentTypeController;
use App\Http\Controllers\Admin\Master\FileRequirementController;
use App\Http\Controllers\Admin\Master\GenderController;
use App\Http\Controllers\Admin\Master\LocationController;
use App\Http\Controllers\Admin\Master\PositionController;
use App\Http\Controllers\Admin\Master\ReligionController;
use App\Http\Controllers\Admin\Master\ShiftController;
use App\Http\Controllers\Admin\Master\ShiftPatternController;

// Worker & User Controllers
use App\Http\Controllers\Admin\Worker\WorkerController;
use App\Http\Controllers\Admin\User\UserController;

// Attendance & Schedule Controllers
use App\Http\Controllers\Admin\Attendance\AbsentController;
use App\Http\Controllers\Admin\Schedule\WorkerShiftScheduleController;

// Leave & Overtime Controllers
use App\Http\Controllers\Admin\Leave\LeaveRequestController;
use App\Http\Controllers\Admin\Overtime\OvertimeController;

// Business Trip Controllers
use App\Http\Controllers\Admin\BusinessTrip\BusinessTripController;
use App\Http\Controllers\Admin\BusinessTrip\BusinessTripReportController;

// Document Controllers
use App\Http\Controllers\Admin\Document\BerkasController;

use Illuminate\Support\Facades\Route;

// ========== HOME / WELCOME PAGE ==========
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// ========== PUBLIC AUTH ROUTES ==========
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');
});

// ========== PROTECTED ROUTES (AUTHENTICATED USERS) ==========
Route::middleware('auth')->group(function () {
    
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard (default redirect after login)
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // ========== ADMIN ROUTES ==========
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('dashboard', function () {
            return view('admin.dashboard.index');
        })->name('dashboard');
        
        // ===== MASTER DATA ROUTES =====
        Route::prefix('master')->name('master.')->group(function () {
            // Religions
            Route::resource('religions', ReligionController::class);
            
            // Genders
            Route::resource('genders', GenderController::class);
            
            // Positions
            Route::resource('positions', PositionController::class);
            
            // Locations
            Route::resource('locations', LocationController::class);
            
            // Document Types
            Route::resource('document-types', DocumentTypeController::class);
            
            // File Requirements
            Route::resource('file-requirements', FileRequirementController::class);
            Route::get('file-requirements/position/{positionId}', [FileRequirementController::class, 'byPosition'])
                ->name('file-requirements.by-position');
        });
        
        // ===== SHIFT MANAGEMENT ROUTES =====
        Route::prefix('settings')->name('settings.')->group(function () {
            // Shifts
            Route::resource('shifts', ShiftController::class);
            
            // Shift Patterns
            Route::resource('shift-patterns', ShiftPatternController::class);
        });
        
        // ===== WORKER ROUTES =====
        Route::prefix('workers')->name('workers.')->group(function () {
            Route::get('/', [WorkerController::class, 'index'])->name('index');
            Route::get('create', [WorkerController::class, 'create'])->name('create');
            Route::post('/', [WorkerController::class, 'store'])->name('store');
            Route::get('{id}', [WorkerController::class, 'show'])->name('show');
            Route::get('{id}/edit', [WorkerController::class, 'edit'])->name('edit');
            Route::put('{id}', [WorkerController::class, 'update'])->name('update');
            Route::delete('{id}', [WorkerController::class, 'destroy'])->name('destroy');
            
            // Additional worker routes
            Route::get('{id}/documents', [WorkerController::class, 'documents'])->name('documents');
            Route::get('{id}/attendance', [WorkerController::class, 'attendance'])->name('attendance');
            Route::get('{id}/leaves', [WorkerController::class, 'leaves'])->name('leaves');
        });
        
        // ===== USER MANAGEMENT ROUTES =====
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('{id}', [UserController::class, 'show'])->name('show');
            Route::get('{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('{id}', [UserController::class, 'update'])->name('update');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
            
            // Password & roles
            Route::put('{id}/password', [UserController::class, 'updatePassword'])->name('update-password');
            Route::put('{id}/roles', [UserController::class, 'updateRoles'])->name('update-roles');
        });
        
        // ===== ATTENDANCE ROUTES =====
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AbsentController::class, 'index'])->name('index');
            Route::get('create', [AbsentController::class, 'create'])->name('create');
            Route::post('/', [AbsentController::class, 'store'])->name('store');
            Route::get('{id}', [AbsentController::class, 'show'])->name('show');
            Route::get('{id}/edit', [AbsentController::class, 'edit'])->name('edit');
            Route::put('{id}', [AbsentController::class, 'update'])->name('update');
            Route::delete('{id}', [AbsentController::class, 'destroy'])->name('destroy');
            
            // Attendance reports
            Route::get('reports/daily', [AbsentController::class, 'dailyReport'])->name('daily-report');
            Route::get('reports/monthly', [AbsentController::class, 'monthlyReport'])->name('monthly-report');
            Route::get('worker/{workerId}', [AbsentController::class, 'workerAttendance'])->name('worker-attendance');
        });
        
        // ===== SHIFT SCHEDULE ROUTES =====
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [WorkerShiftScheduleController::class, 'index'])->name('index');
            Route::get('create', [WorkerShiftScheduleController::class, 'create'])->name('create');
            Route::post('/', [WorkerShiftScheduleController::class, 'store'])->name('store');
            Route::get('{id}', [WorkerShiftScheduleController::class, 'show'])->name('show');
            Route::get('{id}/edit', [WorkerShiftScheduleController::class, 'edit'])->name('edit');
            Route::put('{id}', [WorkerShiftScheduleController::class, 'update'])->name('update');
            Route::delete('{id}', [WorkerShiftScheduleController::class, 'destroy'])->name('destroy');
            
            // Additional schedule routes
            Route::get('worker/{workerId}', [WorkerShiftScheduleController::class, 'workerSchedule'])->name('worker-schedule');
            Route::post('bulk-create', [WorkerShiftScheduleController::class, 'bulkCreate'])->name('bulk-create');
            Route::get('calendar', [WorkerShiftScheduleController::class, 'calendar'])->name('calendar');
        });
        
        // ===== LEAVE REQUEST ROUTES =====
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
        
        // ===== OVERTIME ROUTES =====
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
        
        // ===== BUSINESS TRIP ROUTES =====
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
        
        // ===== BUSINESS TRIP REPORT ROUTES =====
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
        
        // ===== DOCUMENT (BERKAS) ROUTES =====
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
            
            // Download
            Route::get('{id}/download', [BerkasController::class, 'download'])->name('download');
            Route::get('{id}/preview', [BerkasController::class, 'preview'])->name('preview');
        });
    });
    
    // ========== HR ROUTES ==========
    Route::prefix('hr')->name('hr.')->middleware('role:HR')->group(function () {
        Route::get('dashboard', function () {
            return view('hr.dashboard');
        })->name('dashboard');
    });
    
    // ========== MANAGER ROUTES ==========
    Route::prefix('manager')->name('manager.')->middleware('role:Manager')->group(function () {
        Route::get('dashboard', function () {
            return view('manager.dashboard');
        })->name('dashboard');
    });
    
    // ========== USER (EMPLOYEE) ROUTES ==========
    Route::prefix('employee')->name('employee.')->middleware('role:User')->group(function () {
        Route::get('dashboard', function () {
            return view('employee.dashboard.index');
        })->name('dashboard');
        
        // Employee self-service
        Route::get('profile', function () {
            return view('employee.profile');
        })->name('profile');
        
        Route::get('attendance', function () {
            return view('employee.attendance');
        })->name('attendance');
        
        Route::get('leaves', function () {
            return view('employee.leaves');
        })->name('leaves');
        
        Route::get('documents', function () {
            return view('employee.documents');
        })->name('documents');
    });
});

// Fallback
Route::fallback(function () {
    return view('errors.404');
});
