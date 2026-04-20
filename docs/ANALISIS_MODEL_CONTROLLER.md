# Analisis Model dan Controller

Dibuat: 2026-04-06T01:05:26
Total Model: 22
Total Controller: 43

## Model

### Attendance
- File: C:\laragon\www\SimpegRSHDI\app\Models\Attendance.php
- Table: 
- Fillable: 'worker_id', 'shift_id', 'attendance_date', 'check_in', 'check_out', 'distance_check_in', 'distance_check_out', 'check_in_by_admin', 'check_in_admin_id', 'check_out_by_admin', 'check_out_admin_id', 'status', 'is_late', 'late_minutes', 'is_early_leave', 'early_leave_minutes', 'notes',
- Guarded: 
- Casts: 'attendance_date' => 'date', 'check_in' => 'datetime', 'check_out' => 'datetime', 'distance_check_in' => 'integer', 'distance_check_out' => 'integer', 'check_in_by_admin' => 'boolean', 'check_out_by_admin' => 'boolean', 'is_late' => 'boolean', 'late_minutes' => 'integer', 'is_early_leave' => 'boolean', 'early_leave_minutes' => 'integer',
- Relation Types: belongsTo, hasMany
- Public Methods: worker, shift, checkInAdmin, checkOutAdmin, photos, checkInPhoto, checkOutPhoto

### AttendancePhoto
- File: C:\laragon\www\SimpegRSHDI\app\Models\AttendancePhoto.php
- Table: 
- Fillable: 'attendance_id', 'photo_path', 'photo_type', 'taken_at', 'created_at',
- Guarded: 
- Casts: 'taken_at' => 'datetime', 'created_at' => 'datetime',
- Relation Types: belongsTo
- Public Methods: attendance

### AuditLog
- File: C:\laragon\www\SimpegRSHDI\app\Models\AuditLog.php
- Table: 
- Fillable: 'user_id', 'user_name', 'action', 'auditable_type', 'auditable_id', 'description', 'old_values', 'new_values', 'ip_address', 'user_agent', 'url',
- Guarded: 
- Casts: 'old_values' => 'array', 'new_values' => 'array',
- Relation Types: belongsTo, morphTo
- Public Methods: user, auditable, scopeForModel, scopeByUser, scopeByAction, scopeRecent, getModelNameAttribute, getActionBadgeAttribute

### BusinessTrip
- File: C:\laragon\www\SimpegRSHDI\app\Models\BusinessTrip.php
- Table: 
- Fillable: 'worker_id', 'destination', 'purpose', 'start_date', 'end_date', 'trip_duration_type', 'half_day_session', 'transportation', 'accommodation', 'notes', 'supporting_document_path', 'estimated_cost', 'status', 'approved_by', 'approved_at', 'rejection_reason',
- Guarded: 
- Casts: 'start_date' => 'date', 'end_date' => 'date', 'estimated_cost' => 'decimal:2', 'approved_at' => 'datetime',
- Relation Types: belongsTo
- Public Methods: worker, approvedBy, getHalfDaySessionLabelAttribute, getDurationValueAttribute, getDurationLabelAttribute

### Department
- File: C:\laragon\www\SimpegRSHDI\app\Models\Department.php
- Table: 
- Fillable: 'name', 'code', 'description', 'is_active', 'requires_holiday_attendance', 'parent_id', 'manager_id',
- Guarded: 
- Casts: 'is_active' => 'boolean', 'requires_holiday_attendance' => 'boolean',
- Relation Types: hasMany, belongsTo, belongsToMany
- Public Methods: workers, parent, children, manager, documentTypes

### DepartmentDocumentType
- File: C:\laragon\www\SimpegRSHDI\app\Models\DepartmentDocumentType.php
- Table: 
- Fillable: 'department_id', 'document_type_id',
- Guarded: 
- Casts: 
- Relation Types: belongsTo
- Public Methods: department, documentType

### DocumentType
- File: C:\laragon\www\SimpegRSHDI\app\Models\DocumentType.php
- Table: 
- Fillable: 'name', 'description', 'file_format', 'max_file_size', 'is_required', 'is_universal', 'is_active',
- Guarded: 
- Casts: 'is_required' => 'boolean', 'is_universal' => 'boolean', 'is_active' => 'boolean',
- Relation Types: hasMany, belongsToMany
- Public Methods: workerDocuments, departments

### Holiday
- File: C:\laragon\www\SimpegRSHDI\app\Models\Holiday.php
- Table: 
- Fillable: 'name', 'date', 'description', 'is_national',
- Guarded: 
- Casts: 'date' => 'date', 'is_national' => 'boolean',
- Relation Types: 
- Public Methods: scopeNational, scopeDateRange, scopeYear

