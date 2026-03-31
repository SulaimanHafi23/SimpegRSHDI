# Index - Sequence Diagram Sistem SIMPEG

**Total Diagrams:** 125

Dokumen ini mengorganisir semua sequence diagram berdasarkan struktur Use Case dari dokumen [USE-CASE-SISTEM-BERDASARKAN-FITUR.md](../USE-CASE-SISTEM-BERDASARKAN-FITUR.md).

---

## 1. Autentikasi (5 diagrams)

**Parent Module:** [Autentikasi.mermaid](Autentikasi.mermaid)

- [Autentikasi-Login.mermaid](Autentikasi-Login.mermaid) - Login ke sistem (<<include>>)
- [Autentikasi-Logout.mermaid](Autentikasi-Logout.mermaid) - Logout dari sistem (<<include>>)
- [Autentikasi-LupaPassword.mermaid](Autentikasi-LupaPassword.mermaid) - Forgot Password (<<extend>>)
- [Autentikasi-ResetPassword.mermaid](Autentikasi-ResetPassword.mermaid) - Reset Password (<<extend>>)

---

## 2. Portal Pegawai (23 diagrams)

**Parent Module:** Merged dengan subsections berikut

### 2.1 Absensi Saya
- [PortalPegawai-AbsensiSaya.mermaid](PortalPegawai-AbsensiSaya.mermaid) - Parent (<<include>>)
- [KelolaAbsensi-CheckInPegawai.mermaid](KelolaAbsensi-CheckInPegawai.mermaid) - Check-In Pegawai (<<extend>>)
- [KelolaAbsensi-CheckOutPegawai.mermaid](KelolaAbsensi-CheckOutPegawai.mermaid) - Check-Out Pegawai (<<extend>>)
- [KelolaAbsensi-EksporAbsensi.mermaid](KelolaAbsensi-EksporAbsensi.mermaid) - Export Absensi Pribadi (<<extend>>)

### 2.2 Jadwal Kerja
- [PortalPegawai-JadwalKerja.mermaid](PortalPegawai-JadwalKerja.mermaid) - Lihat Jadwal Kerja (<<include>>)

### 2.3 Tukar Shift
- [KelolaTukarShift.mermaid](KelolaTukarShift.mermaid) - Parent (<<include>>)
- [KelolaTukarShift-AjukanTukarShift.mermaid](KelolaTukarShift-AjukanTukarShift.mermaid) - Ajukan Tukar Shift (<<include>>)
- [KelolaTukarShift-LihatDetailTukarShift.mermaid](KelolaTukarShift-LihatDetailTukarShift.mermaid) - Lihat Detail (<<include>>)
- [KelolaTukarShift-SetujuiPermintaanTukarShift.mermaid](KelolaTukarShift-SetujuiPermintaanTukarShift.mermaid) - Setujui/Tolak/Batalkan (<<extend>>)
- [KelolaTukarShift-TolakPermintaanTukarShift.mermaid](KelolaTukarShift-TolakPermintaanTukarShift.mermaid) - Tolak Permintaan (<<extend>>)

### 2.4 Cuti Saya
- [PengajuanPermintaanCuti.mermaid](PengajuanPermintaanCuti.mermaid) - Parent (<<include>>)
- [PengajuanPermintaanCuti-IsiFormPengajuan.mermaid](PengajuanPermintaanCuti-IsiFormPengajuan.mermaid) - Ajukan Cuti (<<include>>)
- [PengajuanPermintaanCuti-BatalkanPengajuan.mermaid](PengajuanPermintaanCuti-BatalkanPengajuan.mermaid) - Batalkan Pengajuan (<<extend>>)
- [PengajuanPermintaanCuti-EksportPengajuanPermintaanCuti.mermaid](PengajuanPermintaanCuti-EksportPengajuanPermintaanCuti.mermaid) - Export Cuti (<<extend>>)

