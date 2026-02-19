# ✅ SimpegRSHDI — Feature Testing Checklist

> Tanggal Cek: 19 Februari 2026  
> Centang `[x]` bila fitur berjalan normal, `[!]` bila ada masalah (tulis catatan di bawahnya)

---

## 🔐 AUTENTIKASI (Semua Role)

| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 1 | Login dengan akun Admin | [ x] | |
| 2 | Login dengan akun HR | [x] | |
| 3 | Login dengan akun Manager | [x] | |
| 4 | Login dengan akun Pegawai | [x] | |
| 5 | Logout | [x] | |
| 6 | Redirect ke halaman yang tepat sesuai role setelah login | [x] | |
| 7 | Akses halaman yang bukan miliknya ditolak (403) | [x] | |

---

## 👑 ADMIN

### Dashboard
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 8 | Buka `/dashboard` — statistik muncul semua | [x] | |
| 9 | Chart distribusi pegawai per departemen tampil | [x] | |
| 10 | Tabel pending Pengajuan cuti tampil | [x] | |

### Manajemen Pegawai (`/workers`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 11 | Lihat daftar pegawai | [x] | |
| 12 | Tambah pegawai baru (form create) | [x] | | 
| 13 | Lihat detail pegawai | [x] | |
| 14 | Edit data pegawai | [x] | |
| 15 | Hapus pegawai | [x] | |
| 16 | Resign pegawai | [!] | | yang mana resign pegawai?
| 17 | Export daftar pegawai (Excel) | [ ] | |
| 18 | Import pegawai dari file | [!] | |gak ada fitur import
| 19 | Download template import | [!] | |gak ada fiturnya
| 20 | Lihat riwayat absensi pegawai (`/workers/{id}/attendance-history`) | [x] | |

### Manajemen User (`/users`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 21 | Lihat daftar user | [x] | |
| 22 | Tambah user baru | [x] | |
| 23 | Lihat detail user | [x] | |
| 24 | Edit user | [ x] | |
| 25 | Hapus user | [ x] | |

### Manajemen Role (`/roles`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 26 | Lihat daftar role | [ x] | |
| 27 | Tambah role baru | [ x] | |
| 28 | Edit role & permission | [ x] | |
| 29 | Hapus role | [ x] | |

### Absensi — Admin (`/attendance`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 30 | Lihat daftar absensi hari ini | [ x] | |
| 31 | Lihat daftar semua pegawai (`/attendance/workers`) | [ x] | |
| 32 | Check-in manual untuk pegawai | [ x] | |
| 33 | Check-out manual untuk pegawai | [ x] | |
| 34 | Tambah absensi manual | [ x] | |
| 35 | Edit absensi | [ x] | |
| 36 | Hapus absensi | [ x] | |
| 37 | Lihat detail absensi | [x ] | |
| 38 | Lihat riwayat absensi per pegawai (`/attendance/history/{worker}`) | [x ] | |
| 39 | Export riwayat absensi pegawai (Excel) | [x ] | |
| 40 | Export riwayat absensi harian (PDF) | [x ] | |
| 41 | Lihat statistik absensi pegawai (`/attendance/stats/{worker}`) | [ x] | |
| 42 | Export statistik absensi (Excel) | [x ] | |
| 43 | Export statistik absensi (PDF) | [x ] | |
| 44 | Laporan harian absensi (`/attendance/report/daily`) | [x ] | |
| 45 | Laporan bulanan absensi (`/attendance/report/monthly`) | [ x] | |
| 46 | Export semua absensi hari ini | [x ] | |
| 47 | Export semua absensi (umum) | [ ] | |

### Cuti — Admin (`/leaves`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 48 | Lihat daftar semua pengajuan cuti | [] | |
| 49 | Tambah pengajuan cuti baru (atas nama pegawai) | [ !] | | tidak ada
| 50 | Lihat detail pengajuan cuti | [ x] | |
| 51 | Edit pengajuan cuti | [ !] | | tidak ada edit untuk pengajuan cuti
| 52 | Approve cuti langsung dari admin | [ x] | |
| 53 | Reject cuti langsung dari admin | [ x] | |
| 54 | Cancel cuti | [ x] | |
| 55 | Hapus pengajuan cuti | [ x] | |
| 56 | Export daftar cuti (Excel) | [ x] | |
| 57 | Lihat saldo cuti pegawai (`/leaves/worker/{workerId}/balance`) | [ !] | | tidak ada fiturnya

### Lembur — Admin (`/overtimes`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 58 | Lihat daftar semua pengajuan lembur | [x ] | |
| 59 | Tambah pengajuan lembur baru | [ x] | |
| 60 | Lihat detail lembur | [x ] | |
| 61 | Edit lembur | [ x] | |
| 62 | Approve lembur | [ x] | |
| 63 | Reject lembur | [ x] | |
| 64 | Bulk approve lembur | [ x] | |tidak ada fiturnya
| 65 | Hapus lembur | [ !] | |tidak ada fiturnya
| 66 | Export daftar lembur | [ x] | |