### LeaveRequest
- File: C:\laragon\www\SimpegRSHDI\app\Models\LeaveRequest.php
- Table: 
- Fillable: 'worker_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'attachment_path', 'status', 'approved_by', 'approved_at', 'rejection_reason',
- Guarded: 
- Casts: 'start_date' => 'date', 'end_date' => 'date', 'total_days' => 'integer', 'approved_at' => 'datetime',
- Relation Types: belongsTo
- Public Methods: worker, leaveType, approver

### LeaveType
- File: C:\laragon\www\SimpegRSHDI\app\Models\LeaveType.php
- Table: 
- Fillable: 'name', 'code', 'max_days_per_year', 'requires_approval', 'requires_attachment', 'days_notice', 'is_active',
- Guarded: 
- Casts: 'max_days_per_year' => 'integer', 'requires_approval' => 'boolean', 'requires_attachment' => 'boolean', 'days_notice' => 'integer', 'is_active' => 'boolean',
- Relation Types: hasMany
- Public Methods: leaveRequests

### Notification
- File: C:\laragon\www\SimpegRSHDI\app\Models\Notification.php
- Table: 
- Fillable: 'user_id', 'notifiable_type', 'notifiable_id', 'type', 'title', 'message', 'data', 'read_at',
- Guarded: 
- Casts: 'data' => 'array', 'read_at' => 'datetime',
- Relation Types: belongsTo
- Public Methods: user, isRead, markAsRead, scopeUnread, scopeRead, getTitleAttribute, getMessageAttribute, getIsReadAttribute

### Shift
- File: C:\laragon\www\SimpegRSHDI\app\Models\Shift.php
- Table: 
- Fillable: 'name', 'start_time', 'end_time', 'total_hours', 'grace_period_minutes', 'is_overnight', 'is_active',
- Guarded: 
- Casts: 'start_time' => 'datetime:H:i:s', 'end_time' => 'datetime:H:i:s', 'total_hours' => 'integer', 'grace_period_minutes' => 'integer', 'is_overnight' => 'boolean', 'is_active' => 'boolean',
- Relation Types: hasMany
- Public Methods: workerShifts, dayTimes, shiftOverrides, attendances, getScheduleForDate

### ShiftDayTime
- File: C:\laragon\www\SimpegRSHDI\app\Models\ShiftDayTime.php
- Table: 
- Fillable: 'shift_id', 'day_of_week', 'start_time', 'end_time',
- Guarded: 
- Casts: 'day_of_week' => 'integer', 'start_time' => 'datetime:H:i:s', 'end_time' => 'datetime:H:i:s',
- Relation Types: belongsTo
- Public Methods: shift

### ShiftOverride
- File: C:\laragon\www\SimpegRSHDI\app\Models\ShiftOverride.php
- Table: 
- Fillable: 'worker_id', 'shift_id', 'override_date', 'reason', 'created_by', 'shift_swap_request_id',
- Guarded: 
- Casts: 'override_date' => 'date',
- Relation Types: belongsTo
- Public Methods: worker, shift, creator, shiftSwapRequest

### ShiftSwapAuditLog
- File: C:\laragon\www\SimpegRSHDI\app\Models\ShiftSwapAuditLog.php
- Table: 
- Fillable: 'shift_swap_request_id', 'user_id', 'action', 'old_status', 'new_status', 'notes', 'metadata', 'user_agent',
- Guarded: 
- Casts: 'metadata' => 'array',
- Relation Types: belongsTo
- Public Methods: shiftSwapRequest, user

### ShiftSwapRequest
- File: C:\laragon\www\SimpegRSHDI\app\Models\ShiftSwapRequest.php
- Table: 
- Fillable: 'requester_id', 'target_worker_id', 'requester_shift_id', 'target_shift_id', 'swap_type', 'swap_start_date', 'swap_end_date', 'swap_dates', 'status', 'requires_manager_approval', 'manager_id', 'manager_approved_at', 'reason', 'metadata', 'requested_at', 'executed_by', 'executed_at',
- Guarded: 
- Casts: 'requires_manager_approval' => 'boolean', 'metadata' => 'array', 'swap_dates' => 'array', 'swap_start_date' => 'date', 'swap_end_date' => 'date', 'requested_at' => 'datetime', 'manager_approved_at' => 'datetime', 'executed_at' => 'datetime',
- Relation Types: belongsTo, hasMany
- Public Methods: getSwapDateAttribute, setSwapDateAttribute, requester, targetWorker, requesterShift, targetShift, manager, executedBy, auditLogs

