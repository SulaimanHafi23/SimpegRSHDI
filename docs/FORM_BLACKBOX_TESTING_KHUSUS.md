# Form Khusus Blackbox Testing - SIMPEGRS

Tanggal: 2026-04-09
Tujuan: Template dan checklist pengujian blackbox yang fokus pada variasi skenario normal, gagal, input tidak valid, dan skenario lain (boundary, keamanan, gangguan layanan).

## Aturan Pengisian
- Hasil Pengujian: Pass / Fail / Blocked
- Keterangan: isi bukti uji, bug ID, atau catatan deviasi
- Jenis Skenario:
  - Normal: alur sukses dengan input valid
  - Gagal: alur ditolak karena aturan bisnis
  - Tidak Valid: format/isi input salah
  - Boundary: pengujian nilai batas (minimum/maksimum)
  - Keamanan: akses, CSRF, otorisasi
  - Gangguan: fallback saat layanan eksternal bermasalah

## Header Form

| No | Jenis Skenario | Skenario Pengujian | Hasil yang Diharapkan | Hasil Pengujian | Keterangan |
|---|---|---|---|---|---|

## Checklist Blackbox (Siap Uji)

| No | Jenis Skenario | Skenario Pengujian | Hasil yang Diharapkan | Hasil Pengujian | Keterangan |
|---|---|---|---|---|---|
| 1 | Normal | Login dengan email dan password valid | Berhasil login dan diarahkan ke dashboard sesuai role |  |  |
| 2 | Gagal | Login dengan password salah | Login ditolak dan muncul pesan kesalahan |  |  |
| 3 | Tidak Valid | Login dengan format email tidak valid | Form ditolak oleh validasi field email |  |  |
| 4 | Gagal | Login melebihi batas percobaan | Endpoint dibatasi sementara (throttle) |  |  |
| 5 | Normal | Logout dari sistem | Sesi berakhir dan kembali ke halaman awal/login |  |  |
| 6 | Keamanan | Akses halaman internal tanpa login | Sistem redirect ke halaman login |  |  |
| 7 | Keamanan | Employee akses menu khusus HR | Akses ditolak (403/unauthorized) |  |  |
| 8 | Normal | Root route untuk user Manager | Redirect otomatis ke manager dashboard |  |  |
| 9 | Normal | HR membuka dashboard HR | Widget dan statistik tampil sesuai hak akses |  |  |
| 10 | Gagal | User tanpa izin membuka audit log | Akses ditolak |  |  |
| 11 | Normal | Tambah data pegawai dengan data lengkap valid | Data tersimpan dan muncul di daftar |  |  |
| 12 | Tidak Valid | Tambah pegawai tanpa field wajib (nama/NIP) | Simpan ditolak, pesan validasi tampil |  |  |
| 13 | Gagal | Tambah pegawai dengan NIP duplikat | Simpan ditolak karena konflik data unik |  |  |
| 14 | Normal | Import pegawai dengan template valid | Import berhasil dan ringkasan hasil tampil |  |  |
| 15 | Tidak Valid | Import pegawai dengan file bukan format yang didukung | Import ditolak dengan pesan error format |  |  |
| 16 | Gagal | Import pegawai dengan kolom wajib hilang | Import ditolak, laporan validasi ditampilkan |  |  |
| 17 | Normal | Export data pegawai | File berhasil diunduh dan data sesuai filter |  |  |
| 18 | Normal | Employee check-in dengan data valid | Check-in berhasil dan waktu tersimpan |  |  |
| 19 | Gagal | Employee check-in dua kali di hari yang sama (tanpa checkout) | Aksi ditolak sesuai aturan absensi |  |  |
| 20 | Tidak Valid | Check-in tanpa data wajib (misalnya foto jika wajib) | Check-in ditolak oleh validasi |  |  |
| 21 | Normal | Employee check-out setelah check-in valid | Check-out berhasil dan durasi kerja terhitung |  |  |
| 22 | Gagal | Check-out tanpa check-in sebelumnya | Aksi ditolak dan pesan informatif tampil |  |  |
| 23 | Normal | Admin melakukan check-in manual untuk pegawai | Data absensi tersimpan sebagai entri admin |  |  |
| 24 | Normal | Lihat histori absensi per pegawai dengan filter tanggal valid | Data histori tampil sesuai filter |  |  |
| 25 | Boundary | Filter histori dengan tanggal awal = tanggal akhir | Sistem menampilkan data hari tersebut secara benar |  |  |
| 26 | Gagal | Filter histori dengan tanggal awal > tanggal akhir | Sistem menolak dan menampilkan validasi periode |  |  |
| 27 | Normal | Buat master shift dengan jam valid | Shift tersimpan dan dapat dipilih saat assignment |  |  |
| 28 | Tidak Valid | Buat shift dengan format jam tidak valid | Simpan ditolak oleh validasi |  |  |
| 29 | Boundary | Buat shift dengan jam mulai sama dengan jam selesai | Ditolak sesuai aturan bisnis shift |  |  |
| 30 | Normal | Assign shift ke pegawai pada tanggal valid | Jadwal pegawai terbentuk sesuai input |  |  |
| 31 | Gagal | Assign dua shift bentrok ke pegawai pada hari sama | Sistem menolak assignment bentrok |  |  |
| 32 | Normal | Generate jadwal shift untuk rentang periode valid | Jadwal massal berhasil dibuat |  |  |
| 33 | Gagal | Generate jadwal pada rentang tidak valid | Proses ditolak dengan pesan error yang jelas |  |  |
| 34 | Normal | Ajukan shift swap antar pegawai dengan syarat valid | Pengajuan tersimpan dengan status pending |  |  |
| 35 | Gagal | Ajukan shift swap saat melanggar lead time | Pengajuan ditolak sesuai aturan lead time |  |  |
| 36 | Gagal | Ajukan shift swap yang melanggar rest period | Pengajuan ditolak sesuai aturan rest period |  |  |
| 37 | Normal | Partner menerima shift swap | Status pengajuan berubah ke tahap berikutnya |  |  |
| 38 | Gagal | Partner menolak shift swap | Status berubah rejected dan alasan tercatat |  |  |
| 39 | Normal | Manager approve shift swap lintas departemen | Status approval manager tercatat |  |  |
| 40 | Normal | Eksekusi shift swap yang sudah approved | Data shift berpindah dan audit log terbentuk |  |  |
| 41 | Normal | Pegawai ajukan cuti dengan data valid | Pengajuan cuti tersimpan dengan status pending |  |  |
| 42 | Tidak Valid | Ajukan cuti dengan tanggal selesai sebelum tanggal mulai | Ditolak oleh validasi tanggal |  |  |
| 43 | Gagal | Ajukan cuti saat saldo tidak mencukupi | Ditolak sesuai aturan kuota/saldo |  |  |
| 44 | Boundary | Ajukan cuti tepat pada sisa saldo terakhir | Diterima jika sesuai aturan, saldo menjadi nol |  |  |
| 45 | Normal | Approver menyetujui cuti | Status approved dan notifikasi terkirim |  |  |
| 46 | Normal | Approver menolak cuti dengan alasan | Status rejected, alasan tersimpan |  |  |
| 47 | Normal | Pegawai ajukan perjalanan dinas valid | Data tersimpan dan masuk antrian approval |  |  |
| 48 | Tidak Valid | Ajukan perjalanan dinas tanpa tujuan/periode | Pengajuan ditolak oleh validasi field wajib |  |  |
| 49 | Gagal | Pegawai membatalkan pengajuan yang sudah approved | Sistem menolak cancel jika status tidak memenuhi syarat |  |  |
| 50 | Normal | Upload dokumen pegawai dengan format dan ukuran valid | Dokumen tersimpan dan dapat dipreview/download |  |  |
| 51 | Tidak Valid | Upload dokumen dengan ekstensi tidak diizinkan | Upload ditolak oleh validasi file |  |  |
| 52 | Boundary | Upload file pada ukuran maksimum yang diizinkan | Upload tetap berhasil |  |  |
| 53 | Gagal | Upload file melebihi ukuran maksimum | Upload ditolak dengan pesan batas ukuran |  |  |
| 54 | Normal | Verifikasi dokumen oleh approver | Status berubah menjadi verified |  |  |
| 55 | Normal | Reject dokumen oleh approver dengan alasan | Status menjadi rejected dan alasan tercatat |  |  |
| 56 | Normal | Tambah holiday manual dengan tanggal valid | Holiday tersimpan dan tampil pada daftar |  |  |
| 57 | Gagal | Tambah holiday pada tanggal yang sudah ada (duplikat) | Sistem menolak atau mengabaikan duplikasi sesuai aturan |  |  |
| 58 | Normal | Bulk create holiday dengan data valid | Data holiday massal tersimpan |  |  |
| 59 | Normal | Buka notifikasi unread | Hanya notifikasi belum dibaca yang tampil |  |  |
| 60 | Normal | Mark all notifications as read | Semua notifikasi user berubah status read |  |  |
| 61 | Keamanan | Submit form POST tanpa CSRF token | Request ditolak middleware CSRF |  |  |
| 62 | Keamanan | Akses endpoint laporan oleh role tanpa izin | Akses ditolak sesuai role/permission |  |  |
| 63 | Normal | Akses endpoint world-time saat API eksternal normal | Response sukses dengan sumber worldtimeapi |  |  |
| 64 | Gangguan | Akses endpoint world-time saat API eksternal gagal | Response sukses fallback menggunakan server time |  |  |
| 65 | Normal | Akses URL yang tidak terdaftar route | Halaman 404 custom tampil |  |  |
| 66 | Normal | Akses /storage/{path} untuk file valid | File ditampilkan/diunduh sesuai request |  |  |
| 67 | Gagal | Akses /storage/{path} untuk file tidak ada | Sistem mengembalikan 404 |  |  |

## Template Kosong Tambahan (Untuk Penambahan Kasus)

| No | Jenis Skenario | Skenario Pengujian | Hasil yang Diharapkan | Hasil Pengujian | Keterangan |
|---|---|---|---|---|---|
| 68 |  |  |  |  |  |
| 69 |  |  |  |  |  |
| 70 |  |  |  |  |  |
| 71 |  |  |  |  |  |
| 72 |  |  |  |  |  |
| 73 |  |  |  |  |  |
| 74 |  |  |  |  |  |
| 75 |  |  |  |  |  |

## Ringkasan Cakupan Uji
- Autentikasi dan sesi
- Otorisasi role/permission
- Manajemen pegawai
- Absensi
- Shift dan shift swap
- Cuti
- Perjalanan dinas
- Dokumen pegawai
- Master data dan holiday
- Notifikasi
- Laporan, keamanan, fallback
