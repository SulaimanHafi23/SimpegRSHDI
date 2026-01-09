# 🎯 Quick Reference: Permission System

## 📊 Permission Hierarchy

```
Super Admin (42 permissions)
    ├── ALL PERMISSIONS
    └── Can override all checks

HR (23 permissions)
    ├── Dashboard Admin ✅
    ├── Master Data (Full) ✅
    ├── Worker Management ✅
    ├── Attendance Management ✅
    ├── Leave/Overtime (Manage + Approve) ✅
    ├── Reports ✅
    └── User Management ✅

Manager (16 permissions)
    ├── Dashboard Admin ✅
    ├── Worker View ✅
    ├── Attendance View ✅
    ├── Approvals (Leave, Overtime, Shift Swap, Business Trip) ✅
    └── Reports ✅

Employee (15 permissions)
    ├── Dashboard Employee ✅
    ├── Personal Profile ✅
    ├── Check In/Out ✅
    ├── View Own Data ✅
    └── Submit Requests ✅
```

---

## 🚀 Quick Access Matrix

### Dashboard Access
| Role | Dashboard Route | Permission Required |
|------|----------------|---------------------|
| Super Admin | `/admin/dashboard` | `dashboard.admin` |
| HR | `/admin/dashboard` | `dashboard.admin` |
| Manager | `/admin/dashboard` | `dashboard.admin` |
| Employee | `/employee/dashboard` | `dashboard.employee` |

---

### Worker Management
| Action | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| View List | ✅ | ✅ | ✅ (view) | ❌ |
| Create | ✅ | ✅ | ❌ | ❌ |
| Edit | ✅ | ✅ | ❌ | ❌ |
| Delete | ✅ | ✅ | ❌ | ❌ |
| View Own Profile | ✅ | ✅ | ✅ | ✅ |

**Permissions**:
- Admin/HR: `worker.manage`
- Employee: `worker.view`

---

### Attendance
| Action | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| View All | ✅ | ✅ | ✅ | ❌ |
| Create/Edit | ✅ | ✅ | ❌ | ❌ |
| Check In/Out | ✅ | ❌ | ❌ | ✅ |
| View Own | ✅ | ✅ | ✅ | ✅ |

**Permissions**:
- Admin/HR: `attendance.manage`
- Manager: `attendance.manage` (view only)
- Employee: `attendance.checkin`, `attendance.view`

---

### Leave Requests
| Action | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| View All | ✅ | ✅ | ✅ | ❌ |
| Create/Edit/Delete | ✅ | ✅ | ❌ | ❌ |
| Approve/Reject | ✅ | ✅ | ✅ | ❌ |
| Submit Request | ✅ | ❌ | ❌ | ✅ |
| View Own | ✅ | ✅ | ✅ | ✅ |

**Permissions**:
- Admin/HR: `leave.manage`, `leave.approve`
- Manager: `leave.approve`, `leave.view`
- Employee: `leave.request`, `leave.view`

---

### Overtime Requests
| Action | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| View All | ✅ | ✅ | ✅ | ❌ |
| Create/Edit/Delete | ✅ | ✅ | ❌ | ❌ |
| Approve/Reject | ✅ | ✅ | ✅ | ❌ |
| Submit Request | ✅ | ❌ | ❌ | ✅ |
| View Own | ✅ | ✅ | ✅ | ✅ |

**Permissions**:
- Admin/HR: `overtime.manage`, `overtime.approve`
- Manager: `overtime.approve`, `overtime.view`
- Employee: `overtime.request`, `overtime.view`

---

### Shift Swap
| Action | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| View All | ✅ | ❌ | ✅ | ❌ |
| Create/Edit/Delete | ✅ | ❌ | ❌ | ❌ |
| Approve/Reject | ✅ | ❌ | ✅ | ❌ |
| Submit Request | ✅ | ❌ | ❌ | ✅ |
| View Own | ✅ | ❌ | ✅ | ✅ |

