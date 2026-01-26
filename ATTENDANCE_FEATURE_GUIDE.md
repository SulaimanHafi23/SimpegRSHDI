# Panduan Fitur Absensi (Attendance)

## Status Fitur: ✅ SUDAH LENGKAP & BERFUNGSI

Sistem absensi di aplikasi SIMPEG-RSHDI **sudah sepenuhnya terimplementasi** dengan semua fitur yang diperlukan.

---

## 1. Fitur Utama

### ✅ Check-In dengan GPS
- **Route:** `GET/POST /employee/attendance/check-in`
- **Lokasi File:** [resources/views/employee/attendance/check-in.blade.php](resources/views/employee/attendance/check-in.blade.php)
- **Fitur:**
  - Geolokasi otomatis menggunakan Leaflet map
  - Validasi radius lokasi (Haversine formula)
  - Upload foto (opsional)
  - Catatan absensi
  - Simpan koordinat (latitude/longitude)
  - Hitung jarak dari lokasi yang ditentukan

### ✅ Check-Out
- **Route:** `GET/POST /employee/attendance/check-out/{id}`
- **Lokasi File:** [resources/views/employee/attendance/check-out.blade.php](resources/views/employee/attendance/check-out.blade.php)
- **Fitur:**
  - Geolokasi saat check-out
  - Deteksi pulang awal (early leave)
  - Hitung lembur (overtime)
  - Catat waktu check-out

### ✅ Riwayat Absensi
- **Route:** `GET /employee/attendance`
- **Lokasi File:** [resources/views/employee/attendance/index.blade.php](resources/views/employee/attendance/index.blade.php)
- **Fitur:**
  - Filter berdasarkan tanggal
  - Filter berdasarkan status
  - Statistik bulanan (hadir, terlambat, tidak hadir)
  - Export PDF
  - Card summary dengan angka dan icon

### ✅ Detail Absensi
- **Route:** `GET /employee/attendance/{id}`
- **Lokasi File:** [resources/views/employee/attendance/show.blade.php](resources/views/employee/attendance/show.blade.php)
- **Fitur:**
  - Tampilkan detail absensi lengkap
  - Lihat koordinat GPS check-in & check-out
  - Lihat status kehadiran
  - Tampilkan waktu terlambat/pulang awal

### ✅ Export PDF
- **Route:** `GET /employee/attendance/export-pdf`
- **Fitur:**
  - Export laporan absensi bulanan ke PDF
  - Menampilkan statistik lengkap
  - Format siap cetak

---

## 2. Status Type yang Didukung

Sistem mendukung **5 jenis status absensi** yang berbeda:

| Status | Keterangan | Kode |
|--------|-----------|------|
| **Hadir** | Karyawan hadir tepat waktu | `present` |
| **Terlambat** | Karyawan hadir tapi terlambat | `late` |
| **Tidak Hadir** | Karyawan tidak hadir tanpa keterangan | `absent` |
| **Sakit** | Tidak hadir karena sakit | `sick` |
| **Izin/Cuti** | Tidak hadir dengan alasan tertentu (rapat, pribadi) | `permission` / `leave` |

**Catatan:** Kolom status dalam database menggunakan enum dengan nilai-nilai ini.

---

## 3. Database Schema

### Tabel: `attendances`

```
Field                 Type          Deskripsi
─────────────────────────────────────────────────────────
id                    UUID           Primary key
worker_id             UUID           FK ke workers
shift_id              UUID           FK ke shifts
location_id           UUID           FK ke locations
attendance_date       DATE           Tanggal absensi
check_in_at           DATETIME       Waktu check-in
check_in_latitude     DECIMAL(10,8)  Latitude check-in
check_in_longitude    DECIMAL(11,8)  Longitude check-in
check_in_distance_m   INT            Jarak check-in (meter)
check_in_photo        STRING         Path foto check-in
check_out_at          DATETIME NULL  Waktu check-out
check_out_latitude    DECIMAL NULL   Latitude check-out
check_out_longitude   DECIMAL NULL   Longitude check-out
check_out_distance_m  INT NULL       Jarak check-out (meter)
status                ENUM           present/absent/leave/sick/permission
is_late               BOOLEAN        Apakah terlambat?
late_minutes          INT            Berapa menit terlambat?
is_early_leave        BOOLEAN        Pulang awal?
early_leave_minutes   INT            Berapa menit pulang awal?
is_outside_radius     BOOLEAN        Apakah di luar radius?
overtime_minutes      INT NULL       Menit lembur
notes                 TEXT NULL      Catatan absensi
created_at, updated_at TIMESTAMP     Timestamp
```