### 2.5 Perjalanan Dinas Saya
- [PengajuanPerjalananDinas.mermaid](PengajuanPerjalananDinas.mermaid) - Parent (<<include>>)
- [PengajuanPerjalananDinas-IsiFormPengajuan.mermaid](PengajuanPerjalananDinas-IsiFormPengajuan.mermaid) - Ajukan Perjalanan Dinas (<<include>>)
- [PengajuanPerjalananDinas-BatalkanPengajuan.mermaid](PengajuanPerjalananDinas-BatalkanPengajuan.mermaid) - Batalkan Pengajuan (<<extend>>)
- [PengajuanPerjalananDinas-EksportPengajuanPerjalananDinas.mermaid](PengajuanPerjalananDinas-EksportPengajuanPerjalananDinas.mermaid) - Export Perjalanan Dinas (<<extend>>)

### 2.6 Dokumen Saya
- [KelolaDokumen.mermaid](KelolaDokumen.mermaid) - Parent (<<include>>)
- [KelolaDokumen-KirimDokumen.mermaid](KelolaDokumen-KirimDokumen.mermaid) - Upload Dokumen (<<include>>)
- [KelolaDokumen-LihatDetailDokumen.mermaid](KelolaDokumen-LihatDetailDokumen.mermaid) - Lihat/Download Dokumen (<<include>>)
- [KelolaDokumen-DownloadDokumen.mermaid](KelolaDokumen-DownloadDokumen.mermaid) - Download Dokumen (<<extend>>)
- [KelolaDokumen-HapusDokumen.mermaid](KelolaDokumen-HapusDokumen.mermaid) - Hapus Dokumen (<<extend>>)

### 2.7 Kalender Saya (Read-Only)
- [PortalPegawai-KalenderSaya.mermaid](PortalPegawai-KalenderSaya.mermaid) - Lihat Kalender Event Kerja (<<include>>)

### 2.8 Profil Saya
- [Profil.mermaid](Profil.mermaid) - Parent Module
- [Profil-LihatProfile.mermaid](Profil-LihatProfile.mermaid) - Lihat Profile (<<include>>)
- [Profil-EditProfile.mermaid](Profil-EditProfile.mermaid) - Update Profile (<<include>>)
- [Profil-UbahPassword.mermaid](Profil-UbahPassword.mermaid) - Update Password (<<extend>>)
- [Profil-GantiFotoProfile.mermaid](Profil-GantiFotoProfile.mermaid) - Update Foto Profile (<<extend>>)

### 2.9 Notifikasi Saya
- [PortalPegawai-NotifikasiSaya.mermaid](PortalPegawai-NotifikasiSaya.mermaid) - Lihat Notifikasi (<<include>>)
- [PortalPegawai-TandaiNotifikasiSudahDibaca.mermaid](PortalPegawai-TandaiNotifikasiSudahDibaca.mermaid) - Mark as Read (<<extend>>)
- [PortalPegawai-TandaiSemuaNotifikasiSudahDibaca.mermaid](PortalPegawai-TandaiSemuaNotifikasiSudahDibaca.mermaid) - Mark All as Read (<<extend>>)
- [PortalPegawai-HapusNotifikasi.mermaid](PortalPegawai-HapusNotifikasi.mermaid) - Delete Notifikasi (<<extend>>)

---

## 3. Persetujuan (16 diagrams)

**Parent Module:** Persetujuan (Approval Management)

### 3.1 Persetujuan Cuti
- [KelolaPermintaanCuti.mermaid](KelolaPermintaanCuti.mermaid) - Parent (<<include>>)
- [KelolaPermintaanCuti-LihatDetailPermintaanCuti.mermaid](KelolaPermintaanCuti-LihatDetailPermintaanCuti.mermaid) - Lihat Detail Cuti (<<include>>)
- [KelolaPermintaanCuti-SetujuiPermintaanCuti.mermaid](KelolaPermintaanCuti-SetujuiPermintaanCuti.mermaid) - Setujui Cuti (<<extend>>)
- [KelolaPermintaanCuti-TolakPermintaanCuti.mermaid](KelolaPermintaanCuti-TolakPermintaanCuti.mermaid) - Tolak Cuti (<<extend>>)

