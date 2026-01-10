# 🔐 SIMPEGRS Permission System Documentation

## 📋 Table of Contents
1. [Overview](#overview)
2. [Permission Structure](#permission-structure)
3. [Role Definitions](#role-definitions)
4. [Permission List](#permission-list)
5. [Usage Examples](#usage-examples)
6. [Implementation Guide](#implementation-guide)

---

## Overview

SIMPEGRS menggunakan **Spatie Laravel Permission** dengan struktur permission berbasis modul yang granular dan fleksibel.

### Key Concepts:
- **Module-based**: Permission dikelompokkan per modul (worker, attendance, leave, dll)
- **Granular Actions**: Setiap modul memiliki action spesifik (manage, view, approve, request)
- **Role-based**: 4 role utama dengan tanggung jawab berbeda
- **Hierarchical**: Super Admin > HR > Manager > Employee

---

## Permission Structure

### Naming Convention:
```
{module}.{action}
```

### Actions:
| Action | Description | Access Level |
|--------|-------------|--------------|
| `.manage` | Full CRUD access | Admin/HR |
| `.view` | Read-only access | Employee (own data) |
| `.approve` | Can approve/reject requests | Manager/HR |
| `.request` | Can submit requests | Employee |
| `.checkin` | Can perform check-in/out | Employee |
| `.export` | Can export data | Admin/Manager |

---

## Role Definitions

### 1️⃣ Super Admin
**Purpose**: Sistem administrator dengan akses penuh

**Responsibilities**:
- Konfigurasi sistem lengkap
- Manajemen roles dan users
- Akses ke semua data dan fitur
- Override semua permission checks

**Total Permissions**: ALL (68 permissions)

**Dashboard Access**: `dashboard.admin`

---

### 2️⃣ HR (Human Resources)
**Purpose**: Mengelola seluruh siklus kepegawaian

**Responsibilities**:
- ✅ Manajemen data pegawai (CRUD)
- ✅ Konfigurasi master data (shift, cuti, dokumen)
- ✅ Manajemen absensi dan jadwal
- ✅ Approval cuti, lembur, perjalanan dinas
- ✅ Lihat dan export laporan
- ✅ Manajemen user accounts
- ❌ TIDAK bisa manajemen roles

**Total Permissions**: 24 permissions

**Dashboard Access**: `dashboard.admin`

**Key Permissions**:
```php
'dashboard.admin',
'worker.manage',
'attendance.manage',
'schedule.manage',
'leave.manage', 'leave.approve',
'overtime.manage', 'overtime.approve',
'report.view', 'report.export',
'user.manage'
```

---

### 3️⃣ Manager
**Purpose**: Supervisi tim dan approval requests

**Responsibilities**:
- ✅ Lihat data pegawai di tim
- ✅ Approve cuti, lembur, shift swap, perjalanan dinas
- ✅ Monitor absensi tim
- ✅ Lihat dan export laporan
- ✅ Manajemen jadwal tim
- ❌ TIDAK bisa tambah/edit/hapus pegawai
- ❌ TIDAK bisa konfigurasi master data
- ❌ TIDAK bisa manajemen users

**Total Permissions**: 16 permissions

**Dashboard Access**: `dashboard.admin`

**Key Permissions**:
```php
'dashboard.admin',
'worker.manage',      // View only
'attendance.manage',  // View only
'schedule.manage',    // Can adjust
'leave.approve', 'leave.view',
'overtime.approve', 'overtime.view',
'shift-swap.approve', 'shift-swap.view',
'business-trip.approve', 'business-trip.view',
'report.view', 'report.export'
```

---

### 4️⃣ Employee (Pegawai)
**Purpose**: Pegawai biasa dengan akses data pribadi

**Responsibilities**:
- ✅ Lihat profil dan data pribadi
- ✅ Check-in/out absensi
- ✅ Lihat jadwal kerja sendiri
- ✅ Submit request: cuti, lembur, shift swap, perjalanan dinas
- ✅ Lihat history request sendiri
- ✅ Lihat dokumen pribadi
- ✅ Lihat laporan pribadi
- ❌ TIDAK bisa lihat data pegawai lain
- ❌ TIDAK bisa approve request
- ❌ TIDAK bisa akses master data

**Total Permissions**: 14 permissions

**Dashboard Access**: `dashboard.employee`

**Key Permissions**:
```php
'dashboard.employee',
'worker.view',              // Own profile only
'attendance.checkin',       // Can check in/out
'attendance.view',          // Own records only
'schedule.view',            // Own schedule only
'leave.request', 'leave.view',
'overtime.request', 'overtime.view',
'shift-swap.request', 'shift-swap.view',
'business-trip.request', 'business-trip.view',
'report.personal'
```

---

## Permission List

### 🏠 Dashboard (2 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `dashboard.admin` | ✅ | ✅ | ✅ | ❌ | Admin dashboard dengan statistik global |
| `dashboard.employee` | ✅ | ❌ | ❌ | ✅ | Employee dashboard dengan data pribadi |

---

### 👤 Worker Management (2 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `worker.manage` | ✅ | ✅ | ✅ (view) | ❌ | Full CRUD workers |
| `worker.view` | ✅ | ✅ | ✅ | ✅ | View own profile only (Employee) |

---

### 📋 Attendance (3 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `attendance.manage` | ✅ | ✅ | ✅ (view) | ❌ | Manage all attendance |
| `attendance.view` | ✅ | ✅ | ✅ | ✅ | View own attendance (Employee) |
| `attendance.checkin` | ✅ | ❌ | ❌ | ✅ | Can check in/out |

---

### 📅 Schedule (2 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `schedule.manage` | ✅ | ✅ | ✅ | ❌ | Manage work schedules |
| `schedule.view` | ✅ | ✅ | ✅ | ✅ | View own schedule (Employee) |

---

### 📄 Worker Documents (2 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `worker-document.manage` | ✅ | ✅ | ❌ | ❌ | Manage all documents |
| `worker-document.view` | ✅ | ✅ | ❌ | ✅ | View own documents (Employee) |

---

### 🌴 Leave (4 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `leave.manage` | ✅ | ✅ | ❌ | ❌ | Full CRUD leave requests |
| `leave.approve` | ✅ | ✅ | ✅ | ❌ | Approve/reject leaves |
| `leave.request` | ✅ | ❌ | ❌ | ✅ | Submit leave requests |
| `leave.view` | ✅ | ✅ | ✅ | ✅ | View own leaves (Employee) |

---

### ⏰ Overtime (4 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `overtime.manage` | ✅ | ✅ | ❌ | ❌ | Full CRUD overtime requests |
| `overtime.approve` | ✅ | ✅ | ✅ | ❌ | Approve/reject overtimes |
| `overtime.request` | ✅ | ❌ | ❌ | ✅ | Submit overtime requests |
| `overtime.view` | ✅ | ✅ | ✅ | ✅ | View own overtimes (Employee) |

---

### 🔄 Shift Swap (4 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `shift-swap.manage` | ✅ | ❌ | ❌ | ❌ | Full CRUD shift swaps |
| `shift-swap.approve` | ✅ | ❌ | ✅ | ❌ | Approve/reject shift swaps |
| `shift-swap.request` | ✅ | ❌ | ❌ | ✅ | Submit shift swap requests |
| `shift-swap.view` | ✅ | ❌ | ✅ | ✅ | View own shift swaps (Employee) |

---

### ✈️ Business Trip (4 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `business-trip.manage` | ✅ | ✅ | ❌ | ❌ | Full CRUD business trips |
| `business-trip.approve` | ✅ | ✅ | ✅ | ❌ | Approve/reject business trips |
| `business-trip.request` | ✅ | ❌ | ❌ | ✅ | Submit business trip requests |
| `business-trip.view` | ✅ | ✅ | ✅ | ✅ | View own business trips (Employee) |

---

### 📊 Reports (3 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `report.view` | ✅ | ✅ | ✅ | ❌ | View all reports |
| `report.export` | ✅ | ✅ | ✅ | ❌ | Export reports |
| `report.personal` | ✅ | ❌ | ❌ | ✅ | View personal reports only |

---

### ⚙️ Master Data (9 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `religion.manage` | ✅ | ✅ | ❌ | ❌ | Manage religion master |
| `gender.manage` | ✅ | ✅ | ❌ | ❌ | Manage gender master |
| `department.manage` | ✅ | ✅ | ✅ (view) | ❌ | Manage departments |
| `location.manage` | ✅ | ✅ | ❌ | ❌ | Manage locations |
| `shift.manage` | ✅ | ✅ | ✅ (view) | ❌ | Manage shifts |
| `leave-type.manage` | ✅ | ✅ | ❌ | ❌ | Manage leave types |
| `document-type.manage` | ✅ | ✅ | ❌ | ❌ | Manage document types |
| `department-document-type.manage` | ✅ | ✅ | ❌ | ❌ | Link dept & doc types |
| `holiday.manage` | ✅ | ✅ | ❌ | ❌ | Manage holidays |

---

### 🔧 Administration (2 permissions)
| Permission | Super Admin | HR | Manager | Employee | Description |
|------------|-------------|-------|---------|----------|-------------|
| `role.manage` | ✅ | ❌ | ❌ | ❌ | Manage roles & permissions |
| `user.manage` | ✅ | ✅ | ❌ | ❌ | Manage user accounts |

---

## Usage Examples

### In Controllers

#### Check Single Permission
```php
public function __construct()
{
    $this->middleware('permission:dashboard.admin');
}
```

#### Check Multiple Permissions (OR)
```php
public function __construct()
{
    $this->middleware('permission:leave.approve|leave.manage');
}
```

#### Check Role + Permission
```php
if (auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage')) {
    // Allow action
}
```

---

### In Blade Views

#### Simple Permission Check
```blade
@can('worker.manage')
    <button>Tambah Pegawai</button>
@endcan
```

#### With Super Admin Bypass
```blade
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
    <button>Tambah Pegawai</button>
@endif
```

#### Multiple Permissions (OR)
```blade
@if(auth()->user()->can('leave.approve') || auth()->user()->can('leave.manage'))
    <button>Approve Cuti</button>
@endif
```

#### Role-based Display
```blade
@role('Employee')
    <a href="{{ route('employee.dashboard') }}">Dashboard Pegawai</a>
@endrole

@role('Super Admin|HR|Manager')
    <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
@endrole
```

---

### In Routes

#### Single Permission
```php
Route::get('/workers', [WorkerController::class, 'index'])
    ->middleware('permission:worker.manage');
```

#### Multiple Permissions
```php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('permission:report.view|report.personal');
```

#### Role-based
```php
Route::middleware('role:Super Admin|HR')->group(function () {
    Route::resource('workers', WorkerController::class);
});
```

---

## Implementation Guide

### 1. Run Seeder
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Clear Cache
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### 3. Assign Role to User
```php
// Via Tinker
$user = User::find(1);
$user->assignRole('Super Admin');

// Via Code
$user->syncRoles(['HR']);
```

### 4. Check Permissions
```php
// Check if user has permission
auth()->user()->can('worker.manage');

// Check if user has role
auth()->user()->hasRole('Super Admin');

// Get all permissions
auth()->user()->getAllPermissions();

// Get all roles
auth()->user()->getRoleNames();
```

---

## Best Practices

### ✅ DO:
- Always use Super Admin bypass in views: `auth()->user()->hasRole('Super Admin') || auth()->user()->can('...')`
- Use descriptive permission names following convention
- Document custom permissions
- Test permissions after changes
- Clear cache after permission updates

### ❌ DON'T:
- Don't create permissions outside seeder
- Don't hardcode permission names
- Don't forget Super Admin bypass
- Don't mix old permission format with new
- Don't skip cache clearing

---

## Migration from Old System

### Old Format → New Format

| Old | New | Notes |
|-----|-----|-------|
| `create-workers` | `worker.manage` | Single manage permission |
| `view-workers` | `worker.manage` or `worker.view` | Context-dependent |
| `edit-workers` | `worker.manage` | Included in manage |
| `delete-workers` | `worker.manage` | Included in manage |
| `approve-leave` | `leave.approve` | Dedicated approval |
| `view-own-data` | `{module}.view` | Employee context |

### Update Views
```blade
<!-- Old -->
@can('create-workers')
@can('view-workers')
@can('edit-workers')

<!-- New -->
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
```

---

## Troubleshooting

### Problem: Permission not working
**Solution**:
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Problem: 403 Access Denied
**Check**:
1. User has correct role: `$user->getRoleNames()`
2. Role has permission: `Role::findByName('HR')->permissions->pluck('name')`
3. Super Admin bypass in view
4. CheckPermission middleware active

### Problem: Employee can't access dashboard
**Solution**:
```php
// Ensure employee has role
$user->assignRole('Employee');

// Check dashboard.employee permission
$user->can('dashboard.employee'); // Should be true
```

---

## Summary

✅ **68 Total Permissions** dengan 4 kategori aksi:
- `.manage` - Full CRUD (Admin/HR)
- `.view` - View own data (Employee)
- `.approve` - Approval authority (Manager)
- `.request` - Submit requests (Employee)

✅ **4 Roles** dengan hierarki jelas:
- Super Admin (ALL)
- HR (24 permissions)
- Manager (16 permissions)
- Employee (14 permissions)

✅ **Granular & Flexible** - Mudah maintain dan extend

✅ **Production-Ready** - Tested dan documented

---

**Last Updated**: January 9, 2026
**Version**: 2.0 (Module-based Permission System)
