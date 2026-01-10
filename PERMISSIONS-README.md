# 🔐 SIMPEGRS Permission System v2.0

> **Comprehensive Role-Based Access Control (RBAC) System**  
> Sistem permission berbasis modul yang granular, fleksibel, dan mudah di-maintain

---

## 📑 Table of Contents

1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Architecture](#architecture)
4. [Roles & Permissions](#roles--permissions)
5. [Implementation](#implementation)
6. [Documentation](#documentation)
7. [Migration Guide](#migration-guide)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

### What's New in v2.0

✅ **Module-based permissions** - Dari 84 permission menjadi 42 (50% reduction)  
✅ **Granular action types** - `.manage`, `.view`, `.approve`, `.request`, `.checkin`  
✅ **Separate dashboards** - `dashboard.admin` vs `dashboard.employee`  
✅ **Better employee access** - Dedicated permissions untuk personal data  
✅ **Clear approval flow** - Dedicated `.approve` permissions untuk manager  
✅ **Comprehensive documentation** - Lengkap dengan diagram dan quick reference  

### Key Features

- 🎭 **4 Role Types**: Super Admin, HR, Manager, Employee
- 🔒 **42 Permissions**: Covering 10+ modules
- 📊 **2 Dashboard Types**: Admin & Employee
- 🔄 **Flexible Actions**: 5 action types per module
- 📝 **Well Documented**: With Mermaid diagrams & examples
- 🧪 **Production Ready**: Tested & optimized

---

## 🚀 Quick Start

### 1. Run the Seeder

```bash
# Fresh install
php artisan db:seed --class=RolePermissionSeeder

# Clear cache
php artisan permission:cache-reset
php artisan cache:clear
```

### 2. Assign Roles to Users

```bash
php artisan tinker

# Assign Super Admin
$user = User::find(1);
$user->assignRole('Super Admin');

# Assign HR
$user = User::find(2);
$user->assignRole('HR');

# Assign Manager
$user = User::find(3);
$user->assignRole('Manager');

# Assign Employee
$user = User::find(4);
$user->assignRole('Employee');
```

### 3. Test Permissions

```bash
# Check user permissions
$user->getAllPermissions()->pluck('name');

# Check specific permission
$user->can('dashboard.admin');
$user->can('worker.manage');
$user->can('leave.request');
```

---

## 🏗️ Architecture

### Permission Structure

```
{module}.{action}
```

**Examples:**
- `worker.manage` - Full CRUD on workers
- `attendance.view` - View own attendance
- `leave.approve` - Approve leave requests
- `leave.request` - Submit leave request

### Action Types

| Action | Description | Used By |
|--------|-------------|---------|
| `.manage` | Full CRUD access | Super Admin, HR |
| `.approve` | Approve/reject requests | HR, Manager |
| `.request` | Submit requests | Employee |
| `.view` | View own data | Employee, Manager (team) |
| `.checkin` | Check in/out | Employee |

### Visual Architecture

![Permission Architecture](docs/permission-architecture.mermaid)

**View the diagram**: Open `docs/permission-architecture.mermaid` in VS Code with Mermaid preview

---

## 👥 Roles & Permissions

### 1️⃣ Super Admin (42 permissions)

**Purpose**: System administrator dengan akses penuh

**Key Capabilities**:
- ✅ ALL permissions
- ✅ Role & permission management
- ✅ Override all permission checks
- ✅ Admin dashboard access

**Dashboard**: `/admin/dashboard` (`dashboard.admin`)

---

### 2️⃣ HR (23 permissions)

**Purpose**: Mengelola seluruh siklus kepegawaian

**Key Capabilities**:
- ✅ Master data configuration (9 modules)
- ✅ Worker full CRUD
- ✅ Attendance & schedule management
- ✅ Leave & overtime (manage + approve)
- ✅ User management (not roles)
- ✅ Reports (view + export)

**Dashboard**: `/admin/dashboard` (`dashboard.admin`)

**Permissions**:
```
dashboard.admin
religion.manage, gender.manage, department.manage, location.manage
shift.manage, leave-type.manage, document-type.manage
department-document-type.manage, holiday.manage
worker.manage, attendance.manage, schedule.manage, worker-document.manage
leave.manage, leave.approve
overtime.manage, overtime.approve
business-trip.manage, business-trip.approve
report.view, report.export
user.manage
```

---

### 3️⃣ Manager (16 permissions)

**Purpose**: Supervisi tim dan approval requests

**Key Capabilities**:
- ✅ View workers & attendance
- ✅ Manage schedules
- ✅ Approve all requests (leave, overtime, shift swap, business trip)
- ✅ View & export reports
- ❌ Cannot add/edit/delete workers
- ❌ Cannot configure master data
- ❌ Cannot manage users

**Dashboard**: `/admin/dashboard` (`dashboard.admin`)

**Permissions**:
```
dashboard.admin
worker.manage (view only)
attendance.manage (view only)
schedule.manage
department.manage (view only), shift.manage (view only)
leave.approve, leave.view
overtime.approve, overtime.view
shift-swap.approve, shift-swap.view
business-trip.approve, business-trip.view
report.view, report.export
```

---

### 4️⃣ Employee (15 permissions)

**Purpose**: Pegawai dengan akses data pribadi

**Key Capabilities**:
- ✅ View personal profile & documents
- ✅ Check in/out attendance
- ✅ View own schedule
- ✅ Submit requests (leave, overtime, shift swap, business trip)
- ✅ View own request history
- ✅ View personal reports
- ❌ Cannot view other employees' data
- ❌ Cannot approve requests
- ❌ Cannot access master data

**Dashboard**: `/employee/dashboard` (`dashboard.employee`)

**Permissions**:
```
dashboard.employee
worker.view, worker-document.view
attendance.checkin, attendance.view
schedule.view
leave.request, leave.view
overtime.request, overtime.view
shift-swap.request, shift-swap.view
business-trip.request, business-trip.view
report.personal
```

---

## 💻 Implementation

### In Controllers

```php
// Single permission
public function __construct()
{
    $this->middleware('permission:dashboard.admin');
}

// Multiple permissions (OR)
public function __construct()
{
    $this->middleware('permission:leave.approve|leave.manage');
}

// In method
public function store(Request $request)
{
    if (!auth()->user()->can('worker.manage')) {
        abort(403, 'Unauthorized action.');
    }
    
    // Your code here
}
```

### In Blade Views

```blade
{{-- Simple permission check --}}
@can('worker.manage')
    <button>Add Worker</button>
@endcan

{{-- With Super Admin bypass (RECOMMENDED) --}}
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
    <button>Add Worker</button>
@endif

{{-- Multiple permissions (OR) --}}
@if(auth()->user()->can('leave.approve') || auth()->user()->can('leave.manage'))
    <button>Approve Leave</button>
@endif

{{-- Role-based display --}}
@role('Employee')
    <a href="{{ route('employee.dashboard') }}">Dashboard Pegawai</a>
@endrole

@role('Super Admin|HR|Manager')
    <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
@endrole
```

### In Routes

```php
// Single permission
Route::get('/workers', [WorkerController::class, 'index'])
    ->middleware('permission:worker.manage');

// Multiple permissions
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('permission:report.view|report.personal');

// Role-based group
Route::middleware('role:Super Admin|HR')->group(function () {
    Route::resource('workers', WorkerController::class);
});
```

---

## 📚 Documentation

### Available Documents

1. **[PERMISSIONS.md](PERMISSIONS.md)** - Comprehensive permission documentation
   - Complete permission list with descriptions
   - Role definitions & responsibilities
   - Usage examples & best practices
   - Troubleshooting guide

2. **[PERMISSIONS-QUICK-REFERENCE.md](PERMISSIONS-QUICK-REFERENCE.md)** - Quick reference guide
   - Permission hierarchy visualization
   - Quick access matrix
   - Common use cases
   - Command reference

3. **[docs/permission-architecture.mermaid](docs/permission-architecture.mermaid)** - Visual architecture diagram
   - Role hierarchy
   - Permission distribution
   - Access flow

4. **[docs/permission-actions.mermaid](docs/permission-actions.mermaid)** - Action type diagram
   - Action types explanation
   - Module examples
   - Who can use what

5. **[docs/dashboard-access.mermaid](docs/dashboard-access.mermaid)** - Dashboard access flow
   - Dashboard routing
   - Features per dashboard
   - Permission requirements

### View Diagrams

```bash
# Open in VS Code with Mermaid preview extension
code docs/permission-architecture.mermaid
code docs/permission-actions.mermaid
code docs/dashboard-access.mermaid
```

---

## 🔄 Migration Guide

### From Old Permission System

#### Old Format → New Format

| Old Permission | New Permission | Notes |
|---------------|----------------|-------|
| `create-workers` | `worker.manage` | Combined into single manage |
| `view-workers` | `worker.manage` or `worker.view` | Context-dependent |
| `edit-workers` | `worker.manage` | Included in manage |
| `delete-workers` | `worker.manage` | Included in manage |
| `approve-leave` | `leave.approve` | Dedicated approval permission |
| `view-own-attendance` | `attendance.view` | Employee context |

#### Update Views

```blade
<!-- Old -->
@can('create-workers')
@can('view-workers')
@can('edit-workers')
@can('delete-workers')

<!-- New -->
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
```

#### Update Controllers

```php
// Old
$this->middleware('permission:create-workers');

// New
$this->middleware('permission:worker.manage');
```

### Step-by-Step Migration

1. **Backup Database**
   ```bash
   php artisan db:backup  # If you have backup package
   # Or manual mysqldump
   ```

2. **Run New Seeder**
   ```bash
   php artisan db:seed --class=RolePermissionSeeder
   ```

3. **Clear Cache**
   ```bash
   php artisan permission:cache-reset
   php artisan cache:clear
   ```

4. **Update Views** (use find & replace)
   - Search: `@can('create-workers')`
   - Replace: `@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))`

5. **Update Controllers**
   - Search: `permission:create-workers`
   - Replace: `permission:worker.manage`

6. **Test Each Module**
   - Test CRUD operations
   - Test approval flows
   - Test employee access

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Permission Not Working

**Symptom**: User has permission but gets 403

**Solution**:
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

**Check**:
```php
$user = User::find(1);
$user->getAllPermissions()->pluck('name');
$user->can('dashboard.admin');
```

---

#### 2. Employee Can't Access Dashboard

**Symptom**: Employee gets redirected or 403 on dashboard

**Solution**:
```bash
# Check if employee has role
php artisan tinker
>>> $user = User::find(4);
>>> $user->assignRole('Employee');
>>> $user->can('dashboard.employee');  // Should return true
```

**Check Middleware**:
```php
// In EmployeeDashboardController
public function __construct()
{
    $this->middleware('permission:dashboard.employee');
}
```

---

#### 3. Super Admin Gets 403

**Symptom**: Super Admin blocked on some pages

**Check View Code**:
```blade
<!-- BAD: No Super Admin bypass -->
@can('worker.manage')

<!-- GOOD: With Super Admin bypass -->
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
```

**Check Middleware**:
```php
// In CheckPermission middleware
if (auth()->user()->hasRole('Super Admin')) {
    return $next($request);
}
```

---

#### 4. Need to Re-seed Permissions

```bash
# Fresh seeding
php artisan db:seed --class=RolePermissionSeeder

# Clear cache
php artisan permission:cache-reset
php artisan cache:clear

# Verify
php artisan tinker
>>> \Spatie\Permission\Models\Permission::count();  // Should be 42
>>> \Spatie\Permission\Models\Role::count();        // Should be 4
```

---

### Debug Commands

```bash
# Check all permissions
php artisan tinker
>>> \Spatie\Permission\Models\Permission::all()->pluck('name');

# Check all roles with permissions
>>> \Spatie\Permission\Models\Role::with('permissions')->get();

# Check specific user
>>> $user = User::find(1);
>>> $user->getRoleNames();
>>> $user->getAllPermissions()->pluck('name');

# Check if user has permission
>>> $user->can('dashboard.admin');

# Check if user has role
>>> $user->hasRole('Super Admin');
```

---

## 📊 Statistics

### Permission Summary

- **Total Permissions**: 42
- **Total Roles**: 4
- **Reduction from old system**: 50%

### Permission Distribution

| Role | Permissions | Access Level |
|------|------------|--------------|
| Super Admin | 42 (ALL) | 🔴 Full System |
| HR | 23 | 🔵 Management & Config |
| Manager | 16 | 🟢 Oversight & Approval |
| Employee | 15 | 🟡 Personal Access |

### Module Coverage

- 📊 Dashboard: 2 permissions
- 👤 Worker: 2 permissions
- 📋 Attendance: 3 permissions
- 📅 Schedule: 2 permissions
- 📄 Documents: 2 permissions
- 🌴 Leave: 4 permissions
- ⏰ Overtime: 4 permissions
- 🔄 Shift Swap: 4 permissions
- ✈️ Business Trip: 4 permissions
- 📊 Reports: 3 permissions
- ⚙️ Master Data: 9 permissions
- 🔧 Administration: 2 permissions

---

## 🎓 Best Practices

### ✅ DO:

1. **Always use Super Admin bypass in views**
   ```blade
   @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('permission'))
   ```

2. **Use middleware in controllers**
   ```php
   $this->middleware('permission:dashboard.admin');
   ```

3. **Clear cache after changes**
   ```bash
   php artisan permission:cache-reset
   ```

4. **Test permissions after each change**

5. **Document custom permissions** in seeder with comments

### ❌ DON'T:

1. ❌ Don't create permissions outside seeder
2. ❌ Don't hardcode permission names in views
3. ❌ Don't forget Super Admin bypass
4. ❌ Don't mix old permission format with new
5. ❌ Don't skip cache clearing after updates

---

## 📞 Support

### Getting Help

1. **Check Documentation**
   - Read [PERMISSIONS.md](PERMISSIONS.md) for detailed info
   - Check [PERMISSIONS-QUICK-REFERENCE.md](PERMISSIONS-QUICK-REFERENCE.md) for quick answers

2. **View Diagrams**
   - Open Mermaid files in `docs/` folder
   - Visualize permission flow

3. **Debug**
   - Use tinker commands above
   - Check Laravel logs: `storage/logs/laravel.log`
   - Check middleware logs for permission checks

---

## 📝 Changelog

### Version 2.0 (January 9, 2026)

**Major Changes**:
- ✨ Module-based permission system (42 permissions)
- ✨ Separate dashboards for admin and employee
- ✨ Granular action types (.manage, .approve, .request, .view, .checkin)
- ✨ Employee-specific permissions for personal data
- ✨ Manager approval permissions separated from manage
- 📚 Comprehensive documentation with Mermaid diagrams
- 🔧 Updated all controllers and views
- ✅ Production-ready and tested

**Breaking Changes**:
- Old permission format no longer supported
- Views need updating for Super Admin bypass
- Controllers need permission middleware updates

---

## 📄 License

SIMPEGRS Permission System v2.0  
© 2026 RSUD Haji Darjlan Ismail  
Internal Use Only

---

**Version**: 2.0  
**Last Updated**: January 9, 2026  
**Authors**: Development Team
