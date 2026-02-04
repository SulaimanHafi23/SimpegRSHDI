# 🌱 Comprehensive Database Seeder Guide

## 📋 Daftar Seeder yang Tersedia

### 1. ComprehensiveDatabaseSeeder
Main seeder yang menjalankan semua seeder dalam urutan yang benar dengan data komprehensif untuk testing.

### 2. Enhanced Seeders (Data Lengkap untuk Testing)
- **EnhancedAttendanceSeeder**: Attendance dengan berbagai skenario (normal, late, early out, missing check in/out, absent)
- **EnhancedLeaveRequestSeeder**: Leave requests dengan berbagai status (pending, approved, rejected, cancelled)
- **EnhancedOvertimeRequestSeeder**: Overtime requests dengan berbagai kondisi
- **EnhancedShiftSwapRequestSeeder**: Shift swap requests dengan audit logs lengkap
- **EnhancedWorkerDocumentSeeder**: Worker documents dengan berbagai status verifikasi
- **EnhancedBusinessTripSeeder**: Business trips dengan berbagai tahapan perjalanan

---

## 🚀 Cara Menggunakan

### Option 1: Full Reset & Seed (Recommended untuk Testing)

```bash
# 1. Reset database (HATI-HATI: Akan menghapus semua data!)
php artisan migrate:fresh

# 2. Run comprehensive seeder
php artisan db:seed --class=ComprehensiveDatabaseSeeder
```

### Option 2: Seed Tanpa Reset (Menambah data ke existing)

```bash
# Run specific seeder
php artisan db:seed --class=EnhancedAttendanceSeeder
php artisan db:seed --class=EnhancedLeaveRequestSeeder
php artisan db:seed --class=EnhancedOvertimeRequestSeeder
php artisan db:seed --class=EnhancedShiftSwapRequestSeeder
php artisan db:seed --class=EnhancedWorkerDocumentSeeder
php artisan db:seed --class=EnhancedBusinessTripSeeder
```

### Option 3: Quick Test (Minimal Data)

```bash
# Run original seeders untuk data minimal
php artisan migrate:fresh
php artisan db:seed
```

---

## 📊 Data yang Di-generate

### Master Data
- ✅ 7 Religions (Islam, Kristen, Katolik, Hindu, Buddha, Konghucu, Lainnya)
- ✅ 2 Genders (Laki-laki, Perempuan)
- ✅ 8+ Departments (IT, HR, Finance, Marketing, Operations, Nursing, Emergency, Security)
- ✅ 5+ Locations (Kantor Pusat, Gedung A, B, C, Emergency Room)
- ✅ 4+ Shifts (Morning, Afternoon, Night, Full Day)
- ✅ 6+ Leave Types (Annual, Sick, Maternity, Paternity, Unpaid, Emergency)
- ✅ 10+ Document Types (KTP, SIM, NPWP, Ijazah, Sertifikat, dll)
- ✅ 10+ Holidays (Hari libur nasional)

### Users & Workers
- ✅ 1 Super Admin (admin@rshdi.com)
- ✅ 1 HR (hr@rshdi.com)
- ✅ 2+ Managers (manager.it@rshdi.com, manager.nursing@rshdi.com)
- ✅ 10+ Employees (employee1@rshdi.com, employee2@rshdi.com, ...)

**Default Password untuk semua user: `password`**

### Attendance (3 bulan terakhir)
- ✅ **Normal**: Hadir tepat waktu (65%)
- ✅ **Late**: Terlambat 5-120 menit (15%)
- ✅ **Early Out**: Pulang cepat 10-90 menit (10%)
- ✅ **Late & Early**: Kombinasi keduanya (5%)
- ✅ **Missing Check**: Lupa check in/out (4%)
- ✅ **Absent**: Tidak hadir (3%)
- ✅ Attendance Photos: Check in/out photos (75% records)

### Leave Requests
- ✅ **Pending**: 20% - Request yang masih menunggu approval
- ✅ **Approved**: 50% - Request yang sudah disetujui
- ✅ **Rejected**: 15% - Request yang ditolak dengan alasan
- ✅ **Cancelled**: 15% - Request yang dibatalkan

**Durasi**: 1-14 hari
**Range**: Past, present, dan future dates

### Overtime Requests
- ✅ **Pending**: 25% - Request overtime yang masih menunggu
- ✅ **Approved**: 50% - Overtime disetujui dengan multiplier (1.5x weekday, 2x weekend)
- ✅ **Rejected**: 15% - Overtime ditolak
- ✅ **Cancelled**: 10% - Overtime dibatalkan

**Durasi**: 1-8 jam
**Waktu**: Malam hari (17:00 - 22:00)

### Shift Swap Requests
- ✅ **Pending**: 20% - Menunggu acceptance dari target worker
- ✅ **Awaiting Approval**: 25% - Menunggu approval dari HR/Manager
- ✅ **Approved**: 30% - Swap disetujui
- ✅ **Rejected**: 15% - Swap ditolak dengan alasan
- ✅ **Cancelled**: 10% - Swap dibatalkan
- ✅ **Open Requests**: 30% dari total (tidak ada target worker spesifik)
- ✅ **Audit Logs**: Complete audit trail untuk setiap swap

### Worker Documents
- ✅ **Pending**: 25% - Dokumen menunggu verifikasi
- ✅ **Verified**: 60% - Dokumen sudah diverifikasi
- ✅ **Rejected**: 15% - Dokumen ditolak dengan alasan

**Jenis File**: PDF, JPG, PNG
**File Size**: 100KB - 5MB
**Expiry Dates**: Untuk dokumen yang perlu (KTP, SIM, dll)

