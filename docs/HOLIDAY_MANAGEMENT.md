# Manajemen Hari Libur - Panduan Lengkap

## Gambaran Umum

Sistem SIMPEG memiliki fitur manajemen hari libur yang memungkinkan Anda untuk:
- 📅 Menambah/mengedit/menghapus hari libur nasional dan cuti bersama
- 📝 Input hari libur untuk tahun-tahun mendatang secara bulk
- 🤖 Auto-generate hari libur berdasarkan kalender nasional
- 🔗 Integrasi dengan Google Calendar untuk sinkronisasi otomatis

## Struktur Data

### Tabel: `holidays`

```
id          UUID (Primary Key)
name        string - Nama hari libur
date        date - Tanggal libur
description string - Deskripsi (opsional)
is_national boolean - Tandai sebagai libur nasional (default: true)
created_at  timestamp
updated_at  timestamp
```

### Model: `App\Models\Holiday`

```php
// Filter berdasarkan tahun
Holiday::year(2027)->get();

// Filter berdasarkan range tanggal
Holiday::dateRange('2027-01-01', '2027-12-31')->get();

// Filter hanya libur nasional
Holiday::national()->get();
```

## Cara Menggunakan

### 1. Akses Menu Holiday Management

**URL:** `http://localhost:8000/holidays`

**Persyaratan:** 
- Login sebagai Super Admin atau HR
- Permission: `holiday.manage`

### 2. Menambah Hari Libur Satu Per Satu

1. Klik tombol **"Tambah Hari Libur"**
2. Isi form:
   - **Nama Libur**: Contoh "Tahun Baru Imlek 2028"
   - **Tanggal**: Pilih tanggal libur
   - **Deskripsi**: Contoh "Tahun Baru Imlek"
   - **Tipe**: Pilih "Libur Nasional" atau "Cuti Bersama"
3. Klik **Simpan**

### 3. Menambah Hari Libur Secara Bulk (Recommended untuk Tahun Baru)

Cara ini lebih efisien untuk input banyak hari libur sekaligus:

1. Dari menu Holiday, klik **"Input Massal"**
2. Pilih tahun yang ingin diinputkan
3. Klik **"Tambah Baris"** untuk setiap libur yang akan ditambahkan
4. Isi data:
   - Nama Libur
   - Tanggal
   - Deskripsi
5. Klik **Simpan**

### 4. Auto-Generate Hari Libur (Beta)

Fitur untuk auto-generate hari libur berdasarkan kalender nasional:

1. Dari menu Holiday, klik **"Auto-Generate"**
2. Pilih tahun target
3. Pilih tipe hari libur yang ingin di-generate (Nasional/Cuti Bersama/Semua)
4. Sistem akan:
   - Mengambil data libur nasional
   - Menghitung libur agama (Isra Mikraj, Idul Fitri, dll)
   - Menambahkan cuti bersama otomatis
5. Review sebelum menyimpan
6. Klik **Generate & Simpan**

**Catatan:** Fitur ini membutuhkan API untuk kalender nasional Indonesia.

## Workflow Update Tahun Baru

Setiap tahun baru, ikuti langkah ini untuk update libur tahun mendatang:

### Langkah 1: Pastikan Data 2026 Ada

Saat ini sudah ada data libur 2026 (partial):
- ✅ Tahun Baru Masehi (2026-01-01)
- ✅ Isra Mikraj (2026-01-16)
- ✅ Tahun Baru Imlek (2026-02-17)

### Langkah 2: Update untuk Tahun 2027

**Waktu yang tepat:** Sekitar Oktober-November 2026, atau ketika pemerintah mengumumkan libur nasional tahun depan

**Data yang perlu ditambahkan (Contoh 2027):**

Hari libur nasional Indonesia tahun 2027 (akan diumumkan pemerintah):

```
1 Januari 2027 - Tahun Baru Masehi
? Januari 2027 - Isra Mikraj (tergantung keputusan pemerintah)
? Februari 2027 - Tahun Baru Imlek (tergantung hijriah)
? Maret 2027 - Hari Raya Nyepi
? April 2027 - Wafat Yesus Kristus / Jumat Agung
? Mei 2027 - Idul Fitri 1448 H
? Mei 2027 - Hari Raya Waisak
? Mei 2027 - Kenaikan Yesus Kristus
1 Juni 2027 - Hari Lahir Pancasila
? Juni 2027 - Idul Adha 1448 H
? Juni 2027 - Tahun Baru Islam 1449 H
17 Agustus 2027 - Hari Kemerdekaan RI
? September 2027 - Maulid Nabi Muhammad
25 Desember 2027 - Hari Raya Natal
```

### Langkah 3: Cara Input Data Libur 2027

**Option A: Input Bulk (Recommended)**

1. Login sebagai HR / Super Admin
2. Buka `/holidays`
3. Klik **"Input Massal"**
4. Pilih Tahun **2027**
5. Tambahkan semua libur nasional sesuai daftar di atas
6. Klik **Simpan**

**Option B: Input Satu Per Satu**

1. Klik **"Tambah Hari Libur"**
2. Isi data untuk setiap libur
3. Klik **Simpan**

**Option C: Melalui Database Script**

Jika data banyak, gunakan artisan command atau script:

```bash
php artisan tinker
```

```php
// Atau buat seeder baru untuk tahun tertentu
// database/seeders/Holiday2027Seeder.php

$holidays = [
    ['name' => 'Tahun Baru Masehi', 'date' => '2027-01-01'],
    // ... tambahkan sisanya
];

foreach ($holidays as $h) {
    Holiday::create([
        'name' => $h['name'],
        'date' => $h['date'],
        'is_national' => true
    ]);
}
```

### Langkah 4: Verifikasi Data