### 3.2 Persetujuan Tukar Shift
- [KelolaPerjalananDinas.mermaid](KelolaPerjalananDinas.mermaid) - Parent (lihat data Tukar Shift untuk approval)
- [KelolaPerjalananDinas-LihatDetailPermintaanPerjalananDinas.mermaid](KelolaPerjalananDinas-LihatDetailPermintaanPerjalananDinas.mermaid) - Lihat Detail
- [KelolaTukarShift-SetujuiPermintaanTukarShift.mermaid](KelolaTukarShift-SetujuiPermintaanTukarShift.mermaid) - Setujui/Tolak/Execute/Revert Tukar Shift
- [KelolaTukarShift-EksekusiTukarShift.mermaid](KelolaTukarShift-EksekusiTukarShift.mermaid) - Execute Tukar Shift (<<extend>>)
- [KelolaTukarShift-RevertTukarShift.mermaid](KelolaTukarShift-RevertTukarShift.mermaid) - Revert Tukar Shift (<<extend>>)
- [KelolaTukarShift-EksportRiwayatTukarShift.mermaid](KelolaTukarShift-EksportRiwayatTukarShift.mermaid) - Export Riwayat Tukar Shift (<<extend>>)

### 3.3 Persetujuan Dokumen
- [PersetujuanDokumen.mermaid](PersetujuanDokumen.mermaid) - Parent (<<include>>)
- [PersetujuanDokumen-LihatDetailDokumen.mermaid](PersetujuanDokumen-LihatDetailDokumen.mermaid) - Lihat Detail Dokumen (<<include>>)
- [PersetujuanDokumen-VerifikasiDokumen.mermaid](PersetujuanDokumen-VerifikasiDokumen.mermaid) - Verifikasi Dokumen (<<extend>>)
- [PersetujuanDokumen-TolakDokumen.mermaid](PersetujuanDokumen-TolakDokumen.mermaid) - Tolak Dokumen (<<extend>>)

### 3.4 Persetujuan Perjalanan Dinas
- [KelolaPerjalananDinas.mermaid](KelolaPerjalananDinas.mermaid) - Parent (<<include>>)
- [KelolaPerjalananDinas-LihatDetailPermintaanPerjalananDinas.mermaid](KelolaPerjalananDinas-LihatDetailPermintaanPerjalananDinas.mermaid) - Lihat Detail (<<include>>)
- [KelolaPerjalananDinas-SetujuiPermintaanPerjalananDinas.mermaid](KelolaPerjalananDinas-SetujuiPermintaanPerjalananDinas.mermaid) - Setujui Perjalanan Dinas (<<extend>>)
- [KelolaPerjalananDinas-TolakPermintaanPerjalananDinas.mermaid](KelolaPerjalananDinas-TolakPermintaanPerjalananDinas.mermaid) - Tolak Perjalanan Dinas (<<extend>>)
- [KelolaPerjalananDinas-EksportRiwayatPerjalananDinas.mermaid](KelolaPerjalananDinas-EksportRiwayatPerjalananDinas.mermaid) - Export Perjalanan Dinas (<<extend>>)

---

## 4. Manajerial (27 diagrams)

**Parent Module:** Manajerial (HR & Manager Operations)

### 4.1 Kelola Pegawai
- [KelolaPegawai.mermaid](KelolaPegawai.mermaid) - Parent (<<include>>)
- [KelolaPegawai-Tambah.mermaid](KelolaPegawai-Tambah.mermaid) - Create Pegawai (<<include>>)
- [KelolaPegawai-Lihat.mermaid](KelolaPegawai-Lihat.mermaid) - Read Pegawai (<<include>>)
- [KelolaPegawai-Ubah.mermaid](KelolaPegawai-Ubah.mermaid) - Update Pegawai (<<include>>)
- [KelolaPegawai-Hapus.mermaid](KelolaPegawai-Hapus.mermaid) - Delete Pegawai (<<include>>)

### 4.2 Kelola Absensi Admin
- [KelolaAbsensi.mermaid](KelolaAbsensi.mermaid) - Parent (<<include>>)
- [KelolaAbsensi-CheckInOlehAdmin.mermaid](KelolaAbsensi-CheckInOlehAdmin.mermaid) - Check-In by Admin (<<extend>>)
- [KelolaAbsensi-CheckOutOlehAdmin.mermaid](KelolaAbsensi-CheckOutOlehAdmin.mermaid) - Check-Out by Admin (<<extend>>)
- [KelolaAbsensi-EksporAbsensi.mermaid](KelolaAbsensi-EksporAbsensi.mermaid) - Export Absensi (<<extend>>)