**Permissions**:
- Admin: `shift-swap.manage`, `shift-swap.approve`
- Manager: `shift-swap.approve`, `shift-swap.view`
- Employee: `shift-swap.request`, `shift-swap.view`

---

### Reports
| Action | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| View All Reports | ✅ | ✅ | ✅ | ❌ |
| Export Reports | ✅ | ✅ | ✅ | ❌ |
| View Personal Report | ✅ | ❌ | ❌ | ✅ |

**Permissions**:
- Admin/HR/Manager: `report.view`, `report.export`
- Employee: `report.personal`

---

### Master Data Configuration
| Module | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| Religion | ✅ | ✅ | ❌ | ❌ |
| Gender | ✅ | ✅ | ❌ | ❌ |
| Department | ✅ | ✅ | ✅ (view) | ❌ |
| Location | ✅ | ✅ | ❌ | ❌ |
| Shift | ✅ | ✅ | ✅ (view) | ❌ |
| Leave Type | ✅ | ✅ | ❌ | ❌ |
| Document Type | ✅ | ✅ | ❌ | ❌ |
| Holiday | ✅ | ✅ | ❌ | ❌ |

**Permissions**: `{module}.manage`

---

### Administration
| Module | Super Admin | HR | Manager | Employee |
|--------|-------------|-------|---------|----------|
| Roles & Permissions | ✅ | ❌ | ❌ | ❌ |
| User Management | ✅ | ✅ | ❌ | ❌ |

**Permissions**:
- `role.manage` - Super Admin only
- `user.manage` - Super Admin & HR

---

## 💡 Common Use Cases

### 1. Employee Submit Leave Request
```php
// Check permission
if (auth()->user()->can('leave.request')) {
    // Show form
}
```

### 2. Manager Approve Leave
```php
// Check permission
if (auth()->user()->can('leave.approve')) {
    // Show approve button
}
```

### 3. HR Add New Worker
```php
// Check permission
if (auth()->user()->can('worker.manage')) {
    // Show create form
}
```

### 4. View with Super Admin Bypass
```blade
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
    <button>Add Worker</button>
@endif
```

---

## 🔍 Testing Permissions

```bash
# Via Tinker
php artisan tinker

# Check user permissions
$user = User::find(1);
$user->getAllPermissions()->pluck('name');

# Check user roles
$user->getRoleNames();

# Test specific permission
$user->can('dashboard.admin');

# Assign role
$user->assignRole('Employee');

# Remove role
$user->removeRole('Employee');

# Sync roles (replace all)
$user->syncRoles(['HR']);
```

---

## 🛠️ Troubleshooting

### Permission not working?
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Need to re-seed?
```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan permission:cache-reset
```

### Check current permissions
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::all()->pluck('name');
>>> \Spatie\Permission\Models\Role::with('permissions')->get();
```

---

## 📝 Key Differences: Old vs New

| Aspect | Old System | New System |
|--------|------------|------------|
| **Format** | `create-workers` | `worker.manage` |
| **Granularity** | 4 permissions/module | 1-4 permissions/module |
| **Dashboard** | `dashboard.view` | `dashboard.admin` + `dashboard.employee` |
| **Employee Access** | No specific permissions | Dedicated `.view`, `.request` permissions |
| **Approval** | Mixed with manage | Dedicated `.approve` permissions |
| **Total Permissions** | ~84 | 42 (50% reduction) |

---

## 🎓 Best Practices

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

4. **Document custom permissions** in seeder with comments

5. **Test permissions** after each change

---

**Quick Command Reference**:
```bash
# Re-seed permissions
php artisan db:seed --class=RolePermissionSeeder

# Clear caches
php artisan permission:cache-reset && php artisan cache:clear

# Check permissions
php artisan tinker
>>> User::find(1)->getAllPermissions()->pluck('name');
```

---

**Version**: 2.0  
**Last Updated**: January 9, 2026