### User
- File: C:\laragon\www\SimpegRSHDI\app\Models\User.php
- Table: 
- Fillable: 'worker_id', 'email', 'username', 'password', 'email_verified_at', 'last_login', 'is_active',
- Guarded: 
- Casts: 'email_verified_at' => 'datetime', 'last_login' => 'datetime', 'is_active' => 'boolean', 'password' => 'hashed',
- Relation Types: belongsTo, hasMany
- Public Methods: worker, createdShiftOverrides, verifiedDocuments, approvedLeaveRequests

### Worker
- File: C:\laragon\www\SimpegRSHDI\app\Models\Worker.php
- Table: 
- Fillable: 'nip', 'name', 'email', 'phone_number', 'address', 'birth_date', 'birth_place', 'gender', 'religion', 'department_id', 'shift_id', 'hire_date', 'resign_date', 'employment_status', 'status', 'photo_url',
- Guarded: 
- Casts: 'birth_date' => 'date', 'hire_date' => 'date', 'resign_date' => 'date', 'gender' => 'string', 'religion' => 'string',
- Relation Types: belongsTo, hasOne, hasMany
- Public Methods: department, shift, user, workerShifts, shiftOverrides, shiftHistories, attendances, workerDocuments, leaveRequests, shiftSwapRequestsAsRequester, shiftSwapRequestsAsTarget, offDays, activeWorkerShift, getShiftForDate, isOffDay, canCheckOutOnDate, getCurrentShift, resolveShiftForDate

### WorkerDocument
- File: C:\laragon\www\SimpegRSHDI\app\Models\WorkerDocument.php
- Table: 
- Fillable: 'worker_id', 'document_type_id', 'department_document_type_id', 'file_name', 'file_path', 'file_size', 'expired_date', 'status', 'verified_by', 'verified_at', 'notes',
- Guarded: 
- Casts: 'file_size' => 'integer', 'expired_date' => 'date', 'verified_at' => 'datetime',
- Relation Types: belongsTo
- Public Methods: worker, documentType, departmentDocumentType, verifier, isExpired, getFileSizeHumanAttribute

### WorkerOffDay
- File: C:\laragon\www\SimpegRSHDI\app\Models\WorkerOffDay.php
- Table: 
- Fillable: 'worker_id', 'day_of_week', 'effective_from', 'effective_until', 'reason', 'created_by',
- Guarded: 
- Casts: 'day_of_week' => 'array', 'effective_from' => 'date', 'effective_until' => 'date',
- Relation Types: belongsTo
- Public Methods: worker, createdBy

### WorkerShift
- File: C:\laragon\www\SimpegRSHDI\app\Models\WorkerShift.php
- Table: 
- Fillable: 'worker_id', 'shift_id', 'effective_from', 'effective_until', 'is_active', 'notes',
- Guarded: 
- Casts: 'effective_from' => 'date', 'effective_until' => 'date', 'is_active' => 'boolean',
- Relation Types: belongsTo
- Public Methods: getStartDateAttribute, getEndDateAttribute, worker, shift, getShiftForDate, isActiveOnDate

### WorkerShiftHistory
- File: C:\laragon\www\SimpegRSHDI\app\Models\WorkerShiftHistory.php
- Table: 
- Fillable: 'worker_id', 'shift_id', 'effective_from', 'effective_until', 'changed_at', 'change_reason', 'changed_by', 'notes',
- Guarded: 
- Casts: 'effective_from' => 'date', 'effective_until' => 'date', 'changed_at' => 'date',
- Relation Types: belongsTo
- Public Methods: worker, shift, changedByUser

## Controller

### NotificationController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\NotificationController.php
- Methods: __construct, index, unread, unreadCount, markAsRead, markAllAsRead, destroy

### ProfileController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\ProfileController.php
- Methods: __construct, show, edit, update, updatePassword

### AuditLogController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Admin\AuditLogController.php
- Methods: index, show

### DashboardController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Admin\DashboardController.php
- Methods: __construct, index, getTodayAttendanceSummary, getPendingApprovalsCount

### HolidayController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Admin\HolidayController.php
- Methods: __construct, index, create, store, edit, update, destroy, bulkCreate, bulkStore, autoGenerate, storeAutoGenerate

### WorkerShiftApiController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Api\WorkerShiftApiController.php
- Methods: __construct, getShiftTime, getFutureShifts

### BusinessTripApprovalController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Approval\BusinessTripApprovalController.php
- Methods: __construct, index, show, approve, reject, destroy, export

### DocumentApprovalController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Approval\DocumentApprovalController.php
- Methods: __construct, index, show, verify, reject

### LeaveApprovalController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Approval\LeaveApprovalController.php
- Methods: __construct, index, show, approve, reject