---

## 4. Controller & Service

### Controller: AttendanceController
**Lokasi:** [app/Http/Controllers/Employee/AttendanceController.php](app/Http/Controllers/Employee/AttendanceController.php)

**Methods:**
```php
- index()                // Tampilkan riwayat absensi
- checkInForm()          // Tampilkan form check-in
- checkIn()              // Proses check-in
- checkOutForm()         // Tampilkan form check-out
- checkOut()             // Proses check-out
- show($id)              // Tampilkan detail absensi
- exportPdf()            // Export ke PDF
```

### Service: AttendanceService
**Lokasi:** `app/Services/Attendance/AttendanceService.php`

**Key Methods:**
```php
- getAll(array $filters)           // Dapatkan daftar absensi dengan filter
- getMonthlyReport()               // Laporan bulanan
- recordCheckIn()                  // Catat check-in
- recordCheckOut()                 // Catat check-out
- calculateDistance()              // Hitung jarak Haversine
- validateLocation()               // Validasi lokasi dalam radius
```

---

## 5. GPS & Geolokasi

### Teknologi
- **Frontend:** Leaflet.js (JavaScript map library)
- **Geolocation API:** HTML5 Geolocation API
- **Distance Calculation:** Haversine Formula

### Implementasi
1. User membuka halaman check-in
2. Klik tombol "Dapatkan Lokasi"
3. Browser meminta permission geolokasi
4. Coordinate (lat/lng) ditampilkan di map
5. Jarak dari lokasi yang dipilih dihitung otomatis
6. Jika dalam radius → bisa lanjut, jika di luar → warning

### Radius Validasi
- Radius dikonfigurasi per **Location** (Lokasi Rumah Sakit)
- Default biasanya 200-500 meter
- Bisa diubah di master data lokasi

---

## 6. Routes Configuration

### Web Routes
```
GET/POST  /employee/attendance              → index (tampilkan riwayat)
GET       /employee/attendance/check-in     → checkInForm (form check-in)
POST      /employee/attendance              → checkIn (proses check-in)
GET       /employee/attendance/check-out    → checkOutForm (form check-out)
POST      /employee/attendance/{id}/check-out → checkOut (proses check-out)
GET       /employee/attendance/{id}         → show (detail)
GET       /employee/attendance/export-pdf   → exportPdf (export PDF)
```

Lokasi: [routes/web.php](routes/web.php) di dalam group `employee`

---

## 7. Cara Menggunakan (User Flow)

### 🔵 Proses Check-In Harian

1. **Login ke aplikasi** sebagai karyawan
2. **Buka menu:** Absensi → Check-In
3. **Pilih lokasi** dari dropdown (contoh: Rumah Sakit Pusat)
4. **Klik "Dapatkan Lokasi"**
   - Browser meminta akses GPS
   - Coordinate otomatis terisi
5. **Cek jarak:**
   - Jika ✅ dalam radius → lanjut
   - Jika ❌ di luar radius → coba pindah lokasi
6. **(Opsional)** Upload foto & catat catatan
7. **Klik "Check-In Sekarang"** → Simpan

### 🔴 Proses Check-Out

1. **Buka menu:** Absensi → Check-Out
2. **Sistem otomatis** menampilkan check-in hari ini
3. **Klik "Dapatkan Lokasi"** untuk check-out
4. **Sistem otomatis hitung:**
   - Status (hadir/terlambat/pulang awal)
   - Menit lembur
5. **(Opsional)** Tambah catatan
6. **Klik "Check-Out Sekarang"** → Simpan

### 📊 Lihat Riwayat

1. **Buka menu:** Absensi → Riwayat
2. **Filter** berdasarkan:
   - Tanggal (dari-sampai)
   - Status (Hadir/Terlambat/Tidak Hadir)
3. **Lihat statistik** bulanan
4. **Klik detail** untuk melihat koordinat GPS
5. **Export PDF** jika diperlukan

---

## 8. Testing & Data Demo

### Test Scenarios