### Dokumen Pegawai — Admin (`/worker-documents`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 67 | Lihat semua dokumen pegawai | [ x] | |
| 68 | Upload dokumen baru | [x ] | |
| 69 | Lihat detail dokumen | [x ] | |
| 70 | Edit dokumen | [ x] | |
| 71 | Verify dokumen (admin) | [x ] | |
| 72 | Reject dokumen (admin) | [ x] | |
| 73 | Download dokumen | [x ] | |
| 74 | Hapus dokumen | [ x] | |
| 75 | Lihat dokumen per pegawai (`/worker-documents/worker/{workerId}`) | [ x] | |
| 76 | Lihat dokumen akan kadaluarsa (`/worker-documents/expiring`) | [x ] | |
| 77 | Lihat dokumen sudah kadaluarsa (`/worker-documents/expired`) | [ x] | |
| 78 | Ambil tipe dokumen untuk pegawai (API endpoint) | [x ] | |

### Shift — Admin (`/master/shifts`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 79 | Lihat daftar shift | [x ] | |
| 80 | Tambah shift baru | [ x] | |
| 81 | Edit shift | [ x] | |
| 82 | Hapus shift | [ x] | |

### Jadwal Shift Pegawai (`/worker-shifts`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 83 | Lihat semua jadwal shift | [ x] | |
| 84 | Tambah jadwal shift manual | [ x] | |
| 85 | Lihat detail jadwal | [ x] | |
| 86 | Edit jadwal shift | [ x] | |
| 87 | Hapus jadwal shift | [ x] | |
| 88 | Generate jadwal otomatis (`/worker-shifts/generate`) | [ x] | |
| 89 | Lihat kalender jadwal shift (`/worker-shifts/calendar-data`) | [ x] | |
| 90 | Lihat jadwal shift per pegawai (`/worker-shifts/worker/{workerId}`) | [ x] | |

### Shift Override (`/shift-overrides`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 91 | Lihat daftar shift override | [x ] | |
| 92 | Tambah shift override | [x ] | |
| 93 | Bulk create shift override | [x ] | |
| 94 | Edit shift override | [ x] | |
| 95 | Hapus shift override | [ x] | |

### Hari Libur (`/holidays`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 96 | Lihat daftar hari libur | [x ] | |
| 97 | Tambah hari libur | [ x] | |
| 98 | Edit hari libur | [ x] | |
| 99 | Hapus hari libur | [ x] | |
| 100 | Auto-generate hari libur nasional | [x ] | |
| 101 | Bulk create hari libur | [ x] | |

### Data Master
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 102 | CRUD Departemen (`/master/departments`) | [ ] | |
| 103 | CRUD Agama (`/master/religions`) | [ ] | |
| 104 | CRUD Jenis Kelamin (`/master/genders`) | [ ] | |
| 105 | CRUD Jenis Cuti (`/master/leave-types`) | [ ] | |
| 106 | CRUD Lokasi (`/master/locations`) | [ ] | |
| 107 | CRUD Tipe Dokumen (`/master/document-types`) | [ ] | |
| 108 | CRUD Tipe Dokumen per Departemen (`/master/department-document-types`) | [ ] | |

### Laporan (`/reports`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 109 | Laporan absensi (`/reports/attendance`) dengan filter | [ x] | |
| 110 | Export laporan absensi | [x ] | |
| 111 | Laporan cuti (`/reports/leaves`) dengan filter + statistik | [ ] | |
| 112 | Export laporan cuti | [x ] | |
| 113 | Laporan lembur (`/reports/overtimes`) dengan filter + statistik | [ ] | |
| 114 | Export laporan lembur | [x ] | |
| 115 | Laporan dokumen pegawai (`/reports/worker-documents`) | [ x] | |
| 116 | Export laporan dokumen | [x ] | |

### Profil Admin
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 117 | Lihat profil (`/profile`) | [x ] | |
| 118 | Edit profil | [x ] | |
| 119 | Ganti password | [x ] | |

---

## 🏥 HR

### Dashboard
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 120 | Buka `/hr/dashboard` — semua statistik tampil | [ ] | |
| 121 | Statistik pegawai (total, aktif, tidak aktif, resign) | [ ] | |
| 122 | Statistik status kepegawaian (tetap, kontrak, probasi, magang) | [ ] | |
| 123 | Chart distribusi pegawai per departemen | [ ] | |
| 124 | Statistik absensi hari ini | [ ] | |
| 125 | Chart absensi 7 hari terakhir (present/late/absent) | [ ] | |
| 126 | Daftar pending cuti terbaru | [ ] | |
| 127 | Daftar pending lembur terbaru | [ ] | |
| 128 | Statistik dokumen (pending, terverifikasi) | [ ] | |
| 129 | Aktivitas terbaru (pegawai baru / resign) | [ ] | |
| 130 | Tabel pending checkout | [ ] | |