### AttendanceController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Attendance\AttendanceController.php
- Methods: __construct, index, create, checkInForm, checkIn, checkOutForm, checkOut, show, edit, update, destroy, dailyReport, monthlyReport, export, workerList, history, exportWorkerAttendance, exportWorkerHistory, workerStats, exportStatsPdf, exportStatsExcel, exportTodayAttendance, getAttendanceDetail

### ForgotPasswordController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Auth\ForgotPasswordController.php
- Methods: showLinkRequestForm, sendResetLinkEmail

### LoginController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Auth\LoginController.php
- Methods: __construct, showLoginForm, login, logout

### ResetPasswordController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Auth\ResetPasswordController.php
- Methods: showResetForm, reset

### BerkasController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Document\BerkasController.php
- Methods: __construct, index, show, create, store, edit, update, destroy, verify, reject, pending, workerDocuments, checkCompleteness, download, preview

### AttendanceController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\AttendanceController.php
- Methods: __construct, index, checkInForm, checkOutForm, checkIn, checkOut, show, photo, export, exportPdf

### BusinessTripController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\BusinessTripController.php
- Methods: __construct, index, create, store, show, cancel, export

### CalendarController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\CalendarController.php
- Methods: __construct, index, events

### DashboardController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\DashboardController.php
- Methods: __construct, index

### DocumentController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\DocumentController.php
- Methods: __construct, index, create, store, show, download, preview, destroy

### LeaveController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\LeaveController.php
- Methods: __construct, index, create, store, show, cancel, export, exportPdf

### NotificationController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\NotificationController.php
- Methods: __construct, index, getUnreadCount, getUnread, markAsRead, markAllAsRead, destroy

### ProfileController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\ProfileController.php
- Methods: __construct, show, edit, update, updatePassword

### ShiftController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\ShiftController.php
- Methods: __construct, index, show

### ShiftSwapController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Employee\ShiftSwapController.php
- Methods: __construct, index, create, store, accept, reject, cancel, showAcceptOpen, acceptOpen, export

### HRDashboardController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\HR\HRDashboardController.php
- Methods: __construct, index

### LeaveRequestController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Leave\LeaveRequestController.php
- Methods: __construct, index, create, store, show, destroy, approve, reject, cancel, export, workerLeaveBalance

### ManagerDashboardController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Manager\ManagerDashboardController.php
- Methods: __construct, index

### ShiftSwapApprovalController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Manager\ShiftSwapApprovalController.php
- Methods: __construct, index, show, approve, reject, execute, revert, export

### DepartmentController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Master\DepartmentController.php
- Methods: __construct, index, create, store, show, edit, update, destroy

### DepartmentDocumentTypeController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Master\DepartmentDocumentTypeController.php
- Methods: __construct, index, create, store, edit, update, show, destroy

### DocumentTypeController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Master\DocumentTypeController.php
- Methods: __construct, index, create, store, show, edit, update, destroy

### LeaveTypeController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Master\LeaveTypeController.php
- Methods: __construct, index, create, store, show, edit, update, destroy

### ShiftController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Master\ShiftController.php
- Methods: __construct, index, create, store, show, edit, update, destroy

### AttendanceReportController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Report\AttendanceReportController.php
- Methods: __construct, index

### ReportController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Report\ReportController.php
- Methods: __construct, attendance, leaves, workerDocuments, workers, exportAttendance, exportLeaves, exportWorkerDocuments

### RoleController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Role\RoleController.php
- Methods: __construct, index, create, store, show, edit, update, destroy

### WorkerShiftScheduleController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Schedule\WorkerShiftScheduleController.php
- Methods: __construct, index, show, create, store, edit, update, destroy, workerSchedule, bulkCreate, calendar

### ShiftOverrideController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\ShiftOverride\ShiftOverrideController.php
- Methods: __construct, index, create, store, bulkCreate, destroy

### UserController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\User\UserController.php
- Methods: __construct, index, create, store, show, edit, update, destroy

### WorkerController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Worker\WorkerController.php
- Methods: __construct, index, show, attendanceHistory, create, store, edit, update, destroy, resign, export, import, downloadTemplate

### WorkerOffDayController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\Worker\WorkerOffDayController.php
- Methods: __construct, index, storePattern, destroyPattern, checkDate, getRange

### WorkerDocumentController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\WorkerDocument\WorkerDocumentController.php
- Methods: __construct, index, create, store, documentTypesForWorker, show, verify, reject, download, workerDocuments, expired, expiring

### WorkerShiftController
- File: C:\laragon\www\SimpegRSHDI\app\Http\Controllers\WorkerShift\WorkerShiftController.php
- Methods: __construct, index, create, generate, generateStore, store, show, edit, update, destroy, workerShifts, calendarData