Setelah input, verifikasi:

1. Klik filter tahun **2027**
2. Lihat apakah semua libur sudah termasuk
3. Check tanggal dan nama sudah benar
4. Jika ada kesalahan, klik **Edit** untuk perbaiki

## Integrasi dengan Google Calendar

### Prasyarat
- Google Calendar sudah terintegrasi (lihat: [GOOGLE_CALENDAR_INTEGRATION.md](./GOOGLE_CALENDAR_INTEGRATION.md))
- User sudah authorize Google Calendar

### Fitur Sinkronisasi

Setelah libur ditambahkan, sistem akan otomatis:

1. ✅ **Membuat event** di Google Calendar user
   - Event name: "[LIBUR] Nama Hari Libur"
   - Full-day event (tidak ada jam kerja)
   - Reminder: 1 hari sebelumnya

2. ✅ **Update event** jika tanggal/nama libur berubah
3. ✅ **Hapus event** jika libur dihapus dari sistem

### Cara Mengaktifkan Sinkronisasi

**Sinkronisasi Otomatis:**
- Setiap user yang punya akses holiday management bisa enable di setting akun
- Toggle: **"Sinkronkan hari libur ke Google Calendar"**

**Sinkronisasi Manual:**
- Di halaman list holidays, ada tombol **"Sync ke Google Calendar"**
- Klik untuk sync langsung ke calendar user

### Contoh Event di Google Calendar

```
📅 [LIBUR] Tahun Baru Masehi
   Senin, 1 Januari 2027 | All day
   Deskripsi: Tahun Baru 2025
   Reminder: 1 hari sebelumnya
```

## FAQ - Pertanyaan Umum

### Q: Bagaimana jika tgl libur berubah karena keputusan pemerintah?

**A:** 
1. Buka halaman holidays
2. Cari libur yang berubah
3. Klik **Edit**
4. Ubah tanggalnya
5. Klik **Simpan**
6. Jika Google Calendar terintegrasi, event otomatis terupdate

### Q: Bisa hapus hari libur?

**A:**
1. Cari libur yang ingin dihapus
2. Klik **Hapus** (atau ikon trash)
3. Confirm di dialog
4. Sistem akan:
   - Hapus dari database
   - Hapus event dari Google Calendar (jika terintegrasi)

### Q: Format tanggal apa yang diterima?

**A:** 
- Format input: DD/MM/YYYY atau pilih dari date picker
- Format database: YYYY-MM-DD
- Sistem otomatis convert

### Q: Bisa lihat libur tahun-tahun sebelumnya?

**A:**
1. Buka halaman holidays
2. Di dropdown "Filter Tahun", pilih tahun yang diinginkan
3. Klik **Filter**
4. Lihat list libur untuk tahun tersebut

### Q: Apakah hari libur mempengaruhi perhitungan cuti & lembur?

**A:** Ya! Sistem menggunakan data holidays untuk:
- ✅ Exclude hari libur dari perhitungan jam kerja
- ✅ Tidak hitung lemburjika pada hari libur
- ✅ Highlight di attendance calendar
- ✅ Report kehadiran otomatis exclude hari libur

### Q: Gimana dengan cuti bersama yang bukan libur nasional?

**A:**
Cuti bersama (yang ditetapkan per RSUD) bisa ditambahkan sebagai libur biasa:
1. Tambah hari libur baru
2. Nama: "Cuti Bersama [alasan]"
3. Tipe: Cuti Bersama (uncheck "Libur Nasional")
4. Simpan

Sistem akan treat sama seperti libur nasional.

## Tips & Best Practices

### 📌 Tip 1: Update Proaktif
- Jangan tunggu hingga tahun baru tiba
- Update libur 2-3 bulan sebelumnya
- Agar semua staff bisa plan leave dengan baik

### 📌 Tip 2: Komunikasi
- Setiap ada perubahan libur, infokan ke semua staff
- Bisa melalui notifikasi sistem atau email
- Sistem otomatis kirim notification saat holiday ditambah

### 📌 Tip 3: Backup Data
- Export list holidays regularly
- File backup di folder `/storage/backups/`
- Gunakan fitur Export Excel (coming soon)

### 📌 Tip 4: Validasi Data
- Sebelum "Simpan Bulk", review setiap baris
- Pastikan tahun dan tanggal benar
- Double-check nama libur (spelling penting untuk reporting)

### 📌 Tip 5: Sinkronisasi Google Calendar
- Enable setelah setup Google Calendar integration
- Pastikan semua user sudah authorize
- Manfaatkan untuk reminder di personal calendar

## Troubleshooting

### Problem: Tidak bisa akses menu Holidays

**Solusi:**
- Cek role user (harus Super Admin atau HR)
- Cek permission `holiday.manage` sudah di-grant
- Cek login berhasil

### Problem: Libur tidak muncul di Google Calendar

**Solusi:**
- Verifikasi Google Calendar sudah terintegrasi
- Cek user sudah authorize Google
- Klik "Sync ke Google Calendar" manual
- Cek Google Calendar permission setting

### Problem: Tanggal libur tidak konsisten

**Solusi:**
- Cek timezone di `config/app.php`
- Pastikan gunakan timezone Indonesia: `Asia/Jakarta`
- Bersihkan libur duplicate (jika ada)

### Problem: Bulk input gagal

**Solusi:**
- Review error message
- Pastikan format tanggal benar (DD/MM/YYYY)
- Jika terlalu banyak rows, split ke 2x input
- Cek permission masih valid

## Hubungi Tim Development

Jika ada bug atau fitur request:
- Report di issue tracker
- Include screenshot dan langkah reproduce
- Mention: @HR-Team atau @ITSupport

---

**Last Updated:** February 2026  
**Version:** 1.0  
**Status:** Active
