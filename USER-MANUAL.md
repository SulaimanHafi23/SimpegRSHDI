# 📖 USER MANUAL - SIMPEGRS

**Sistem Informasi Manajemen Pegawai Rumah Sakit**  
**RSUD Haji Darlan Ismail**

Version: 1.0.0  
Last Updated: January 3, 2026

---

## 📋 Daftar Isi

1. [Pengenalan](#1-pengenalan)
2. [Login & Logout](#2-login--logout)
3. [Panduan untuk Employee](#3-panduan-untuk-employee)
4. [Panduan untuk Manager](#4-panduan-untuk-manager)
5. [Panduan untuk HR](#5-panduan-untuk-hr)
6. [Panduan untuk Super Admin](#6-panduan-untuk-super-admin)
7. [FAQ & Troubleshooting](#7-faq--troubleshooting)

---

## 1. Pengenalan

### 1.1 Tentang SIMPEGRS

SIMPEGRS adalah sistem informasi manajemen kepegawaian yang dirancang khusus untuk RSUD Haji Darlan Ismail. Sistem ini membantu mengelola:

- ✅ Data karyawan
- ✅ Absensi dengan GPS
- ✅ Pengajuan cuti dan lembur
- ✅ Penjadwalan shift
- ✅ Dokumen karyawan
- ✅ Laporan dan analitik

### 1.2 Role & Hak Akses

| Role | Deskripsi | Hak Akses |
|------|-----------|-----------|
| **Super Admin** | Administrator sistem | Full akses ke semua fitur |
| **HR** | Staff HRD | Manajemen karyawan, approval semua departemen |
| **Manager** | Kepala Departemen | Approval untuk departemen sendiri |
| **Employee** | Karyawan | Self-service (absensi, cuti, lembur) |

### 1.3 Browser yang Didukung

- ✅ Google Chrome (recommended)
- ✅ Mozilla Firefox
- ✅ Microsoft Edge
- ✅ Safari

**Catatan:** Pastikan browser Anda mengizinkan akses GPS untuk fitur absensi.

---

## 2. Login & Logout

### 2.1 Cara Login

1. **Buka browser** dan akses URL: `http://simpegrs.yourdomain.com`
2. **Masukkan kredensial:**
   - **NIP** atau **Email**
   - **Password**
3. **Klik tombol "Login"**
4. Sistem akan mengarahkan Anda ke dashboard sesuai role

### 2.2 Lupa Password

1. Klik link **"Lupa Password?"** di halaman login
2. Masukkan **email** Anda
3. Cek email untuk link reset password
4. Klik link dan masukkan password baru
5. Login dengan password baru

### 2.3 Cara Logout

1. Klik **nama Anda** di pojok kanan atas
2. Pilih **"Logout"**
3. Anda akan diarahkan kembali ke halaman login

---

## 3. Panduan untuk Employee

### 3.1 Dashboard Employee

Dashboard menampilkan:
- ✅ Status absensi hari ini
- ✅ Jadwal shift Anda
- ✅ Saldo cuti
- ✅ Notifikasi terbaru
- ✅ Riwayat absensi minggu ini

### 3.2 Check-in (Masuk Kerja)

#### Langkah-langkah:

1. **Buka menu "Absensi"**
2. **Klik tombol "Check-in"**
3. **Izinkan akses GPS** saat browser meminta
4. **Pilih lokasi kantor** dari dropdown (jika ada multiple locations)
5. **Sistem akan menampilkan:**
   - Jarak Anda dari kantor
   - Status: Dalam radius / Luar radius
   - Waktu check-in
6. **Ambil foto** (opsional, jika diaktifkan)
7. **Klik "Submit Check-in"**

#### Status Check-in:

| Status | Keterangan |
|--------|------------|
| ✅ **Tepat Waktu** | Check-in sebelum atau tepat jam masuk shift |
| ⚠️ **Terlambat** | Check-in setelah jam masuk + grace period |
| 📍 **Dalam Radius** | Anda berada dalam radius kantor (< 100m) |
| 📍 **Luar Radius** | Anda berada di luar radius kantor |

**Catatan:**
- Check-in hanya bisa dilakukan 1x per hari
- Pastikan GPS aktif dan akurat
- Foto wajib diambil jika terlambat atau di luar radius (tergantung kebijakan)

### 3.3 Check-out (Pulang Kerja)

#### Langkah-langkah:

1. **Buka menu "Absensi"**
2. **Klik tombol "Check-out"**
3. **Izinkan akses GPS**
4. **Sistem akan menampilkan:**
   - Jarak dari kantor
   - Waktu check-out
   - Total jam kerja hari ini
5. **Ambil foto** (opsional)
6. **Klik "Submit Check-out"**

#### Informasi yang Dihitung:

- ✅ Total jam kerja (check-out - check-in - istirahat)
- ✅ Deteksi pulang cepat (jika ada)
- ✅ Status absensi hari ini

### 3.4 Riwayat Absensi

**Menu:** Absensi → Riwayat

Fitur:
- ✅ Lihat absensi per hari, minggu, atau bulan
- ✅ Filter by tanggal, status
- ✅ Detail: jam masuk, jam pulang, foto, lokasi
- ✅ Download laporan personal

### 3.5 Pengajuan Cuti

#### Langkah-langkah:

1. **Buka menu "Cuti"**
2. **Klik "Buat Pengajuan Cuti"**
3. **Isi form:**
   - **Tipe Cuti:** Pilih (Tahunan, Sakit, Urgent, dll)
   - **Tanggal Mulai**
   - **Tanggal Selesai**
   - **Alasan:** Jelaskan alasan cuti
   - **Dokumen Pendukung:** Upload (opsional, wajib untuk cuti sakit)
4. **Klik "Submit"**
5. **Tunggu approval** dari Manager atau HR

#### Status Pengajuan Cuti:

| Status | Icon | Keterangan |
|--------|------|------------|
| **Pending** | 🕒 | Menunggu approval |
| **Approved** | ✅ | Disetujui |
| **Rejected** | ❌ | Ditolak (lihat alasan) |
| **Cancelled** | 🚫 | Dibatalkan oleh Anda |

#### Cek Saldo Cuti:

- Buka **menu "Cuti"**
- Lihat **"Saldo Cuti"** di bagian atas
- Detail: Jatah tahunan, terpakai, sisa

**Catatan:**
- Pengajuan cuti minimal H-3 (3 hari sebelum)
- Cuti sakit wajib melampirkan surat dokter
- Saldo cuti akan berkurang setelah cuti disetujui

### 3.6 Pengajuan Lembur

#### Langkah-langkah:

1. **Buka menu "Lembur"**
2. **Klik "Buat Pengajuan Lembur"**
3. **Isi form:**
   - **Tanggal:** Tanggal lembur
   - **Jam Mulai**
   - **Jam Selesai**
   - **Alasan:** Jelaskan pekerjaan yang akan dilakukan
4. **Sistem otomatis menghitung** total jam lembur
5. **Klik "Submit"**
6. **Tunggu approval**

#### Aturan Lembur:

- ✅ Maksimal 4 jam per hari
- ✅ Maksimal 40 jam per bulan
- ✅ Lembur di hari libur maksimal 7 jam
- ✅ Wajib approval Manager/HR

### 3.7 Tukar Shift (Shift Swap)

#### Langkah-langkah:

1. **Buka menu "Shift"**
2. **Klik "Request Shift Swap"**
3. **Pilih shift Anda** yang ingin ditukar
4. **Pilih tipe swap:**

   **Opsi A: Open Swap**
   - Buka untuk semua rekan se-departemen
   - Tidak menentukan target spesifik
   - Rekan yang tertarik bisa "accept"

   **Opsi B: Direct Swap**
   - Pilih rekan target spesifik
   - Pilih shift target yang ingin ditukar
   - Target harus approve

5. **Masukkan alasan**
6. **Submit dan tunggu:**
   - Accept dari rekan (jika direct swap)
   - Approval Manager

#### Status Shift Swap:

| Status | Keterangan |
|--------|------------|
| **Pending** | Menunggu accept rekan (direct) atau approval manager (open) |
| **Accepted** | Rekan sudah accept, menunggu approval manager |
| **Approved** | Manager approve, shift sudah ditukar |
| **Rejected** | Ditolak oleh rekan atau manager |
| **Cancelled** | Dibatalkan oleh Anda |

**Aturan Shift Swap:**
- ✅ Minimal 48 jam sebelum shift
- ✅ Harus ada rest period 12 jam
- ✅ Tidak boleh konflik dengan cuti/lembur

### 3.8 Upload Dokumen

#### Langkah-langkah:

1. **Buka menu "Dokumen Saya"**
2. **Klik "Upload Dokumen"**
3. **Isi form:**
   - **Tipe Dokumen:** KTP, BPJS, Ijazah, Sertifikat, dll
   - **Nomor Dokumen**
   - **File:** PDF atau gambar (max 5MB)
   - **Catatan:** (opsional)
4. **Klik "Upload"**
5. **Tunggu verifikasi** dari HR

#### Tipe File yang Diterima:

- ✅ PDF (recommended)
- ✅ JPG, JPEG, PNG
- ✅ Maksimal 5MB per file

**Catatan:**
- Dokumen harus jelas dan terbaca
- HR akan memverifikasi dalam 1-3 hari kerja
- Jika ditolak, Anda bisa upload ulang

### 3.9 Profil Saya

**Menu:** Profil

Fitur:
- ✅ Lihat data pribadi (tidak bisa edit)
- ✅ Update foto profil
- ✅ Ganti password
- ✅ Update nomor telepon & email

#### Cara Ganti Password:

1. Buka **"Profil"**
2. Klik **"Ganti Password"**
3. Masukkan:
   - Password lama
   - Password baru
   - Konfirmasi password baru
4. Klik **"Simpan"**

---

## 4. Panduan untuk Manager

### 4.1 Dashboard Manager

Dashboard menampilkan (khusus departemen Anda):
- ✅ Total karyawan
- ✅ Attendance rate hari ini
- ✅ Pending approvals (cuti, lembur, shift swap)
- ✅ Chart absensi 7 hari terakhir
- ✅ Top performers

### 4.2 Approval Cuti

#### Langkah-langkah:

1. **Buka menu "Approvals" → "Cuti"**
2. **Lihat daftar** pengajuan cuti yang pending
3. **Klik "Detail"** pada pengajuan yang ingin direview
4. **Review informasi:**
   - Nama karyawan & departemen
   - Tipe cuti & tanggal
   - Alasan
   - Saldo cuti tersisa
   - Dokumen pendukung (jika ada)
5. **Buat keputusan:**

   **Approve:**
   - Klik tombol **"Approve"**
   - Masukkan catatan (opsional)
   - Konfirmasi

   **Reject:**
   - Klik tombol **"Reject"**
   - Masukkan alasan penolakan (WAJIB)
   - Konfirmasi

6. **Karyawan akan mendapat notifikasi**

**Tips Approval:**
- ✅ Cek saldo cuti karyawan
- ✅ Cek jadwal tim (tidak bertabrakan dengan cuti lain)
- ✅ Pastikan minimal staffing terpenuhi
- ✅ Cuti sakit harus ada surat dokter

### 4.3 Approval Lembur

#### Langkah-langkah:

1. **Buka "Approvals" → "Lembur"**
2. **Review pengajuan:**
   - Tanggal & jam lembur
   - Total jam
   - Alasan/pekerjaan
3. **Pertimbangan:**
   - Apakah pekerjaan urgent?
   - Apakah sudah melebihi quota bulanan?
   - Apakah anggaran lembur cukup?
4. **Approve atau Reject**

**Aturan:**
- ✅ Max 4 jam/hari (weekday)
- ✅ Max 7 jam/hari (weekend/libur)
- ✅ Max 40 jam/bulan per karyawan

### 4.4 Approval Shift Swap

#### Langkah-langkah:

1. **Buka "Approvals" → "Shift Swap"**
2. **Review detail:**
   - Requester & Target
   - Shift yang akan ditukar
   - Alasan
   - Validasi sistem (lead time, rest period)
3. **Cek staffing:**
   - Apakah minimal staffing terpenuhi setelah swap?
   - Apakah ada konflik jadwal?
4. **Approve atau Reject**

**Sistem akan menampilkan warning jika:**
- ⚠️ Lead time < 48 jam
- ⚠️ Rest period < 12 jam
- ⚠️ Minimal staffing tidak terpenuhi

**Anda tetap bisa approve** dengan pertimbangan khusus, tapi masukkan alasan.

### 4.5 Lihat Tim Saya

**Menu:** Tim Saya

Fitur:
- ✅ Daftar karyawan di departemen Anda
- ✅ Status karyawan (aktif/non-aktif)
- ✅ Lihat detail karyawan
- ✅ Lihat absensi per karyawan
- ✅ Lihat riwayat cuti & lembur

### 4.6 Laporan Departemen

**Menu:** Laporan

Fitur:
- ✅ Laporan absensi departemen
- ✅ Laporan cuti & lembur
- ✅ Export to Excel
- ✅ Filter by periode

---

## 5. Panduan untuk HR

### 5.1 Dashboard HR

Dashboard menampilkan (seluruh sistem):
- ✅ Total karyawan (by status, by department, by employment type)
- ✅ Attendance today (present, late, absent)
- ✅ Pending approvals (all departments)
- ✅ Recent activities (hires, resignations)
- ✅ Chart attendance 7 hari

### 5.2 Manajemen Karyawan

#### 5.2.1 Tambah Karyawan Baru

**Menu:** Admin → Karyawan → Tambah Karyawan

**Langkah-langkah:**

1. **Klik "Tambah Karyawan"**
2. **Isi Tab "Data Pribadi":**
   - NIP (auto-generate atau manual)
   - Nama lengkap
   - Email
   - No. Telepon
   - Gender, Agama
   - Tanggal lahir
   - Alamat
3. **Isi Tab "Data Kepegawaian":**
   - Departemen
   - Posisi/Jabatan
   - Status kepegawaian (Tetap/Kontrak/Percobaan/Magang)
   - Tanggal bergabung
   - Gaji pokok (opsional)
4. **Isi Tab "Akun":**
   - Email login (bisa sama dengan email pribadi)
   - Password default
   - Role (Employee/Manager)
5. **Klik "Simpan"**

**Sistem akan otomatis:**
- ✅ Create user account
- ✅ Kirim email welcome (jika configured)
- ✅ Assign role & permissions

#### 5.2.2 Edit Data Karyawan

1. Buka **"Admin → Karyawan"**
2. Cari karyawan (search by NIP/nama)
3. Klik **"Edit"**
4. Update data yang perlu diubah
5. **Simpan**

#### 5.2.3 Non-aktifkan Karyawan

1. Buka detail karyawan
2. Klik **"Ubah Status"**
3. Pilih:
   - **Resign** - Karyawan mengundurkan diri
   - **Terminated** - Karyawan diberhentikan
   - **Inactive** - Non-aktif sementara
4. Masukkan **tanggal efektif**
5. Masukkan **alasan** (opsional)
6. **Konfirmasi**

**Catatan:**
- Karyawan non-aktif tidak bisa login
- Data tetap tersimpan di sistem
- Bisa diaktifkan kembali jika diperlukan

#### 5.2.4 Import Karyawan (Bulk)

**Langkah-langkah:**

1. **Download template:**
   - Buka **"Admin → Karyawan"**
   - Klik **"Download Template"**
   - Simpan file Excel

2. **Isi data di Excel:**
   - Kolom wajib: NIP, Nama, Email, Gender, Religion, DOB, Address, Department, Position, Employment Status, Join Date, Password
   - Ikuti format yang ada di template
   - Jangan ubah header kolom

3. **Upload file:**
   - Klik **"Import Karyawan"**
   - Pilih file Excel
   - Klik **"Upload & Process"**

4. **Review hasil:**
   - Sistem akan menampilkan:
     - ✅ Jumlah berhasil
     - ❌ Jumlah error (dengan detail error per row)
   - Perbaiki error dan upload ulang jika perlu

**Validasi Sistem:**
- ✅ NIP harus unik
- ✅ Email harus valid & unik
- ✅ Department, Position, Gender, Religion harus ada di master data
- ✅ Format tanggal harus benar
- ✅ Password minimal 8 karakter

#### 5.2.5 Export Data Karyawan

1. Buka **"Admin → Karyawan"**
2. **Set filter** (opsional):
   - Status (aktif/non-aktif)
   - Departemen
   - Employment status
3. Klik **"Export to Excel"**
4. File akan terdownload

### 5.3 Approval (HR Level)

HR bisa approve **semua departemen**:
- ✅ Cuti (all departments)
- ✅ Lembur (all departments)
- ✅ Dokumen (HR only)

**Prosedur sama seperti Manager**, tapi dengan akses lebih luas.

### 5.4 Verifikasi Dokumen

**Menu:** Admin → Dokumen Karyawan

#### Langkah-langkah:

1. **Buka "Approvals" → "Dokumen"**
2. **Lihat pending documents**
3. **Klik "Detail"**
4. **Download/lihat dokumen:**
   - Klik thumbnail atau "Download"
   - Buka di aplikasi viewer
5. **Review:**
   - Cek keaslian dokumen
   - Cek kejelasan scan/foto
   - Cek kesesuaian nomor dokumen
6. **Buat keputusan:**

   **Verify (Approve):**
   - Klik **"Verify"**
   - Masukkan catatan (opsional)
   - Konfirmasi

   **Reject:**
   - Klik **"Reject"**
   - Masukkan alasan penolakan (WAJIB)
     - Contoh: "Foto tidak jelas", "Nomor tidak sesuai", "Dokumen expired"
   - Konfirmasi

7. **Karyawan akan mendapat notifikasi**

### 5.5 Manajemen Master Data

#### 5.5.1 Departemen

**Menu:** Admin → Master Data → Departemen

Fitur:
- ✅ Tambah departemen baru
- ✅ Edit nama departemen
- ✅ Assign required document types
- ✅ Hapus (jika tidak ada karyawan)

#### 5.5.2 Posisi/Jabatan

**Menu:** Admin → Master Data → Posisi

Fitur:
- ✅ Tambah posisi
- ✅ Edit nama posisi
- ✅ Set departemen
- ✅ Hapus

#### 5.5.3 Lokasi Kantor

**Menu:** Admin → Master Data → Lokasi

Fitur:
- ✅ Tambah lokasi (nama, alamat, GPS, radius)
- ✅ Edit lokasi
- ✅ Set lokasi default
- ✅ Hapus

**Setting GPS:**
- Masukkan latitude & longitude (decimal)
- Set radius validasi (meter)
- Test dengan Google Maps

#### 5.5.4 Shift

**Menu:** Admin → Master Data → Shift

Fitur:
- ✅ Tambah shift (nama, jam masuk, jam pulang, break time)
- ✅ Edit shift
- ✅ Set grace period (toleransi terlambat)
- ✅ Hapus

**Contoh Shift:**
- Pagi: 07:00 - 15:00 (break 1 jam)
- Siang: 15:00 - 23:00 (break 1 jam)
- Malam: 23:00 - 07:00 (break 1 jam)

#### 5.5.5 Tipe Cuti

**Menu:** Admin → Master Data → Tipe Cuti

Fitur:
- ✅ Tambah tipe cuti
- ✅ Set kuota per tahun
- ✅ Set apakah dipotong saldo
- ✅ Set requires document

**Contoh Tipe Cuti:**
- Cuti Tahunan (12 hari/tahun)
- Cuti Sakit (unlimited, perlu surat dokter)
- Cuti Urgent (3 hari/tahun)
- Cuti Menikah (3 hari, tidak potong saldo)

#### 5.5.6 Hari Libur

**Menu:** Admin → Master Data → Hari Libur

Fitur:
- ✅ Tambah hari libur (nasional/cuti bersama)
- ✅ Edit tanggal & nama
- ✅ Hapus
- ✅ Import from iCalendar (future)

### 5.6 Penjadwalan Shift

**Menu:** Admin → Shift Scheduling

#### 5.6.1 Assign Shift ke Karyawan

**Langkah-langkah:**

1. **Pilih periode** (minggu/bulan)
2. **Pilih departemen**
3. **Sistem tampilkan grid:**
   - Rows: Karyawan
   - Columns: Tanggal
4. **Assign shift:**
   - Klik cell
   - Pilih shift dari dropdown
   - Shift ter-assign dengan warna

5. **Bulk assign:**
   - Select multiple karyawan
   - Pilih shift
   - Pilih tanggal range
   - Apply to all

6. **Klik "Simpan Jadwal"**

#### 5.6.2 Shift Override (Pengecualian)

Jika ada karyawan perlu shift berbeda untuk 1 hari:

1. Klik cell tanggal
2. Pilih **"Override Shift"**
3. Pilih shift pengganti
4. Masukkan alasan
5. Simpan

### 5.7 Laporan HR

**Menu:** Laporan

Fitur:
- ✅ Laporan Absensi (all departments)
- ✅ Laporan Cuti & Lembur
- ✅ Laporan Karyawan (turnover, hiring)
- ✅ Laporan Dokumen (kelengkapan)
- ✅ Export to Excel/PDF

---

## 6. Panduan untuk Super Admin

Super Admin memiliki **semua akses** HR + fitur tambahan:

### 6.1 User Management

**Menu:** Admin → Users

Fitur:
- ✅ Lihat semua users
- ✅ Edit user (email, role, status)
- ✅ Reset password user
- ✅ Aktivasi/Non-aktivasi akun
- ✅ Assign multiple roles

### 6.2 Role & Permission Management

**Menu:** Admin → Roles & Permissions

Fitur:
- ✅ Lihat roles (Super Admin, HR, Manager, Employee)
- ✅ Edit permissions per role
- ✅ Create custom role
- ✅ Assign permissions to role

**Permission Categories:**
- **Workers:** view, create, edit, delete, export, import
- **Attendance:** view, create, edit, delete, export
- **Leaves:** view, create, approve, reject
- **Overtimes:** view, create, approve, reject
- **Shifts:** view, create, edit, delete, schedule
- **Documents:** view, upload, verify, reject
- **Reports:** view, export
- **Master Data:** manage

### 6.3 System Settings

**Menu:** Admin → Settings

Fitur:
- ✅ Company info (nama, logo, alamat)
- ✅ GPS settings (radius, photo required)
- ✅ Attendance settings (grace period, auto check-out)
- ✅ Leave settings (default quota, carry over)
- ✅ Notification settings (email, push)

### 6.4 Audit Logs

**Menu:** Admin → Audit Logs

Fitur:
- ✅ Lihat semua aktivitas user
- ✅ Filter by: user, action, date
- ✅ Detail: Who, What, When, IP Address
- ✅ Export logs

**Events yang di-log:**
- Login/Logout
- Create/Update/Delete data
- Approvals
- Shift swaps
- Export/Import

### 6.5 Database Backup

**Menu:** Admin → Backup

Fitur:
- ✅ Manual backup (on-demand)
- ✅ Scheduled backup (daily/weekly)
- ✅ Download backup file
- ✅ Restore from backup

---

## 7. FAQ & Troubleshooting

### 7.1 Masalah Login

**Q: Lupa password, apa yang harus dilakukan?**

A: Klik "Lupa Password" di halaman login, masukkan email, dan ikuti instruksi reset password.

**Q: Akun terkunci setelah beberapa kali salah password**

A: Tunggu 15 menit atau hubungi HR/Admin untuk unlock akun.

---

### 7.2 Masalah Absensi

**Q: GPS tidak bisa diakses, bagaimana?**

A:
1. Cek apakah browser memiliki izin akses lokasi
2. Cek apakah GPS device aktif
3. Refresh halaman dan coba lagi
4. Jika tetap error, hubungi IT support

**Q: Terlambat check-in karena GPS error, apakah tetap dihitung terlambat?**

A: Hubungi Manager/HR dengan bukti error. HR bisa melakukan manual correction.

**Q: Lupa check-out, bagaimana?**

A: Hubungi HR untuk manual check-out. HR akan set jam pulang sesuai kebijakan.

**Q: Bisa check-in/out lebih dari 1x per hari?**

A: Tidak. Sistem hanya mengizinkan 1x check-in dan 1x check-out per hari.

---

### 7.3 Masalah Cuti

**Q: Pengajuan cuti ditolak tanpa alasan jelas?**

A: Hubungi Manager untuk klarifikasi. Manager wajib memberikan alasan penolakan.

**Q: Saldo cuti tidak sesuai?**

A: Hubungi HR. HR akan cek history dan lakukan adjustment jika perlu.

**Q: Bisa cancel cuti yang sudah diapprove?**

A: Ya, tapi harus ada approval lagi dari Manager/HR. Status akan kembali ke "Pending Cancellation".

**Q: Cuti urgent tidak bisa diajukan?**

A: Cuti urgent harus diajukan maksimal H+1 (sehari setelah kejadian). Jika melebihi, status berubah otomatis menjadi cuti tahunan.

---

### 7.4 Masalah Lembur

**Q: Pengajuan lembur harus sebelum atau sesudah lembur?**

A: Best practice: sebelum lembur. Tapi bisa juga H+1 (dengan approval).

**Q: Lembur weekend apakah dihitung double?**

A: Tergantung kebijakan perusahaan. Biasanya weekend = 2x tarif.

**Q: Lembur tidak sesuai jam sebenarnya di system?**

A: Hubungi HR untuk correction. HR bisa edit jam lembur dengan bukti pendukung.

---

### 7.5 Masalah Shift Swap

**Q: Rekan tidak menerima notifikasi shift swap request?**

A: Cek apakah notifikasi email aktif. Atau langsung japri rekan Anda untuk inform.

**Q: Shift swap sudah di-accept rekan tapi Manager reject, apa yang terjadi?**

A: Shift tidak jadi ditukar. Status kembali seperti semula. Manager akan memberikan alasan rejection.

**Q: Bisa cancel shift swap yang sudah approved?**

A: Sangat sulit. Harus hubungi HR dengan alasan emergency. HR bisa manual reverse.

---

### 7.6 Masalah Dokumen

**Q: Upload dokumen gagal terus?**

A:
1. Cek ukuran file (max 5MB)
2. Cek format file (PDF/JPG/PNG)
3. Rename file jika ada karakter special
4. Coba compress file jika terlalu besar

**Q: Dokumen ditolak HR tanpa alasan?**

A: HR wajib memberikan alasan. Cek notifikasi atau hubungi HR langsung.

**Q: Bisa delete dokumen yang sudah verified?**

A: Tidak bisa delete, tapi bisa upload versi baru (update).

---

### 7.7 Masalah Teknis

**Q: Sistem lemot/hang?**

A:
1. Refresh browser (Ctrl+F5)
2. Clear cache browser
3. Coba browser lain
4. Hubungi IT support

**Q: Notifikasi tidak muncul?**

A:
1. Cek izin notifikasi browser
2. Cek email (notifikasi juga dikirim via email)
3. Cek spam folder

**Q: Tidak bisa download file (export/dokumen)?**

A:
1. Cek popup blocker browser
2. Izinkan download di browser
3. Coba browser lain

---

### 7.8 Kontak Support

| Masalah | Kontak |
|---------|---------|
| **Password & Login** | IT Support: it@simpegrs.com |
| **Cuti & Lembur** | Manager atau HR: hr@simpegrs.com |
| **Data Karyawan** | HR: hr@simpegrs.com |
| **Absensi GPS** | IT Support: it@simpegrs.com |
| **Bug/Error Sistem** | IT Support: it@simpegrs.com |

**Hotline:** 0821-xxxx-xxxx (Senin-Jumat, 08:00-17:00)

---

## 📚 Panduan Video

Scan QR code untuk akses video tutorial:

- 📹 Cara Check-in/Check-out
- 📹 Cara Pengajuan Cuti
- 📹 Cara Tukar Shift
- 📹 Panduan Approval (Manager)
- 📹 Panduan Manajemen Karyawan (HR)

---

**Version:** 1.0.0  
**Last Updated:** January 3, 2026  
**Prepared by:** IT Team - RSUD Haji Darlan Ismail

---

**Terima kasih telah menggunakan SIMPEGRS! 🏥**
