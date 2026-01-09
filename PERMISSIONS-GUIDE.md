# 📋 Panduan Permissions & Roles

## Overview
Sistem SIMPEGRS HDI menggunakan **154 permissions** yang terorganisir berdasarkan modul dan aksi.

---

## 🎭 Roles & Access Levels

### 1. 👑 Super Admin
- **Total Permissions**: 154 (ALL)
- **Akses**: Full control atas semua fitur sistem
- **Fungsi**: System administrator, maintenance, konfigurasi penuh

### 2. 👔 HR (Human Resources)
- **Total Permissions**: 129
- **Akses**: Full management untuk data pegawai, master data, payroll, dan approval
- **Fungsi**: Mengelola kepegawaian, absensi, dokumen, gaji, approval

### 3. 👨‍💼 Manager
- **Total Permissions**: 39
- **Akses**: View data pegawai, approval requests, laporan
- **Fungsi**: Approve/reject leave, overtime, shift swap, business trip

### 4. 👤 Employee
- **Total Permissions**: 29
- **Akses**: Self-service untuk absensi, cuti, lembur, dokumen pribadi
- **Fungsi**: Check-in/out, request leave/overtime, view payroll

---

## 📦 Permissions by Module

### 🏠 Dashboard
```
dashboard.view          - View dashboard
dashboard.admin         - Admin dashboard
dashboard.hr            - HR dashboard
dashboard.manager       - Manager dashboard
dashboard.employee      - Employee dashboard
```

### 🗂️ Data Master

#### Religion
```
religion.view           - Lihat daftar agama
religion.create         - Tambah agama baru
religion.edit           - Edit data agama
religion.delete         - Hapus agama
religion.manage         - Full management
```

#### Gender
```
gender.view
gender.create
gender.edit
gender.delete
gender.manage
```

#### Department
```
department.view
department.create
department.edit
department.delete
department.manage
```

#### Location
```
location.view
location.create
location.edit
location.delete
location.manage
```

#### Shift
```
shift.view
shift.create
shift.edit
shift.delete
shift.manage
```

#### Leave Type
```
leave-type.view
leave-type.create
leave-type.edit
leave-type.delete
leave-type.manage
```

#### Document Type
```
document-type.view
document-type.create
document-type.edit
document-type.delete
document-type.manage
```

#### Department Document Type
```
department-document-type.view
department-document-type.create
department-document-type.edit
department-document-type.delete
department-document-type.manage
```

---

### 👥 Worker Management
```
worker.view             - Lihat daftar pegawai
worker.create           - Tambah pegawai baru
worker.edit             - Edit data pegawai
worker.delete           - Hapus pegawai
worker.resign           - Proses resign pegawai
worker.export           - Export data pegawai
worker.import           - Import data pegawai
worker.manage           - Full management
```

### ⏰ Attendance
```
attendance.view         - Lihat absensi sendiri
attendance.view-all     - Lihat semua absensi
attendance.create       - Create manual attendance
attendance.edit         - Edit attendance
attendance.delete       - Hapus attendance
attendance.checkin      - Check-in
attendance.checkout     - Check-out
attendance.export       - Export data absensi
attendance.manage       - Full management
```

### 📅 Schedule / Worker Shifts
```
schedule.view           - Lihat jadwal sendiri
schedule.view-all       - Lihat semua jadwal
schedule.create         - Buat jadwal baru
schedule.edit           - Edit jadwal
schedule.delete         - Hapus jadwal
schedule.override       - Override jadwal
schedule.manage         - Full management
```

### 📄 Worker Documents
```
worker-document.view            - Lihat dokumen sendiri
worker-document.view-all        - Lihat semua dokumen
worker-document.upload          - Upload dokumen
worker-document.verify          - Verifikasi dokumen
worker-document.reject          - Reject dokumen
worker-document.delete          - Hapus dokumen
worker-document.download        - Download dokumen
worker-document.manage          - Full management
```

### 💰 Payroll
```
payroll.view            - Lihat payroll sendiri
payroll.view-all        - Lihat semua payroll
payroll.create          - Buat payroll
payroll.edit            - Edit payroll
payroll.delete          - Hapus payroll
payroll.process         - Process payroll
payroll.export          - Export payroll
payroll.manage          - Full management
```

