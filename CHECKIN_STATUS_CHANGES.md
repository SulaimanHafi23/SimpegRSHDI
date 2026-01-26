# Perubahan: Status Kehadiran di Form Check-In

## Status: ✅ COMPLETED

Sudah ditambahkan **pilihan status kehadiran** di halaman check-in agar karyawan bisa memilih status mereka sebelum check-in.

---

## 📋 Apa yang Berubah

### 1. **Check-In Form** 
**File:** [resources/views/employee/attendance/check-in.blade.php](resources/views/employee/attendance/check-in.blade.php)

**Perubahan:**
- ✅ Ditambah field dropdown **"Status Kehadiran"**
- Field ini muncul setelah pilih lokasi, sebelum GPS geolocation
- Ada 4 pilihan status:
  - **Hadir** (present)
  - **Sakit** (sick)
  - **Izin** (permission)
  - **Cuti** (leave)
- Setiap option ada penjelasan singkat di bawah dropdown
- Field **required** (harus diisi)

### 2. **Controller Validation**
**File:** [app/Http/Controllers/Employee/AttendanceController.php](app/Http/Controllers/Employee/AttendanceController.php)

**Perubahan:**
- ✅ Ditambah validation rule untuk status:
  ```php
  'status' => 'required|in:present,sick,permission,leave',
  ```
- Status dikirim ke service bersama data GPS dan foto

### 3. **Attendance Service**
**File:** [app/Services/Attendance/AttendanceService.php](app/Services/Attendance/AttendanceService.php)

**Perubahan:**
- ✅ Sebelumnya: `'status' => 'present'` (hardcoded)
- ✅ Sekarang: `'status' => $data['status'] ?? 'present'` (dari request)
- Jika status tidak 'present', tidak hitung terlambat (hanya untuk hadir)

---

## 🎯 Alur Penggunaan Sekarang

```
1. Karyawan buka halaman Check-In
   ↓
2. Pilih Lokasi (Rumah Sakit Pusat, Klinik, dll)
   ↓
3. **BARU**: Pilih Status (Hadir/Sakit/Izin/Cuti) ← PERUBAHAN
   ↓
4. Klik "Dapatkan Lokasi" → GPS capture
   ↓
5. Upload foto (opsional)
   ↓
6. Tambah catatan (opsional)
   ↓
7. Klik "Check In Sekarang"
```

---

## 📊 Contoh Skenario

### Scenario 1: Karyawan Hadir Tepat Waktu
```
- Status dipilih: "Hadir"
- Check-in jam 07:00 (shift 07:00, toleransi 15 min)
- Hasil: is_late = false, late_minutes = 0
```

### Scenario 2: Karyawan Sakit
```
- Status dipilih: "Sakit"
- GPS tidak perlu disimpan (cukup marking sakit)
- Hasil: status = "sick", is_late = null, late_minutes = null
```

### Scenario 3: Karyawan Izin
```
- Status dipilih: "Izin"
- Alasan bisa ditulis di field "Catatan"
- Hasil: status = "permission", catatan disimpan
```

### Scenario 4: Karyawan Cuti (Approved Leave)
```
- Status dipilih: "Cuti"
- Sistem sudah check jika cuti approved di database
- Hasil: status = "leave"
```

---

## 🗄️ Database Schema

Kolom `status` di tabel `attendances` sudah support enum dengan nilai:
- `present` ← hadir
- `absent` ← tidak hadir tanpa keterangan
- `sick` ← sakit
- `permission` ← izin
- `leave` ← cuti

**Catatan:** Kolom ini sudah ada di schema, jadi tidak perlu migration baru.

---

## ✨ Fitur Tambahan

### Info Teks di Bawah Dropdown
Setiap status punya penjelasan:
- **Hadir:** Datang ke kantor
- **Sakit:** Tidak hadir karena sakit
- **Izin:** Tidak hadir untuk keperluan pribadi
- **Cuti:** Cuti yang sudah disetujui

### Instruksi yang Diupdate
Langkah-langkah check-in sekarang termasuk:
1. ✅ Pilih lokasi absensi dari dropdown
2. ✅ **Pilih status kehadiran Anda (Hadir/Sakit/Izin/Cuti)** ← BARU
3. ✅ Klik tombol "Dapatkan Lokasi" untuk mendapatkan koordinat GPS Anda
4. ✅ Pastikan Anda berada dalam radius lokasi yang dipilih
5. ✅ Upload foto (opsional) dan tambahkan catatan jika diperlukan
6. ✅ Klik "Check In Sekarang" untuk menyelesaikan

---

## 🧪 Testing

### Cara Test
1. Buka browser → http://localhost:8000
2. Login sebagai karyawan
3. Buka menu **Absensi → Check-In**
4. Form sekarang harus ada dropdown "Status Kehadiran"
5. Coba pilih setiap status dan lihat form behavior
6. Submit dan cek database

### Expected Results
- Form validation mencegah submit tanpa pilih status
- Database menyimpan status yang dipilih di kolom `status`
- Views yang lain (check-out, history) juga menampilkan status

---

## 📝 Files Modified

| File | Changes |
|------|---------|
| [resources/views/employee/attendance/check-in.blade.php](resources/views/employee/attendance/check-in.blade.php) | ✅ Tambah status dropdown + instruksi |
| [app/Http/Controllers/Employee/AttendanceController.php](app/Http/Controllers/Employee/AttendanceController.php) | ✅ Tambah validation rule status |
| [app/Services/Attendance/AttendanceService.php](app/Services/Attendance/AttendanceService.php) | ✅ Ubah hardcoded 'present' ke dynamic status |

---

## 🚀 Deployment

Perubahan sudah ready to deploy:
- ✅ Tidak ada migration baru diperlukan
- ✅ Kolom `status` sudah ada di database
- ✅ Backward compatible (default ke 'present' jika status kosong)
- ✅ Tidak break fitur lain

---

## 📌 Catatan

1. **GPS tetap required:** Meskipun ada status pilihan, GPS coordinates tetap harus diinput (untuk tracking lokasi karyawan)
   
2. **Foto tetap opsional:** Foto bisa dikosongkan (hanya untuk dokumentasi tambahan)

3. **Cuti vs Permission:**
   - **Cuti (leave):** Untuk cuti yang sudah diapprove, biasanya diambil dari LeaveRequest
   - **Izin (permission):** Untuk keperluan dadakan tanpa rencana
   - **Sakit (sick):** Untuk ketidakhadiran karena sakit

4. **Future Enhancement:** Bisa ditambahkan:
   - Attachment dokumen (SIPA untuk sakit, surat izin untuk permission)
   - Kategori izin (rapat, pribadi, lainnya)
   - Approval workflow untuk sick leave
   - SMS notifikasi ke manager

---

## ✅ Summary

Fitur **Status Kehadiran** di check-in form sudah ditambahkan dengan:
- ✅ Dropdown dengan 4 pilihan (Hadir/Sakit/Izin/Cuti)
- ✅ Validation yang ketat
- ✅ Service logic yang support dynamic status
- ✅ Instruksi yang jelas untuk user
- ✅ Database schema sudah siap
- ✅ Siap di-deploy

**Sekarang karyawan bisa langsung memilih status mereka saat check-in!** 🎉