### 4.3 Kelola Jadwal Pegawai
- [KelolaJadwalPegawai.mermaid](KelolaJadwalPegawai.mermaid) - Parent (<<include>>)
- [KelolaJadwalPegawai-TambahJadwal.mermaid](KelolaJadwalPegawai-TambahJadwal.mermaid) - Create Schedule (<<include>>)
- [KelolaJadwalPegawai-LihatJadwal.mermaid](KelolaJadwalPegawai-LihatJadwal.mermaid) - Read Schedule (<<include>>)
- [KelolaJadwalPegawai-UbahJadwal.mermaid](KelolaJadwalPegawai-UbahJadwal.mermaid) - Update Schedule (<<include>>)
- [KelolaJadwalPegawai-HapusJadwal.mermaid](KelolaJadwalPegawai-HapusJadwal.mermaid) - Delete Schedule (<<include>>)

### 4.4 Kelola Shift Override
- [KelolaShiftOverride.mermaid](KelolaShiftOverride.mermaid) - Parent (<<include>>)
- [KelolaShiftOverride-TambahShiftOverride.mermaid](KelolaShiftOverride-TambahShiftOverride.mermaid) - Create Override (<<include>>)
- [KelolaShiftOverride-LihatShiftOverride.mermaid](KelolaShiftOverride-LihatShiftOverride.mermaid) - Read Override (<<include>>)
- [KelolaShiftOverride-UbahShiftOverride.mermaid](KelolaShiftOverride-UbahShiftOverride.mermaid) - Update Override (<<include>>)
- [KelolaShiftOverride-HapusShiftOverride.mermaid](KelolaShiftOverride-HapusShiftOverride.mermaid) - Delete Override (<<include>>)
- [KelolaShiftOverride-BulkTambahShiftOverride.mermaid](KelolaShiftOverride-BulkTambahShiftOverride.mermaid) - Bulk Create (<<extend>>)

### 4.5 Kelola Dokumen Pegawai
- [KelolaDokumen.mermaid](KelolaDokumen.mermaid) - Parent (lihat juga Portal Pegawai > Dokumen Saya)
- [KelolaDokumen-VerifikasiDokumen.mermaid](KelolaDokumen-VerifikasiDokumen.mermaid) - Verify Document (<<extend>>)

---

## 5. Data Master (19 diagrams)

**Parent Module:** Data Master (Referensi & Master Data)

### 5.1 Departemen
- [KelolaDepartemen.mermaid](KelolaDepartemen.mermaid) - Parent (<<include>>)
- [KelolaDepartemen-TambahDepartemen.mermaid](KelolaDepartemen-TambahDepartemen.mermaid) - Create (<<include>>)
- [KelolaDepartemen-LihatDepartemen.mermaid](KelolaDepartemen-LihatDepartemen.mermaid) - Read (<<include>>)
- [KelolaDepartemen-UbahDepartemen.mermaid](KelolaDepartemen-UbahDepartemen.mermaid) - Update (<<include>>)
- [KelolaDepartemen-HapusDepartemen.mermaid](KelolaDepartemen-HapusDepartemen.mermaid) - Delete (<<include>>)

### 5.2 Shift
- [KelolaShift.mermaid](KelolaShift.mermaid) - Parent (<<include>>)
- [KelolaShift-TambahShift.mermaid](KelolaShift-TambahShift.mermaid) - Create (<<include>>)
- [KelolaShift-LihatShift.mermaid](KelolaShift-LihatShift.mermaid) - Read (<<include>>)
- [KelolaShift-UpdateShift.mermaid](KelolaShift-UpdateShift.mermaid) - Update (<<include>>)
- [KelolaShift-DeleteShift.mermaid](KelolaShift-DeleteShift.mermaid) - Delete (<<include>>)

### 5.3 Jenis Cuti
- [KelolaJenisCuti.mermaid](KelolaJenisCuti.mermaid) - Parent (<<include>>)
- [KelolaJenisCuti-TambahJenisCuti.mermaid](KelolaJenisCuti-TambahJenisCuti.mermaid) - Create (<<include>>)
- [KelolaJenisCuti-LihatJenisCuti.mermaid](KelolaJenisCuti-LihatJenisCuti.mermaid) - Read (<<include>>)
- [KelolaJenisCuti-UpdateJenisCuti.mermaid](KelolaJenisCuti-UpdateJenisCuti.mermaid) - Update (<<include>>)
- [KelolaJenisCuti-DeleteJenisCuti.mermaid](KelolaJenisCuti-DeleteJenisCuti.mermaid) - Delete (<<include>>)