---

### ✅ Approvals

#### Leave (Cuti)
```
leave.view              - Lihat cuti sendiri
leave.view-all          - Lihat semua pengajuan cuti
leave.request           - Ajukan cuti
leave.approve           - Approve cuti
leave.reject            - Reject cuti
leave.cancel            - Cancel cuti
leave.export            - Export data cuti
leave.manage            - Full management
```

#### Overtime (Lembur)
```
overtime.view
overtime.view-all
overtime.request
overtime.approve
overtime.reject
overtime.cancel
overtime.export
overtime.manage
```

#### Shift Swap (Tukar Shift)
```
shift-swap.view
shift-swap.view-all
shift-swap.request
shift-swap.approve
shift-swap.reject
shift-swap.execute
shift-swap.cancel
shift-swap.manage
```

#### Business Trip (Dinas)
```
business-trip.view
business-trip.view-all
business-trip.request
business-trip.approve
business-trip.reject
business-trip.cancel
business-trip.export
business-trip.manage
```

---

### 📊 Reports
```
report.view                 - View reports
report.attendance           - Laporan absensi
report.leave                - Laporan cuti
report.overtime             - Laporan lembur
report.worker-document      - Laporan dokumen
report.payroll              - Laporan payroll
report.export               - Export laporan
```

---

### ⚙️ Settings

#### Holidays
```
holiday.view
holiday.create
holiday.edit
holiday.delete
holiday.manage
```

#### Roles
```
role.view
role.create
role.edit
role.delete
role.assign-permission      - Assign permissions ke role
role.manage
```

#### Users
```
user.view
user.create
user.edit
user.delete
user.activate
user.deactivate
user.reset-password
user.manage
```

#### Salary Components
```
salary-component.view
salary-component.create
salary-component.edit
salary-component.delete
salary-component.manage
```

---

### 👤 Employee Self-Service
```
profile.view                - Lihat profile
profile.edit                - Edit profile
profile.change-password     - Ubah password
notification.view           - Lihat notifikasi
notification.mark-read      - Mark notifikasi as read
calendar.view               - Lihat calendar
```

---

## 🔧 Cara Menggunakan

### 1. Menambah Permission ke Role
```php
$role = Role::findByName('Manager');
$role->givePermissionTo('payroll.view');
```

### 2. Menghapus Permission dari Role
```php
$role = Role::findByName('Manager');
$role->revokePermissionTo('payroll.view');
```

### 3. Check Permission di Controller
```php
// Method 1: Middleware
$this->middleware('permission:worker.manage');

// Method 2: Manual check
if (!auth()->user()->can('worker.manage')) {
    abort(403);
}

// Method 3: Helper method
$this->authorizePermission('worker.manage');
```

### 4. Check Permission di Blade
```blade
@can('worker.create')
    <button>Tambah Pegawai</button>
@endcan

@if(auth()->user()->can('worker.edit'))
    <a href="#">Edit</a>
@endif
```

### 5. Mengelola Permissions via Seeder
Edit file `database/seeders/RolePermissionSeeder.php` untuk menambah/edit permissions dan assignment.

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## 📝 Best Practices

1. **Gunakan format konsisten**: `module.action`
2. **Jangan hapus permission** yang sudah digunakan, buat yang baru
3. **Update seeder** ketika menambah fitur baru
4. **Test permissions** setelah perubahan
5. **Clear cache** setelah update: `php artisan permission:cache-reset`

---

## 🔄 Update Permissions

Jika ada fitur baru, ikuti langkah ini:

1. Tambahkan permissions di `RolePermissionSeeder.php`
2. Assign ke roles yang sesuai
3. Jalankan seeder:
   ```bash
   php artisan permission:cache-reset
   php artisan db:seed --class=RolePermissionSeeder
   ```
4. Update dokumentasi ini

---

## 📞 Support

Untuk pertanyaan atau penambahan permissions, hubungi tim development.

**Last Updated**: January 8, 2026
