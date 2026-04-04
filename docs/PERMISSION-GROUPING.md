# 📋 Permission Grouping Structure

## Pengelompokan Permission di Halaman Roles

Permissions di halaman roles telah dikelompokkan menjadi **7 kategori** untuk memudahkan pemahaman dan management:

---

## 🎨 Kategori Permission

### 1. 🏠 **Dashboard** (Purple)
**Icon**: `fas fa-tachometer-alt`  
**Color**: Purple  

**Deskripsi**: Akses ke halaman dashboard

**Permissions**:
- `dashboard.admin` - Dashboard untuk admin, HR, dan manager
- `dashboard.employee` - Dashboard untuk pegawai

---

### 2. 📋 **Master Data** (Blue)
**Icon**: `fas fa-database`  
**Color**: Blue  

**Deskripsi**: Konfigurasi dan pengaturan data master sistem

**Modules**:
- `religion.manage` - Agama
- `gender.manage` - Jenis Kelamin
- `department.manage` - Departemen
- `location.manage` - Lokasi
- `shift.manage` - Shift Kerja
- `leave-type.manage` - Tipe Cuti
- `document-type.manage` - Tipe Dokumen
- `department-document-type.manage` - Relasi Dept-Dokumen
- `holiday.manage` - Hari Libur

**Total**: 9 permissions

---

### 3. 👥 **Manajemen** (Green)
**Icon**: `fas fa-users-cog`  
**Color**: Green  

**Deskripsi**: Manajemen data pegawai, absensi, jadwal, dan dokumen

**Permissions**:
- `worker.manage` - Manajemen pegawai (CRUD)
- `attendance.manage` - Manajemen absensi
- `schedule.manage` - Manajemen jadwal kerja
- `worker-document.manage` - Manajemen dokumen pegawai

**Total**: 4 permissions

---

### 4. ✅ **Persetujuan** (Yellow)
**Icon**: `fas fa-check-double`  
**Color**: Yellow  

**Deskripsi**: Approval untuk berbagai jenis permohonan

**Modules & Actions**:
- **Leave (Cuti)**
  - `leave.manage` - Full management
  - `leave.approve` - Approve/reject
  
- **Shift Swap (Tukar Shift)**
  - `shift-swap.manage` - Full management
  - `shift-swap.approve` - Approve/reject
  
- **Business Trip (Perjalanan Dinas)**
  - `business-trip.manage` - Full management
  - `business-trip.approve` - Approve/reject

**Total**: 12 permissions

---

### 5. 👤 **Akses Pegawai** (Indigo)
**Icon**: `fas fa-user`  
**Color**: Indigo  

**Deskripsi**: Permission khusus untuk pegawai (personal access)

**Actions**:
- `.request` - Submit permohonan
- `.view` - Lihat data pribadi
- `.checkin` - Check in/out

**Permissions**:
- `worker.view` - Lihat profil sendiri
- `worker-document.view` - Lihat dokumen sendiri
- `attendance.checkin` - Check in/out
- `attendance.view` - Lihat absensi sendiri
- `schedule.view` - Lihat jadwal sendiri
- `leave.request` - Ajukan cuti
- `leave.view` - Lihat cuti sendiri
- `shift-swap.request` - Ajukan tukar shift
- `shift-swap.view` - Lihat tukar shift sendiri
- `business-trip.request` - Ajukan perjalanan dinas
- `business-trip.view` - Lihat perjalanan dinas sendiri

**Total**: 13 permissions

---

### 6. 📊 **Laporan** (Pink)
**Icon**: `fas fa-chart-bar`  
**Color**: Pink  

**Deskripsi**: Akses laporan dan export data

**Permissions**:
- `report.view` - Lihat semua laporan
- `report.export` - Export laporan
- `report.personal` - Lihat laporan pribadi (pegawai)

**Total**: 3 permissions

---

### 7. ⚙️ **Pengaturan** (Gray)
**Icon**: `fas fa-cog`  
**Color**: Gray  

**Deskripsi**: Pengaturan sistem dan administrasi

**Permissions**:
- `role.manage` - Manajemen roles
- `user.manage` - Manajemen users

**Total**: 2 permissions

---

## 📊 Total Permissions: 42

### Distribution by Category:
1. Dashboard: 2 permissions
2. Master Data: 9 permissions
3. Manajemen: 4 permissions
4. Persetujuan: 12 permissions
5. Akses Pegawai: 13 permissions
6. Laporan: 3 permissions
7. Pengaturan: 2 permissions

---

## 🎯 Visual Representation