> **Catatan:** HR memiliki akses ke semua fitur yang sama dengan Admin di atas (routes `/attendance`, `/leaves`, `/overtimes`, dst. tidak dibatasi role HR saja, tergantung konfigurasi middleware). Uji ulang fitur Admin dengan login HR jika diperlukan.

---

## 🏢 MANAGER

### Dashboard
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 131 | Buka `/manager/dashboard` — data departemen sendiri muncul | [ ] | |
| 132 | Statistik pegawai departemen (hadir, terlambat, absen, %) | [ ] | |
| 133 | Jumlah pending approval (cuti, lembur, shift swap) | [ ] | |
| 134 | Chart absensi 7 hari terakhir departemen | [ ] | |
| 135 | Daftar 5 pending cuti departemen | [ ] | |
| 136 | Daftar 5 pending lembur departemen | [ ] | |
| 137 | Daftar 5 pending shift swap departemen | [ ] | |
| 138 | Top performers departemen bulan ini | [ ] | |
| 139 | Tabel pending checkout pegawai departemen | [ ] | |

### Approval Cuti (`/approvals/leaves`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 140 | Lihat daftar pengajuan cuti | [ ] | |
| 141 | Lihat detail pengajuan cuti pegawai | [ ] | |
| 142 | Approve cuti | [ ] | |
| 143 | Reject cuti (dengan alasan) | [ ] | |

### Approval Lembur (`/approvals/overtimes`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 144 | Lihat daftar pengajuan lembur | [ ] | |
| 145 | Lihat detail pengajuan lembur | [ ] | |
| 146 | Approve lembur | [ ] | |
| 147 | Reject lembur | [ ] | |

### Approval Perjalanan Dinas (`/approvals/business-trips`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 148 | Lihat daftar pengajuan perjalanan dinas | [ ] | |
| 149 | Lihat detail perjalanan dinas | [ ] | |
| 150 | Approve perjalanan dinas | [ ] | |
| 151 | Reject perjalanan dinas | [ ] | |
| 152 | Export daftar perjalanan dinas | [ ] | |

### Approval Dokumen Pegawai (`/approvals/documents`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 153 | Lihat daftar dokumen yang perlu diverifikasi | [ ] | |
| 154 | Lihat detail dokumen pegawai | [ ] | |
| 155 | Verify dokumen | [ ] | |
| 156 | Reject dokumen (dengan alasan) | [ ] | |

### Approval Tukar Shift (`/manager/shift-swap-approvals`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 157 | Lihat daftar pengajuan tukar shift | [ ] | |
| 158 | Lihat detail tukar shift (beserta audit log) | [ ] | |
| 159 | Approve tukar shift | [ ] | |
| 160 | Reject tukar shift | [ ] | |
| 161 | Execute (jalankan) tukar shift | [ ] | |
| 162 | Revert (batalkan) tukar shift yang sudah dieksekusi | [ ] | |

---

## 👤 PEGAWAI

### Dashboard
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 163 | Buka `/employee/dashboard` — data personal muncul | [ ] | |
| 164 | Ringkasan absensi bulan ini (hadir, terlambat, absen) | [ ] | |
| 165 | Chart absensi 7 hari terakhir | [ ] | |
| 166 | Ringkasan cuti (disetujui, pending, sisa saldo) | [ ] | |
| 167 | Ringkasan lembur | [ ] | |
| 168 | Saldo cuti per jenis cuti | [ ] | |
| 169 | Aktivitas terbaru | [ ] | |
| 170 | Notifikasi pending checkout (jika ada) | [ ] | |

### Absensi Pegawai (`/employee/attendance`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 171 | Lihat riwayat absensi sendiri | [ ] | |
| 172 | Form check-in | [ ] | |
| 173 | Proses check-in | [ ] | |
| 174 | Form check-out | [ ] | |
| 175 | Proses check-out | [ ] | |
| 176 | Lihat detail absensi tertentu | [ ] | |
| 177 | Export riwayat absensi (Excel) | [ ] | |
| 178 | Export riwayat absensi (PDF) | [ ] | |

### Pengajuan Cuti (`/employee/leaves`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 179 | Lihat daftar pengajuan cuti sendiri + ringkasan status | [ ] | |
| 180 | Buat pengajuan cuti baru | [ ] | |
| 181 | Lihat detail pengajuan cuti | [ ] | |
| 182 | Batalkan pengajuan cuti (hanya yang pending) | [ ] | |
| 183 | Export daftar cuti (Excel) | [ ] | |
| 184 | Export daftar cuti (PDF) | [ ] | |

