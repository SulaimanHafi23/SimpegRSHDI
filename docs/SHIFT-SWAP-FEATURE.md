# Shift Swap Feature Documentation

## Overview
Fitur Shift Swap memungkinkan pekerja untuk menukar shift dengan rekan kerja lain dengan sistem approval berbasis departemen dan manager. Sistem ini dirancang untuk rumah sakit RSUD Haji Darlan Ismail yang memerlukan kebijakan konservatif untuk operasional 6 bulan pertama.

## Table of Contents
- [Business Rules](#business-rules)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Configuration](#configuration)
- [User Flows](#user-flows)
- [Testing](#testing)
- [Audit & Logging](#audit--logging)

---

## Business Rules

### 1. Lead Time Requirements
**Tujuan**: Memastikan persiapan yang cukup untuk pergantian shift

- **Regular Departments**: Minimal 48 jam sebelum shift dimulai
- **Critical Departments**: Minimal 72 jam sebelum shift dimulai
  - IGD (Emergency Department)
  - ICU (Intensive Care Unit)
  - Satpam (Security)

**Implementasi**: `validateLeadTime()` di `ShiftSwapService.php`

### 2. Rest Period Validation
**Tujuan**: Memastikan waktu istirahat yang cukup untuk kesehatan pekerja

- **Minimum**: 12 jam istirahat antara shift
- **Validasi**: Before & after shift yang akan ditukar
- **Data Source**: Attendance records + WorkerShift schedule

**Implementasi**: `validateRestPeriod()` + `checkWorkerRestPeriod()` di `ShiftSwapService.php`

### 3. Double Shift Prevention
**Tujuan**: Mencegah pekerja bekerja dua kali dalam satu hari

- **Check**: Validasi tidak ada shift lain pada tanggal yang sama
- **Data Source**: WorkerShift (active) + Attendance records

**Implementasi**: `validateDoubleShift()` di `ShiftSwapService.php`

### 4. Minimum Staffing Level
**Tujuan**: Memastikan jumlah pekerja per shift tidak turun di bawah threshold

- **Threshold**: 75% dari total scheduled workers
- **Calculation**: `(total_scheduled - pending_swaps - this_swap) / total_scheduled >= 75%`
- **Scope**: Per shift, per date

**Implementasi**: `validateMinimumStaffing()` di `ShiftSwapService.php`

### 5. Department-Based Approval
**Tujuan**: Kontrol manajerial untuk cross-department swaps

- **Same Department**: Tidak perlu approval manager (peer-to-peer)
- **Cross-Department**: Memerlukan approval manager
- **Manager Notification**: Auto-notify managers saat swap awaiting approval

**Implementasi**: `validateRoleMatch()` di `ShiftSwapService.php`

---

## Architecture

### Design Pattern
```
┌─────────────────┐
│   Controller    │ ← HTTP Requests
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  FormRequest    │ ← Validation
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│    Service      │ ← Business Logic + Validations
└────────┬────────┘
         │
         ├─────────────────┬─────────────────┬─────────────────┐
         ▼                 ▼                 ▼                 ▼
    ┌────────┐      ┌───────────┐    ┌──────────┐    ┌────────────┐
    │ Model  │      │ DTO       │    │ Audit    │    │Notification│
    └────────┘      └───────────┘    └──────────┘    └────────────┘
```

### Key Components

#### 1. Models
- **ShiftSwapRequest**: Main entity untuk swap requests
- **ShiftSwapAuditLog**: Audit trail untuk semua perubahan
- **Worker**: Pekerja yang terlibat dalam swap
- **WorkerShift**: Jadwal shift pekerja
- **Shift**: Master data shift

#### 2. Controllers
- **Employee/ShiftSwapController**: Employee-facing endpoints
  - `index()` - List swaps untuk authenticated worker
  - `store()` - Buat swap request
  - `accept()` - Accept swap (target worker)
  - `reject()` - Reject swap (target worker)
  - `cancel()` - Cancel swap (requester)

- **Manager/ShiftSwapApprovalController**: Manager-facing endpoints
  - `index()` - List pending approvals
  - `show()` - Detail swap dengan audit trail
  - `approve()` - Approve swap (manager)
  - `reject()` - Reject swap (manager)
  - `execute()` - Execute approved swap

#### 3. Services
- **ShiftSwapService**: Core business logic
  - `createRequest()` - Create + validate swap
  - `acceptRequest()` - Target accepts
  - `rejectRequest()` - Target rejects
  - `approveByManager()` - Manager approves
  - `rejectByManager()` - Manager rejects
  - `cancelRequest()` - Requester cancels
  - `executeSwap()` - Execute actual shift swap
  - `listForWorker()` - List swaps for worker
  - `listPendingApprovalsForManager()` - List for manager

#### 4. Notifications
- **ShiftSwapNotification**: Multi-channel notifications
  - Database notifications (in-app)
  - Email notifications
  - Context-aware messages berdasarkan action
  - Auto-routing ke relevant pages

---

## Database Schema

### shift_swap_requests
```sql
id                          UUID PRIMARY KEY
requester_id                UUID FK → workers
target_worker_id            UUID FK → workers (nullable)
requester_shift_id          UUID FK → worker_shifts
target_shift_id             UUID FK → worker_shifts (nullable)
status                      ENUM (pending, accepted, rejected, cancelled, 
                                  awaiting_approval, approved, executed)
requires_manager_approval   BOOLEAN
manager_id                  UUID FK → users (nullable)
manager_approved_at         TIMESTAMP (nullable)
reason                      TEXT (nullable)
metadata                    JSON (nullable)
requested_at                TIMESTAMP (nullable)
expires_at                  TIMESTAMP (nullable)
executed_by                 UUID FK → users (nullable)
executed_at                 TIMESTAMP (nullable)
created_at                  TIMESTAMP
updated_at                  TIMESTAMP
deleted_at                  TIMESTAMP (nullable, soft delete)

INDEXES:
- requester_id
- target_worker_id
- requester_shift_id
- status
```

### shift_swap_audit_logs
```sql
id                      UUID PRIMARY KEY
shift_swap_request_id   UUID FK → shift_swap_requests (cascade)
user_id                 UUID FK → users (nullable, set null)
action                  VARCHAR (created, accepted, rejected, cancelled, 
                               awaiting_approval, approved, 
                               rejected_by_manager, executed)
old_status              VARCHAR (nullable)
new_status              VARCHAR
notes                   TEXT (nullable)
metadata                JSON (nullable)
ip_address              VARCHAR (nullable)
user_agent              VARCHAR (nullable)
created_at              TIMESTAMP
updated_at              TIMESTAMP

INDEXES:
- (shift_swap_request_id, created_at)
- user_id
- action
```

---

## API Endpoints

### Employee Routes (Prefix: `/employee/shift-swaps`)

#### List Swap Requests
```
GET /employee/shift-swaps
Auth: Required (Employee)
Returns: List of swaps where user is requester or target
```

#### Create Swap Request
```
POST /employee/shift-swaps
Auth: Required (Employee)
Body:
{
  "requester_shift_id": "uuid",
  "target_worker_id": "uuid" (optional),
  "target_shift_id": "uuid" (optional),
  "reason": "string" (optional, max 1000),
  "expires_at": "date" (optional)
}
Validations:
- All business rules applied
Returns: Redirect with success/error message
```

#### Accept Swap
```
POST /employee/shift-swaps/{id}/accept
Auth: Required (Employee, must be target worker)
Returns: Redirect with success/error message
Notes: 
- If cross-department → status: awaiting_approval
- If same-department → status: accepted
```

#### Reject Swap
```
POST /employee/shift-swaps/{id}/reject
Auth: Required (Employee, must be target worker)
Body:
{
  "reason": "string" (optional)
}
Returns: Redirect with success/error message
```

#### Cancel Swap
```
POST /employee/shift-swaps/{id}/cancel
Auth: Required (Employee, must be requester)
Returns: Redirect with success/error message
Allowed if: status != executed && status != cancelled
```

### Manager Routes (Prefix: `/manager/shift-swap-approvals`)

#### List Pending Approvals
```
GET /manager/shift-swap-approvals
Auth: Required (Manager|HR|Super Admin)
Returns: List of swaps awaiting manager approval in manager's department
```

#### Show Swap Detail
```
GET /manager/shift-swap-approvals/{id}
Auth: Required (Manager|HR|Super Admin)
Returns: Detail view dengan audit trail
```

#### Approve Swap
```
POST /manager/shift-swap-approvals/{id}/approve
Auth: Required (Manager|HR|Super Admin)
Body:
{
  "notes": "string" (optional, max 500)
}
Returns: Redirect with success/error message
Allowed if: status == awaiting_approval
```

#### Reject Swap
```
POST /manager/shift-swap-approvals/{id}/reject
Auth: Required (Manager|HR|Super Admin)
Body:
{
  "reason": "string" (required, max 500)
}
Returns: Redirect with success/error message
Allowed if: status == awaiting_approval
```

#### Execute Swap
```
POST /manager/shift-swap-approvals/{id}/execute
Auth: Required (Manager|HR|Super Admin)
Returns: Redirect with success/error message
Allowed if: status == approved || status == accepted
Action: Swaps shift_id between requester and target in worker_shifts table
```

---

## Configuration

### File: `config/attendance.php`

```php
'shift_swap' => [
    // Minimum rest period between shifts (hours)
    'min_rest_period_hours' => env('SHIFT_SWAP_MIN_REST_HOURS', 12),

    // Lead time required for swap request (hours)
    'lead_time_hours' => env('SHIFT_SWAP_LEAD_TIME_HOURS', 48),

    // Lead time for critical roles (hours)
    'critical_lead_time_hours' => env('SHIFT_SWAP_CRITICAL_LEAD_TIME_HOURS', 72),

    // Critical departments that require longer lead time
    'critical_departments' => ['IGD', 'ICU', 'Satpam', 'Emergency'],

    // Minimum staffing level per shift (percentage)
    'min_staffing_percentage' => env('SHIFT_SWAP_MIN_STAFFING_PCT', 75),

    // Swap request expiration (hours)
    'request_expiration_hours' => env('SHIFT_SWAP_EXPIRATION_HOURS', 72),
],
```

### Environment Variables (Optional Overrides)
```env
SHIFT_SWAP_MIN_REST_HOURS=12
SHIFT_SWAP_LEAD_TIME_HOURS=48
SHIFT_SWAP_CRITICAL_LEAD_TIME_HOURS=72
SHIFT_SWAP_MIN_STAFFING_PCT=75
SHIFT_SWAP_EXPIRATION_HOURS=72
```

---

## User Flows

### Flow 1: Same-Department Swap (No Manager Approval)
```
1. Employee A (Dept X) → Create swap request → Target: Employee B (Dept X)
2. Employee B → Receives notification
3. Employee B → Accept
4. Status: accepted (ready to execute)
5. Manager/HR → Execute swap
6. Both employees receive notification
```

### Flow 2: Cross-Department Swap (Requires Manager Approval)
```
1. Employee A (Dept X) → Create swap request → Target: Employee B (Dept Y)
2. Employee B → Receives notification
3. Employee B → Accept
4. Status: awaiting_approval
5. Manager → Receives notification
6. Manager → Review → Approve
7. Status: approved (ready to execute)
8. Manager/HR → Execute swap
9. Both employees + manager receive notification
```

### Flow 3: Rejection by Target
```
1. Employee A → Create swap request → Target: Employee B
2. Employee B → Receives notification
3. Employee B → Reject (with reason)
4. Status: rejected
5. Employee A receives notification
```

### Flow 4: Cancellation by Requester
```
1. Employee A → Create swap request
2. Before acceptance/execution
3. Employee A → Cancel
4. Status: cancelled
5. Target + Manager (if involved) receive notification
```

---

## Testing

### Unit Tests (10 tests) - `tests/Unit/ShiftSwap/ShiftSwapServiceTest.php`

1. **it_validates_lead_time_requirement**
   - Tests: Lead time < 48h should fail
   
2. **it_allows_swap_with_sufficient_lead_time**
   - Tests: Lead time >= 48h should pass

3. **it_requires_manager_approval_for_cross_department_swaps**
   - Tests: requires_manager_approval = true for cross-dept

4. **it_does_not_require_manager_approval_for_same_department_swaps**
   - Tests: requires_manager_approval = false for same-dept

5. **it_validates_double_shift_prevention**
   - Tests: Cannot create swap if worker has another shift same day

6. **it_creates_audit_log_on_swap_creation**
   - Tests: Audit log entry created with action='created'

7. **target_worker_can_accept_swap**
   - Tests: Target acceptance updates status to 'accepted'

8. **target_worker_can_reject_swap**
   - Tests: Target rejection updates status to 'rejected'

9. **requester_can_cancel_swap**
   - Tests: Requester can cancel before execution

10. **manager_can_approve_cross_department_swap**
    - Tests: Manager approval updates status to 'approved'

### Feature Tests (7 tests) - `tests/Feature/ShiftSwap/ShiftSwapWorkflowTest.php`

1. **employee_can_create_swap_request**
   - Full HTTP test: POST request creates swap

2. **target_worker_can_accept_swap_request**
   - Full HTTP test: Target acceptance flow

3. **manager_can_approve_cross_department_swap**
   - Full HTTP test: Manager approval flow

4. **manager_can_execute_approved_swap**
   - Full HTTP test: Execution swaps shift_id values

5. **requester_can_cancel_pending_swap**
   - Full HTTP test: Cancellation flow

6. **target_worker_can_reject_swap**
   - Full HTTP test: Rejection flow

7. **audit_logs_are_created_for_all_actions**
   - Integration test: Verifies audit trail completeness

### Running Tests
```bash
# Run all shift swap tests
php artisan test --filter=ShiftSwap

# Run unit tests only
php artisan test --filter=ShiftSwapServiceTest

# Run feature tests only
php artisan test --filter=ShiftSwapWorkflowTest

# Run with coverage
php artisan test --filter=ShiftSwap --coverage
```

---

## Audit & Logging

### Audit Trail System

Setiap perubahan status pada swap request dicatat di tabel `shift_swap_audit_logs`:

**Tracked Information**:
- Action yang dilakukan
- User yang melakukan action
- Old status & new status
- Notes/reason
- Metadata (additional context)
- IP address
- User agent
- Timestamp

**Audit Actions**:
- `created` - Swap request dibuat
- `accepted` - Target menerima
- `rejected` - Target menolak
- `cancelled` - Requester membatalkan
- `awaiting_approval` - Status berubah awaiting approval
- `approved_by_manager` - Manager menyetujui
- `rejected_by_manager` - Manager menolak
- `executed` - Swap dieksekusi

### Logging (Laravel Log)

**Success Operations**:
```php
Log::info('Shift swap request created', [
    'swap_id' => $swap->id,
    'requester_id' => $requester->id,
    'target_worker_id' => $targetWorker?->id,
]);
```

**Validation Failures**:
```php
Log::warning('Shift swap validation failed', [
    'requester_id' => $worker->id,
    'validation' => 'lead_time',
    'required_hours' => 48,
]);
```

**Errors**:
```php
Log::error('Failed to execute shift swap', [
    'swap_id' => $swapId,
    'error' => $e->getMessage(),
]);
```

---

## Notifications

### Channels
- **Database**: In-app notifications (stored in `notifications` table)
- **Email**: Email notifications via queue

### Notification Triggers

| Action | Recipient(s) | Message |
|--------|-------------|---------|
| Created | Target Worker | "Permintaan tukar shift baru dari {requester}" |
| Accepted | Requester | "{target} telah menerima permintaan" |
| Awaiting Approval | Manager(s) | "Permintaan cross-department memerlukan persetujuan" |
| Approved | Requester + Target | "Manager menyetujui permintaan" |
| Rejected (by target) | Requester | "{target} menolak permintaan" |
| Rejected (by manager) | Requester + Target | "Manager menolak permintaan" |
| Cancelled | Target + Manager | "{requester} membatalkan permintaan" |
| Executed | Requester + Target | "Pertukaran shift berhasil dieksekusi" |

### Notification Class: `ShiftSwapNotification`

**Methods**:
- `via()` - Returns ['database', 'mail']
- `toMail()` - Email message with action button
- `toArray()` - Database notification data
- `getTitle()` - Context-aware title
- `getMessage()` - Context-aware message
- `getUrl()` - Route to relevant page

---

## Security Considerations

### Authorization
- ✅ Route middleware: `auth`, `role:Employee|Manager|HR|Super Admin`
- ✅ Service-level checks: Worker ownership verification
- ✅ Status-based action restrictions

### Data Validation
- ✅ FormRequest validation layer
- ✅ Service-level business rule validation
- ✅ Database constraints (foreign keys, unique, nullable)

### Audit Trail
- ✅ Complete audit log for compliance
- ✅ IP address & user agent tracking
- ✅ Soft deletes for data retention

---

## Troubleshooting

### Common Issues

#### 1. Lead Time Validation Fails
**Symptom**: Error "Swap request harus diajukan minimal X jam sebelum shift"
**Solution**: 
- Check shift's `effective_from` date
- Ensure lead time >= config value (48h regular, 72h critical)
- Verify department in critical_departments list

#### 2. Double Shift Error
**Symptom**: Error "Double shift tidak diperbolehkan"
**Solution**:
- Check WorkerShift table for existing shifts on same date
- Check Attendance table for existing attendance records
- Verify shift's `effective_from` is unique per worker per day

#### 3. Minimum Staffing Error
**Symptom**: Error "Akan menurunkan staffing di bawah X%"
**Solution**:
- Increase minimum staffing percentage in config
- Ensure sufficient workers scheduled for the shift
- Consider rejecting or delaying the swap

#### 4. Notification Not Sent
**Symptom**: User doesn't receive notification
**Solution**:
- Check worker has associated user account
- Verify queue is running (`php artisan queue:work`)
- Check `failed_jobs` table for failures
- Verify email configuration in `.env`

---

## Maintenance

### Database Cleanup

**Clean up expired swap requests**:
```php
ShiftSwapRequest::where('status', 'pending')
    ->where('expires_at', '<', now())
    ->update(['status' => 'cancelled']);
```

**Archive old audit logs** (older than 1 year):
```php
ShiftSwapAuditLog::where('created_at', '<', now()->subYear())
    ->delete();
```

### Monitoring

**Key Metrics**:
- Total swap requests per month
- Approval rate (accepted / total)
- Average time to approval
- Cross-department vs same-department ratio
- Rejection reasons analysis

**Query Examples**:
```php
// Approval rate
$total = ShiftSwapRequest::count();
$accepted = ShiftSwapRequest::whereIn('status', ['accepted', 'executed'])->count();
$rate = ($accepted / $total) * 100;

// Average time to approval
ShiftSwapRequest::whereNotNull('manager_approved_at')
    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, requested_at, manager_approved_at)) as avg_hours')
    ->first();
```

---

## Future Enhancements

### Potential Features
1. **Auto-matching algorithm** - Suggest suitable swap partners
2. **Shift preference system** - Workers can mark preferred shifts
3. **Bulk swap operations** - Manager can approve multiple swaps at once
4. **Mobile notifications** - Push notifications via Firebase
5. **Shift marketplace** - Open marketplace for shift offers
6. **Recurring swaps** - Template for regular swap patterns
7. **Integration with attendance** - Auto-execute based on attendance
8. **Analytics dashboard** - Swap patterns and insights
9. **Shift bidding system** - Workers can bid for preferred shifts
10. **WebSocket real-time updates** - Live updates without refresh

---

## Support & Contact

Untuk pertanyaan atau dukungan teknis:
- **Developer**: [Your Contact]
- **Documentation**: `/docs/shift-swap`
- **Issue Tracking**: [Your Issue Tracker]

---

**Version**: 1.0.0  
**Last Updated**: January 2, 2026  
**Author**: SIMPEG Development Team