```
┌─────────────────────────────────────────────────┐
│  🏠 Dashboard (2)                               │
│  ├─ dashboard.admin                             │
│  └─ dashboard.employee                          │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  📋 Master Data (9)                             │
│  ├─ religion.manage                             │
│  ├─ gender.manage                               │
│  ├─ department.manage                           │
│  ├─ location.manage                             │
│  ├─ shift.manage                                │
│  ├─ leave-type.manage                           │
│  ├─ document-type.manage                        │
│  ├─ department-document-type.manage             │
│  └─ holiday.manage                              │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  👥 Manajemen (4)                               │
│  ├─ worker.manage                               │
│  ├─ attendance.manage                           │
│  ├─ schedule.manage                             │
│  └─ worker-document.manage                      │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  ✅ Persetujuan (9)                             │
│  ├─ Leave                                       │
│  │  ├─ leave.manage                             │
│  │  └─ leave.approve                            │
│  ├─ Shift Swap                                  │
│  │  ├─ shift-swap.manage                        │
│  │  └─ shift-swap.approve                       │
│  └─ Business Trip                               │
│     ├─ business-trip.manage                     │
│     └─ business-trip.approve                    │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  👤 Akses Pegawai (11)                          │
│  ├─ worker.view                                 │
│  ├─ worker-document.view                        │
│  ├─ attendance.checkin                          │
│  ├─ attendance.view                             │
│  ├─ schedule.view                               │
│  ├─ leave.request                               │
│  ├─ leave.view                                  │
│  ├─ shift-swap.request                          │
│  ├─ shift-swap.view                             │
│  ├─ business-trip.request                       │
│  └─ business-trip.view                          │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  📊 Laporan (3)                                 │
│  ├─ report.view                                 │
│  ├─ report.export                               │
│  └─ report.personal                             │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  ⚙️ Pengaturan (2)                              │
│  ├─ role.manage                                 │
│  └─ user.manage                                 │
└─────────────────────────────────────────────────┘
```

---

## 🔍 Grouping Logic

### Algorithm:
```php
// 1. Dashboard - contains "dashboard"
if (str_contains($permName, 'dashboard'))

// 2. Master Data - specific modules
if (in_array($module, ['religion', 'gender', 'department', ...]))

// 3. Management - .manage for worker modules
if (str_contains($permName, 'worker.manage') || 
    str_contains($permName, 'attendance.manage') || ...)

// 4. Approval - approval modules with .approve or .manage
if (in_array($module, ['leave', 'shift-swap', 'business-trip']) && 
    (str_contains('.approve') || str_contains('.manage')))

// 5. Employee Access - .request, .view, .checkin actions
if (str_contains('.request') || 
    str_contains('.view') || 
    str_contains('.checkin'))

// 6. Report - contains "report"
if (str_contains($permName, 'report'))

// 7. Settings - role & user modules
if (str_contains('role') || str_contains('user'))
```

---

## 💡 Usage Benefits

### For Super Admin:
- ✅ Quick overview of all permission categories
- ✅ Easy to identify which permissions to assign
- ✅ Visual grouping reduces confusion

### For HR:
- ✅ Clear separation between management and approval
- ✅ Easy to see master data configuration access
- ✅ Understand employee access levels

### For Manager:
- ✅ Focus on approval permissions
- ✅ Clear view of team oversight capabilities
- ✅ Understand reporting access

### For Developers:
- ✅ Structured permission organization
- ✅ Easy to maintain and extend
- ✅ Consistent naming convention
- ✅ Self-documenting code

---

## 📱 UI Implementation

### Color Scheme:
- 🟣 Purple - Dashboard
- 🔵 Blue - Master Data
- 🟢 Green - Management
- 🟡 Yellow - Approval
- 🟣 Indigo - Employee Access
- 🔴 Pink - Reports
- ⚫ Gray - Settings

### Visual Elements:
- Bordered cards with category color
- Icons for each category
- Badge showing permission count
- Checkboxes in grid layout
- Responsive design (1-3 columns)

---

## 🔄 Maintenance

### Adding New Permissions:
1. Add to seeder: `database/seeders/RolePermissionSeeder.php`
2. Follow naming convention: `module.action`
3. Permission will auto-group based on name
4. Update this documentation

### Modifying Groups:
1. Edit grouping logic in views:
   - `resources/views/admin/settings/roles/create.blade.php`
   - `resources/views/admin/settings/roles/edit.blade.php`
   - `resources/views/admin/settings/roles/show.blade.php`
2. Test with all role types
3. Update documentation

---

**Last Updated**: January 9, 2026  
**Version**: 2.0