### 5.4 Jenis Dokumen
- [KelolaJenisDokumen.mermaid](KelolaJenisDokumen.mermaid) - Parent (<<include>>)
- [KelolaJenisDokumen-TambahJenisDokumen.mermaid](KelolaJenisDokumen-TambahJenisDokumen.mermaid) - Create (<<include>>)
- [KelolaJenisDokumen-LihatJenisDokumen.mermaid](KelolaJenisDokumen-LihatJenisDokumen.mermaid) - Read (<<include>>)
- [KelolaJenisDokumen-UbahJenisDokumen.mermaid](KelolaJenisDokumen-UbahJenisDokumen.mermaid) - Update (<<include>>)
- [KelolaJenisDokumen-HapusJenisDokumen.mermaid](KelolaJenisDokumen-HapusJenisDokumen.mermaid) - Delete (<<include>>)

### 5.5 Mapping Dokumen Posisi
- [KelolaMappingDokumenPosisi.mermaid](KelolaMappingDokumenPosisi.mermaid) - Parent (<<include>>)
- [KelolaMappingDokumenPosisi-TambahMapping.mermaid](KelolaMappingDokumenPosisi-TambahMapping.mermaid) - Create (<<include>>)
- [KelolaMappingDokumenPosisi-LihatMapping.mermaid](KelolaMappingDokumenPosisi-LihatMapping.mermaid) - Read (<<include>>)
- [KelolaMappingDokumenPosisi-UbahMapping.mermaid](KelolaMappingDokumenPosisi-UbahMapping.mermaid) - Update (<<include>>)
- [KelolaMappingDokumenPosisi-HapusMapping.mermaid](KelolaMappingDokumenPosisi-HapusMapping.mermaid) - Delete (<<include>>)

---

## 6. Hari Libur (7 diagrams)

**Parent Module:** [KelolaHariLibur.mermaid](KelolaHariLibur.mermaid)

- [KelolaHariLibur-TambahHariLibur.mermaid](KelolaHariLibur-TambahHariLibur.mermaid) - Create Hari Libur (<<include>>)
- [KelolaHariLibur-LihatHariLibur.mermaid](KelolaHariLibur-LihatHariLibur.mermaid) - Read Hari Libur (<<include>>)
- [KelolaHariLibur-UbahHariLibur.mermaid](KelolaHariLibur-UbahHariLibur.mermaid) - Update Hari Libur (<<include>>)
- [KelolaHariLibur-HapusHariLibur.mermaid](KelolaHariLibur-HapusHariLibur.mermaid) - Delete Hari Libur (<<include>>)
- [KelolaHariLibur-BulkTambahHariLibur.mermaid](KelolaHariLibur-BulkTambahHariLibur.mermaid) - Bulk Create (<<extend>>)
- [KelolaHariLibur-AutoGenerateHariLibur.mermaid](KelolaHariLibur-AutoGenerateHariLibur.mermaid) - Auto Generate (<<extend>>)

---

## 7. Laporan (5 diagrams)

**Parent Module:** [Laporan.mermaid](Laporan.mermaid)

- [Laporan-LaporanAbsensi.mermaid](Laporan-LaporanAbsensi.mermaid) - Laporan Absensi (Read + Export)
- [Laporan-LaporanCuti.mermaid](Laporan-LaporanCuti.mermaid) - Laporan Cuti (Read + Export)
- [Laporan-LaporanDokumen.mermaid](Laporan-LaporanDokumen.mermaid) - Laporan Dokumen (Read + Export)
- [Laporan-LaporanTukarShift.mermaid](Laporan-LaporanTukarShift.mermaid) - Laporan Tukar Shift (Read + Export)
- [Laporan-PerjalananDinas.mermaid](Laporan-PerjalananDinas.mermaid) - Laporan Perjalanan Dinas (Read + Export)

---

## 8. Administrasi Sistem (6 diagrams)

**Parent Module:** Administrasi Sistem