### Business Trips
- ✅ **Pending**: 20% - Request perjalanan dinas menunggu approval
- ✅ **Approved**: 40% - Perjalanan disetujui
- ✅ **Rejected**: 15% - Request ditolak
- ✅ **On Trip**: 15% - Sedang dalam perjalanan
- ✅ **Completed**: 10% - Perjalanan selesai dengan laporan

**Destinasi**: Dalam dan luar negeri (Jakarta, Surabaya, Singapore, dll)
**Durasi**: 1-14 hari
**Budget**: 1.000.000 - 15.000.000

---

## 🎯 Skenario Testing yang Tercakup

### ✅ Attendance Management
- [x] Check in normal
- [x] Check in terlambat dengan berbagai durasi
- [x] Check out lebih awal
- [x] Lupa check in
- [x] Lupa check out
- [x] Absent (tidak ada record)
- [x] Attendance photos dengan GPS coordinates
- [x] Work hours calculation
- [x] Status categorization

### ✅ Leave Management
- [x] Leave request pending (future dates)
- [x] Leave request approved (various dates)
- [x] Leave request rejected dengan alasan
- [x] Leave request cancelled
- [x] Leave dengan attachment (30% kemungkinan)
- [x] Multiple leave types
- [x] Berbagai durasi cuti (1-14 hari)

### ✅ Overtime Management
- [x] Overtime request pending
- [x] Overtime approved dengan multiplier (1.5x/2x)
- [x] Overtime rejected dengan alasan
- [x] Overtime cancelled
- [x] Weekday vs weekend overtime
- [x] Berbagai durasi (1-8 jam)

### ✅ Shift Swap Management
- [x] Pending swap (waiting for target)
- [x] Awaiting approval (accepted by target)
- [x] Approved swap
- [x] Rejected swap dengan alasan
- [x] Cancelled swap
- [x] Open request (no specific target)
- [x] Cross-department swaps
- [x] Complete audit logs
- [x] Manager approval workflow

### ✅ Document Management
- [x] Document pending verification
- [x] Document verified
- [x] Document rejected dengan alasan
- [x] Multiple document types
- [x] Document dengan expiry date
- [x] Berbagai format file (PDF, JPG, PNG)
- [x] File size variations

### ✅ Business Trip Management
- [x] Trip request pending
- [x] Trip approved
- [x] Trip rejected dengan alasan
- [x] Trip on-going
- [x] Trip completed dengan report
- [x] Budget estimation vs actual
- [x] Berbagai destinasi
- [x] Multiple transportation types
- [x] Accommodation variations

---

## 🔐 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@rshdi.com | password |
| HR | hr@rshdi.com | password |
| Manager IT | manager.it@rshdi.com | password |
| Manager Nursing | manager.nursing@rshdi.com | password |
| Employee 1 | employee1@rshdi.com | password |
| Employee 2 | employee2@rshdi.com | password |
| Employee 3 | employee3@rshdi.com | password |

---

## 📈 Expected Results

Setelah run ComprehensiveDatabaseSeeder, anda akan memiliki:

```
📊 Summary Data:
┌─────────────────────────┬────────┐
│ Table                   │ Count  │
├─────────────────────────┼────────┤
│ Religions               │ 7      │
│ Genders                 │ 2      │
│ Departments             │ 8+     │
│ Locations               │ 5+     │
│ Shifts                  │ 4+     │
│ Leave Types             │ 6+     │
│ Document Types          │ 10+    │
│ Holidays                │ 10+    │
│ Roles                   │ 4      │
│ Permissions             │ 50+    │
│ Users                   │ 15+    │
│ Workers                 │ 15+    │
│ Worker Shifts           │ 90+    │
│ Attendances             │ 800+   │
│ Leave Requests          │ 50+    │
│ Overtime Requests       │ 40+    │
│ Worker Documents        │ 60+    │
│ Shift Swap Requests     │ 20+    │
│ Business Trips          │ 30+    │
│ Notifications           │ 100+   │
└─────────────────────────┴────────┘
```

---

## ⚠️ Important Notes

1. **BACKUP DATABASE DULU** sebelum run seeder dengan migrate:fresh
2. Seeder ini cocok untuk **DEVELOPMENT & TESTING** environment
3. **JANGAN** run di PRODUCTION tanpa review
4. File dummy tidak benar-benar ada di storage, hanya path reference
5. GPS coordinates menggunakan Jakarta area sebagai default
6. Dates di-generate secara random dalam range yang masuk akal

---

## 🔧 Troubleshooting

### Error: "No workers found"
```bash
# Run worker seeder dulu
php artisan db:seed --class=WorkerSeeder
```

### Error: "No shifts found"
```bash
# Run shift seeder dulu
php artisan db:seed --class=ShiftSeeder
```

### Error: Foreign key constraint
```bash
# Run comprehensive seeder yang sudah include proper order
php artisan db:seed --class=ComprehensiveDatabaseSeeder
```

### Ingin reset specific table saja
```bash
# Truncate specific table
php artisan tinker
>>> DB::table('attendances')->truncate();
>>> exit

# Lalu run specific seeder
php artisan db:seed --class=EnhancedAttendanceSeeder
```

---

## 📝 Customization

Jika ingin customize jumlah data atau persentase status, edit file seeder:

```php
// File: database/seeders/EnhancedAttendanceSeeder.php
$statuses = [
    'pending' => 20,      // Ubah persentase di sini
    'approved' => 50,
    'rejected' => 15,
    'cancelled' => 15,
];
```

---

## 🎉 Happy Testing!

Dengan seeder ini, anda memiliki data lengkap untuk test semua fitur aplikasi dengan berbagai skenario edge cases!
