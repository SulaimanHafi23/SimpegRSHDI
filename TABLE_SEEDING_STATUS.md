# Database Seeding Status Report

## 📊 Ringkasan Table

### ✅ TABLE YANG SUDAH TERISI DATA

| Table | Records | Seeder | Status |
|-------|---------|--------|--------|
| genders | 2 | GenderSeeder | ✅ Lengkap |
| religions | 6 | ReligionSeeder | ✅ Lengkap |
| departments | 13 | DepartmentSeeder | ✅ Lengkap |
| locations | 7 | LocationSeeder | ✅ Lengkap |
| shifts | 4 | ShiftSeeder | ✅ Lengkap |
| leave_types | 3 | LeaveTypeSeeder | ✅ Lengkap |
| document_types | 13 | DocumentTypeSeeder | ✅ Lengkap |
| salary_components | 8+ | SalaryComponentSeeder | ✅ Lengkap |
| holidays | 12+ | HolidaySeeder | ✅ Lengkap |
| roles | 4 | RolePermissionSeeder | ✅ Lengkap |
| permissions | 48 | RolePermissionSeeder | ✅ Lengkap |
| users | 11 | SuperAdminSeeder + UserSeeder | ✅ Lengkap |
| workers | 11 | WorkerSeeder | ✅ Lengkap |
| worker_shifts | 11 | WorkerShiftSeeder | ✅ Lengkap |
| shift_overrides | 20-30 | WorkerShiftSeeder | ✅ Lengkap |
| leave_requests | 20-40 | LeaveRequestSeeder | ✅ Lengkap |

---

### ❌ TABLE YANG KOSONG & ALASAN

#### 1. **attendances** (Kehadiran Karyawan)
**Status**: KOSONG (0 records)

**Alasan**: 
- Seeder (`AttendanceSeeder`) dibuat tapi tidak dijalankan
- **Schema Mismatch**: Seeder menggunakan kolom `date`, tapi schema table menggunakan `attendance_date`
- Juga membutuhkan kolom geo-location: `check_in_latitude`, `check_in_longitude`, `check_out_latitude`, `check_out_longitude`, dan `distance_check_in`

**Schema yang dibutuhkan**:
```php
$table->date('attendance_date');
$table->time('check_in');
$table->time('check_out')->nullable();
$table->decimal('check_in_latitude', 10, 8);
$table->decimal('check_in_longitude', 11, 8);
// ... dan fields lainnya
```

---

#### 2. **overtime_requests** (Pengajuan Lembur)
**Status**: KOSONG (0 records)

**Alasan**:
- Seeder (`OvertimeRequestSeeder`) dibuat tapi tidak dijalankan
- **Schema Mismatch**: Seeder menggunakan field `date`, `start_time`, `end_time`, `notes`
- Schema table sebenarnya:
  - `overtime_date` (bukan `date`)
  - Tidak ada kolom `notes`
  - Ada field: `total_hours`, `reason`, `status`, `approved_at`, `rejection_reason`

---

#### 3. **business_trips** (Perjalanan Dinas)
**Status**: KOSONG (0 records)

**Alasan**:
- Seeder (`BusinessTripSeeder`) dibuat tapi tidak dijalankan
- **Kolom Tidak Exist**: Seeder menggunakan field `transportation` dan `accommodation`
- Tapi field-field ini belum ada di schema table saat migration dijalankan
- Field yang ada di schema: `destination`, `purpose`, `start_date`, `end_date`, `estimated_cost`, `status`, `notes`, `approved_by`, `approved_at`, `rejection_reason`

**Solusi**: Perlu migration baru untuk menambah kolom `transportation` dan `accommodation`

---

#### 4. **worker_documents** (Dokumen Karyawan)
**Status**: KOSONG (0 records)

**Alasan**:
- Seeder (`WorkerDocumentSeeder`) dibuat tapi tidak dijalankan
- **Import Error**: Menggunakan class `App\Models\Master\DepartmentDocumentType` yang tidak exist
- Model yang benar lokasi-nya berbeda atau belum ada

---

#### 5. **shift_swap_requests** (Permintaan Tukar Shift)
**Status**: KOSONG (0 records)

**Alasan**:
- Seeder (`ShiftSwapRequestSeeder`) dibuat tapi tidak dijalankan
- **Schema Mismatch**: Seeder awal mencoba mengakses `requester_shift_schedule_id` dan `target_shift_schedule_id`
- Tapi tabel tidak punya field tersebut