### 8.1 Kelola Role
- [KelolaRole.mermaid](KelolaRole.mermaid) - Parent (<<include>>)
- [KelolaRole-TambahRole.mermaid](KelolaRole-TambahRole.mermaid) - Create Role (<<include>>)
- [KelolaRole-LihatRole.mermaid](KelolaRole-LihatRole.mermaid) - Read Role (<<include>>)
- [KelolaRole-UbahRole.mermaid](KelolaRole-UbahRole.mermaid) - Update Role (<<include>>)
- [KelolaRole-HapusRole.mermaid](KelolaRole-HapusRole.mermaid) - Delete Role (<<include>>)

### 8.2 Kelola User
- [KelolaPengguna.mermaid](KelolaPengguna.mermaid) - Parent (<<include>>)
- [KelolaPengguna-TambahPengguna.mermaid](KelolaPengguna-TambahPengguna.mermaid) - Create User (<<include>>)
- [KelolaPengguna-LihatPengguna.mermaid](KelolaPengguna-LihatPengguna.mermaid) - Read User (<<include>>)
- [KelolaPengguna-UpdatePengguna.mermaid](KelolaPengguna-UpdatePengguna.mermaid) - Update User (<<include>>)
- [KelolaPengguna-DeletePengguna.mermaid](KelolaPengguna-DeletePengguna.mermaid) - Delete User (<<include>>)
- [KelolaPengguna-MenetapkanRole.mermaid](KelolaPengguna-MenetapkanRole.mermaid) - Assign Role (<<extend>>)
- [KelolaPengguna-ResetPassword.mermaid](KelolaPengguna-ResetPassword.mermaid) - Reset Password (<<extend>>)
- [KelolaPengguna-NonaktifkanPengguna.mermaid](KelolaPengguna-NonaktifkanPengguna.mermaid) - Deactivate User (<<extend>>)

### 8.3 Audit Log
- [AuditLog.mermaid](AuditLog.mermaid) - Read Audit Log (Read-only, <<include>>)

---

## Ringkasan Struktur

| Module | Total | Parent | Children |
|--------|-------|--------|----------|
| Autentikasi | 5 | 1 | 4 |
| Portal Pegawai | 23 | Multiple | Multiple |
| Persetujuan | 16 | 4 | 12 |
| Manajerial | 27 | 5 | 22 |
| Data Master | 19 | 5 | 14 |
| Hari Libur | 7 | 1 | 6 |
| Laporan | 5 | 1 | 4 |
| Administrasi Sistem | 16 | 3 | 13 |
| **TOTAL** | **118** | **Various** | **Various** |

---

## Konvensi Naming

- **Parent Module**: `{NamaModule}.mermaid` (e.g., `Autentikasi.mermaid`, `Laporan.mermaid`)
- **Child Diagrams**: `{NamaModule}-{NamaAction}.mermaid` (e.g., `Autentikasi-Login.mermaid`, `KelolaHariLibur-TambahHariLibur.mermaid`)
- **Relationships**: 
  - `<<include>>` = Selalu bagian dari parent (essential use case)
  - `<<extend>>` = Skenario tambahan/opsional berbasis kondisi atau role

---

## Catatan Penting

1. **Konsistensi Penamaan**: Semua file sequence diagram sekarang mengikuti konvensi naming yang konsisten dengan struktur use case di dokumen utama.

2. **File Baru Ditambahkan** (43 diagrams):
   - Notifikasi Saya (3 diagrams)
   - Kelola Hari Libur (7 diagrams)
   - Audit Log (1 diagram)
   - Kelola Role (5 diagrams)
   - Kelola Jenis Dokumen (5 diagrams)
   - Kelola Mapping Dokumen Posisi (5 diagrams)
   - Persetujuan Dokumen (4 diagrams)
   - Kelola Jadwal Pegawai (5 diagrams)
   - Kelola Shift Override (6 diagrams)
   - Lupa/Reset Password (2 diagrams)

3. **File Direname** (untuk standardisasi):
   - `BuatLaporan-*` → `Laporan-*` (5 files)
   - `KelolaDepartmen*` → `KelolaDepartemen*` (5 files)

4. **Akses Cepat**: Gunakan Ctrl+F untuk mencari diagram berdasarkan nama use case atau action.

---

**Last Updated**: 2026-03-30  
**Total Diagrams**: 118  
**Status**: ✅ Complete - Semua use case dari dokumen USE-CASE-SISTEM-BERDASARKAN-FITUR.md sudah memiliki sequence diagram.