### Pengajuan Lembur (`/employee/overtimes`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 185 | Lihat daftar pengajuan lembur sendiri + ringkasan | [ ] | |
| 186 | Buat pengajuan lembur baru | [ ] | |
| 187 | Lihat detail lembur | [ ] | |
| 188 | Batalkan lembur (hanya yang pending) | [ ] | |
| 189 | Export daftar lembur (Excel) | [ ] | |
| 190 | Export daftar lembur (PDF) | [ ] | |

### Perjalanan Dinas (`/employee/business-trips`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 191 | Lihat daftar perjalanan dinas sendiri | [ ] | |
| 192 | Buat pengajuan perjalanan dinas baru | [ ] | |
| 193 | Lihat detail perjalanan dinas | [ ] | |
| 194 | Batalkan perjalanan dinas | [ ] | |

### Dokumen Saya (`/employee/documents`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 195 | Lihat daftar dokumen sendiri | [ ] | |
| 196 | Upload dokumen baru | [ ] | |
| 197 | Lihat detail dokumen | [ ] | |
| 198 | Download dokumen sendiri | [ ] | |
| 199 | Hapus dokumen (yang belum terverifikasi) | [ ] | |

### Tukar Shift (`/employee/shift-swaps`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 200 | Lihat daftar pengajuan tukar shift | [ ] | |
| 201 | Buat permintaan tukar shift baru | [ ] | |
| 202 | Accept permintaan tukar shift dari pegawai lain | [ ] | |
| 203 | Reject permintaan tukar shift | [ ] | |
| 204 | Cancel permintaan tukar shift sendiri | [ ] | |
| 205 | Accept open shift swap | [ ] | |

### Jadwal Shift Saya (`/employee/shifts`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 206 | Lihat jadwal shift saya (`/employee/shifts`) | [ ] | |
| 207 | Lihat detail shift saya (`/employee/shifts/show`) | [ ] | |

### Kalender (`/employee/calendar`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 208 | Lihat kalender pribadi | [ ] | |
| 209 | Event kalender (absensi, cuti, lembur) tampil | [ ] | |

### Notifikasi (`/employee/notifications`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 210 | Lihat semua notifikasi | [ ] | |
| 211 | Notifikasi belum dibaca tampil | [ ] | |
| 212 | Mark notifikasi sebagai terbaca | [ ] | |
| 213 | Mark semua sebagai terbaca | [ ] | |
| 214 | Hapus notifikasi | [ ] | |

### Profil Pegawai (`/employee/profile`)
| # | Fitur | Status | Catatan |
|---|-------|--------|---------|
| 215 | Lihat profil sendiri | [ ] | |
| 216 | Edit profil (foto, data personal) | [ ] | |
| 217 | Ganti password | [ ] | |

---

## 🔄 ALUR INTEGRASI (End-to-End)

Uji alur lengkap dari sisi pegawai hingga approval:

| # | Alur | Status | Catatan |
|---|------|--------|---------|
| 218 | **Cuti penuh:** Pegawai ajukan → Manager approve → Cek status di sisi pegawai | [ ] | |
| 219 | **Cuti ditolak:** Pegawai ajukan → Manager reject → Cek status di sisi pegawai | [ ] | |
| 220 | **Lembur penuh:** Pegawai ajukan → Manager approve → Cek status | [ ] | |
| 221 | **Perjalanan dinas:** Pegawai ajukan → Manager approve → Cek status | [ ] | |
| 222 | **Dokumen:** Pegawai upload → Manager verify → Cek status "Terverifikasi" | [ ] | |
| 223 | **Dokumen ditolak:** Pegawai upload → Manager reject → Pegawai lihat alasan | [ ] | |
| 224 | **Tukar shift:** Pegawai A ajukan ke B → B accept → Manager approve → Execute | [ ] | |
| 225 | **Absensi harian:** Check-in → Kerja → Check-out → Lihat di riwayat | [ ] | |
| 226 | **Check-in terlambat:** Check-in setelah jam shift → Tampil `is_late = true` | [ ] | |

---

## 📊 RINGKASAN

| Role | Total Fitur | Selesai | Bermasalah |
|------|-------------|---------|------------|
| Auth | 7 | 0 | 0 |
| Admin | ~112 | 0 | 0 |
| HR | 11 | 0 | 0 |
| Manager | 32 | 0 | 0 |
| Pegawai | 55 | 0 | 0 |
| Integrasi | 9 | 0 | 0 |
| **Total** | **~226** | **0** | **0** |

---

*Update tabel Ringkasan saat testing berlangsung. Tandai `[x]` = OK, `[!]` = Ada masalah.*