**Schema yang sebenarnya** memungkinkan:
```php
$table->foreignUuid('requester_worker_id');
$table->foreignUuid('target_worker_id');
$table->date('requested_date');
$table->enum('status', ['pending', 'approved', 'rejected', 'cancelled']);
$table->enum('target_response', ['pending', 'accepted', 'declined']);
```

---

#### 6. **payrolls** (Penggajian)
**Status**: KOSONG (0 records)

**Alasan**:
- Seeder (`PayrollSeeder`) dibuat tapi tidak dijalankan
- **Kolom Tidak Exist**: Seeder menggunakan field `paid_at` dan `paid_date`
- Schema table tidak memiliki field ini
- Field yang ada: `period_start`, `period_end`, `basic_salary`, `gross_salary`, `total_deductions`, `net_salary`, `status`

---

#### 7. **payroll_details** (Detail Penggajian)
**Status**: KOSONG (0 records)

**Alasan**:
- Bergantung pada `PayrollSeeder` yang tidak berjalan
- Tidak ada parent records di `payrolls` table

---

#### 8. **notifications** (Notifikasi)
**Status**: KOSONG (0 records)

**Alasan**:
- Seeder (`NotificationSeeder`) dibuat tapi tidak dijalankan
- Tidak critical untuk testing functional features

---

#### 9. **attendances** (Kehadiran) + Terkait
**Status**: KOSONG

Tabel yang related tapi kosong:
- `attendance_photos` (foto check-in/out)

---

## 🔧 Solusi untuk Mengisi Setiap Table

### Untuk Attendance (Kehadiran)
```php
// Fix kolom di seeder
$table->date('attendance_date');  // bukan 'date'
$table->dateTime('check_in');
$table->dateTime('check_out')->nullable();
// Tambah geo-location fields yang wajib
```

### Untuk Overtime Requests
```php
// Sesuaikan field nama
'date' → 'overtime_date'
'notes' → hapus (kolom tidak ada)
Tambah 'reason' untuk penjelasan lembur
```

### Untuk Business Trips
**Opsi 1**: Tambah kolom ke schema via migration baru
```bash
php artisan make:migration add_transportation_accommodation_to_business_trips
```

**Opsi 2**: Update seeder untuk sesuai schema yang ada

### Untuk Worker Documents
- Fix import: `App\Models\Master\DepartmentDocumentType` → cari model yang benar
- Atau buat model missing

### Untuk Shift Swap Requests
- Update seeder untuk menggunakan field schema yang benar
- Tidak perlu `requester_shift_schedule_id` dan `target_shift_schedule_id`

### Untuk Payrolls
- Remove `paid_at` field dari seeder
- Atau tambah kolom ke schema
- Fokus pada: `period_start`, `period_end`, `basic_salary`, `net_salary`

---

## 📈 Prioritas Pengisian Data

**Priority 1 (Essential untuk testing):**
- ✅ Leave Requests (SUDAH ADA)
- ❌ Attendances (perlu perbaikan schema)
- ❌ Overtime Requests (perlu perbaikan seeder)

**Priority 2 (Important untuk demo):**
- ❌ Business Trips (perlu migration kolom)
- ❌ Shift Swap Requests (perlu perbaikan seeder)
- ❌ Worker Documents (perlu fix import)

**Priority 3 (Optional):**
- ❌ Payrolls (tidak critical untuk MVP)
- ❌ Notifications (optional, bisa diisi nanti)

---

## 📝 Rekomendasi Aksi

1. **Immediate**: Seeder yang sudah berfungsi sudah berjalan. Data untuk leave management siap.

2. **For Attendance Data**:
   - Perbaiki `AttendanceSeeder.php` untuk use `attendance_date`
   - Tambah geo-location data (latitude/longitude)
   - Run `php artisan db:seed --class=AttendanceSeeder`

3. **For Business Trips**:
   - Buat migration untuk add `transportation` dan `accommodation` columns
   - Update BusinessTripSeeder
   - Run migration dan seeder

4. **For Overtime & Shift Swap**:
   - Perbaiki field names di seeder sesuai schema
   - Run individual seeders

5. **Testing untuk User**:
   - Gunakan data yang sudah ada: Leave Requests, Worker Shifts, Master Data
   - Sufficient untuk demo leave management system

---

**Report Generated**: January 26, 2026  
**Status**: Database partially seeded, ready for basic testing