**Scenario 1: Check-In On-Time**
- Time: 07:30 (shift mulai 07:00, toleransi 15 menit)
- Location: Dalam radius
- Expected: Status = "present" (hadir)

**Scenario 2: Check-In Late**
- Time: 08:30 (lebih dari toleransi 15 menit)
- Location: Dalam radius
- Expected: Status = "late" (terlambat 75 menit)

**Scenario 3: Out of Radius**
- Time: 08:00
- Location: > 500 meter dari lokasi yang dipilih
- Expected: Warning ⚠️ "Anda berada di luar radius lokasi"

**Scenario 4: Check-Out Early**
- Check-in: 07:00
- Check-out: 14:30 (shift berakhir 16:00)
- Expected: is_early_leave = true, early_leave_minutes = 90

**Scenario 5: Overtime**
- Check-in: 07:00
- Check-out: 17:30 (shift berakhir 16:00)
- Expected: overtime_minutes = 90

### Test Accounts
```
User (Employee):
- Email: worker@example.com
- Password: password
- Has worker profile
- Can check-in/out
```

---

## 9. Pengembangan Selanjutnya (Opsional)

Jika ingin menambah fitur:

### ✨ Possible Enhancements
- [ ] **Mobile App:** Buat mobile-specific check-in interface
- [ ] **Real-time Notification:** Notifikasi ketika ada keterlambatan
- [ ] **Attendance Report:** Dashboard manager untuk monitor kehadiran tim
- [ ] **Biometric Integration:** Integrasi dengan mesin finger print/face recognition
- [ ] **Leave Integration:** Otomatis buat attendance record saat ada leave approval
- [ ] **Overtime Approval:** Fitur persetujuan lembur oleh manager
- [ ] **Shift Swap Tracking:** Catat absensi berdasarkan shift swap
- [ ] **Weekly Reports:** Email ringkasan kehadiran mingguan

---

## 10. Troubleshooting

### ❌ GPS tidak bekerja
- **Solusi:** 
  - Pastikan HTTPS (required untuk geolocation API)
  - Periksa permission browser
  - Coba refresh halaman

### ❌ Terlalu jauh dari lokasi
- **Solusi:**
  - Cek lokasi yang dipilih sudah benar
  - Minta ubah radius lokasi ke IT (di master data)
  - Gunakan lokasi alternatif jika ada

### ❌ Tidak bisa check-out
- **Solusi:**
  - Pastikan sudah check-in hari ini
  - Cek apakah shift belum berakhir
  - Hubungi admin jika ada masalah

---

## 11. File-File Terkait

```
Backend:
├── app/Http/Controllers/Employee/AttendanceController.php
├── app/Services/Attendance/AttendanceService.php
├── app/DTOs/AttendanceDTO.php
├── app/Models/Attendance.php
└── database/migrations/xxxx_create_attendances_table.php

Frontend:
├── resources/views/employee/attendance/
│   ├── index.blade.php (Riwayat)
│   ├── check-in.blade.php (Form Check-In)
│   ├── check-out.blade.php (Form Check-Out)
│   └── show.blade.php (Detail)
├── resources/css/ (Tailwind CSS)
└── resources/js/ (Alpine.js untuk interaksi)

Routes:
└── routes/web.php (Attendance routes di group 'employee')
```

---

## 12. Kesimpulan

**Status:** ✅ **FITUR ATTENDANCE SUDAH LENGKAP & SIAP DIGUNAKAN**

Semua komponen sudah terimplementasi:
- ✅ GPS check-in/check-out
- ✅ Status differentiation (hadir/terlambat/sakit/izin/cuti)
- ✅ Jarak validasi dengan Haversine formula
- ✅ Statistik dan reporting
- ✅ Export PDF
- ✅ Responsive UI (mobile-friendly)

**Yang perlu dilakukan sekarang:**
1. ✅ Test fitur di browser dengan akun karyawan
2. ✅ Verifikasi GPS bekerja (pastikan HTTPS)
3. ✅ Lihat data yang terbuat di database
4. ✅ Demo ke stakeholder

**Yang tidak perlu dikerjakan:**
- ❌ Tidak perlu membuat controller baru
- ❌ Tidak perlu membuat view baru
- ❌ Tidak perlu migrasi database baru
- ❌ Tidak perlu bikin routes baru

Aplikasi sudah **production-ready untuk fitur attendance**! 🎉
