# Class Diagram SIMPEG (Sistem Informasi Kepegawaian)

Berikut adalah representasi *Class Diagram* (berbahasa Indonesia) untuk arsitektur database/model di sistem SIMPEG. Diagram ini sudah mencakup atribut dan metode operasional (Create, Read, Update, Delete) serta operasi bisnis spesifik masing-masing entitas seolah direpresentasikan mulai dari Controller/Service hingga Model.

```mermaid
classDiagram
    class Pengguna {
        +BigInt id
        +String nama
        +String email
        +String kata_sandi
        +DateTime email_diverifikasi_pada
        +Boolean aktif
        +create(data) Pengguna
        +read(id) Pengguna
        +update(id, data) Boolean
        +delete(id) Boolean
        +login(email, password) Token
        +logout() Boolean
        +pegawai() Pegawai
        +peran() Collection
    }

    class Peran {
        +BigInt id
        +String nama
        +String guard_name
        +create(data) Peran
        +read(id) Peran
        +update(id, data) Boolean
        +delete(id) Boolean
        +pengguna() Collection
    }

    class Pegawai {
        +BigInt id
        +BigInt pengguna_id
        +BigInt departemen_id
        +String nip
        +String nama_depan
        +String nama_belakang
        +String jenis_kelamin
        +String agama
        +String jabatan
        +Date tanggal_bergabung
        +create(data) Pegawai
        +read(id) Pegawai
        +update(id, data) Boolean
        +delete(id) Boolean
        +assignShift(shiftId, tanggal) Boolean
        +pengguna() Pengguna
        +departemen() Departemen
        +presensi() Collection
        +jadwalShift() Collection
        +pengajuanCuti() Collection
        +perjalananDinas() Collection
    }

    class Departemen {
        +BigInt id
        +String nama
        +String kode
        +String deskripsi
        +create(data) Departemen
        +read(id) Departemen
        +update(id, data) Boolean
        +delete(id) Boolean
        +pegawai() Collection
    }

    class Shift {
        +BigInt id
        +String nama
        +Time waktu_mulai
        +Time waktu_selesai
        +Boolean aktif
        +create(data) Shift
        +read(id) Shift
        +update(id, data) Boolean
        +delete(id) Boolean
        +pegawai() Collection
    }

    class JadwalPegawai {
        +BigInt id
        +BigInt pegawai_id
        +BigInt shift_id
        +Date tanggal_berlaku
        +create(data) JadwalPegawai
        +read(id) JadwalPegawai
        +update(id, data) Boolean
        +delete(id) Boolean
    }

    class Presensi {
        +BigInt id
        +BigInt pegawai_id
        +Date tanggal_kerja
        +Time waktu_masuk
        +Time waktu_keluar
        +String status
        +String catatan
        +create(data) Presensi
        +read(id) Presensi
        +update(id, data) Boolean
        +delete(id) Boolean
        +checkIn(pegawaiId, waktu) Boolean
        +checkOut(pegawaiId, waktu) Boolean
        +pegawai() Pegawai
        +foto() Collection
    }

    class FotoPresensi {
        +BigInt id
        +BigInt presensi_id
        +String path_foto
        +String jenis
        +create(data) FotoPresensi
        +delete(id) Boolean
        +presensi() Presensi
    }

    class PengajuanCuti {
        +BigInt id
        +BigInt pegawai_id
        +BigInt jenis_cuti_id
        +Date tanggal_mulai
        +Date tanggal_selesai
        +String alasan
        +String status
        +create(data) PengajuanCuti
        +read(id) PengajuanCuti
        +update(id, data) Boolean
        +delete(id) Boolean
        +approve() Boolean
        +reject() Boolean
        +pegawai() Pegawai
        +jenisCuti() JenisCuti
    }

    class JenisCuti {
        +BigInt id
        +String nama
        +Int jatah_hari_default
        +Boolean aktif
        +create(data) JenisCuti
        +read(id) JenisCuti
        +update(id, data) Boolean
        +delete(id) Boolean
        +pengajuanCuti() Collection
    }

    class PerjalananDinas {
        +BigInt id
        +BigInt pegawai_id
        +String tujuan
        +Date tanggal_mulai
        +Date tanggal_selesai
        +String status
        +String tujuan_dinas
        +create(data) PerjalananDinas
        +read(id) PerjalananDinas
        +update(id, data) Boolean
        +delete(id) Boolean
        +approve() Boolean
        +reject() Boolean
        +pegawai() Pegawai
    }

    class PengajuanTukarShift {
        +BigInt id
        +BigInt pemohon_id
        +BigInt penerima_id
        +String status
        +String alasan
        +create(data) PengajuanTukarShift
        +read(id) PengajuanTukarShift
        +update(id, data) Boolean
        +delete(id) Boolean
        +approveByResponder() Boolean
        +rejectByResponder() Boolean
        +approveByHR() Boolean
        +rejectByHR() Boolean
        +pemohon() Pegawai
        +penerima() Pegawai
        +logAudit() Collection
    }
    
    class LogTukarShift {
        +BigInt id
        +BigInt pengajuan_tukar_shift_id
        +String aksi
        +String komentar
        +create(data) LogTukarShift
        +read(id) LogTukarShift
        +pengajuanTukarShift() PengajuanTukarShift
    }

    class DokumenPegawai {
        +BigInt id
        +BigInt pegawai_id
        +BigInt jenis_dokumen_id
        +String path_file
        +Date tanggal_kadaluarsa
        +create(data) DokumenPegawai
        +read(id) DokumenPegawai
        +delete(id) Boolean
        +pegawai() Pegawai
        +jenisDokumen() JenisDokumen
    }

    class JenisDokumen {
        +BigInt id
        +String nama
        +String deskripsi
        +Boolean wajib
        +Boolean aktif
        +create(data) JenisDokumen
        +read(id) JenisDokumen
        +update(id, data) Boolean
        +delete(id) Boolean
        +dokumenPegawai() Collection
    }

    class HariLibur {
        +BigInt id
        +String nama
        +Date tanggal_libur
        +String deskripsi
        +create(data) HariLibur
        +read(id) HariLibur
        +update(id, data) Boolean
        +delete(id) Boolean
    }

    %% Relationships
    Pengguna "1" -- "0..1" Pegawai : memiliki
    Pengguna "*" -- "*" Peran : memiliki_peran
    Departemen "1" -- "*" Pegawai : menaungi
    Pegawai "1" -- "*" Presensi : mencatat
    Presensi "1" -- "*" FotoPresensi : memiliki
    Pegawai "*" -- "*" Shift : memiliki_jadwal (JadwalPegawai)
    Pegawai "1" -- "*" JadwalPegawai : memiliki
    Shift "1" -- "*" JadwalPegawai : referensi
    Pegawai "1" -- "*" PengajuanCuti : mengajukan
    PengajuanCuti "*" -- "1" JenisCuti : dikategorikan_sebagai
    Pegawai "1" -- "*" PerjalananDinas : melakukan
    Pegawai "1" -- "*" PengajuanTukarShift : sebagai_pemohon
    Pegawai "1" -- "*" PengajuanTukarShift : sebagai_penerima
    PengajuanTukarShift "1" -- "*" LogTukarShift : dicatat_di
    Pegawai "1" -- "*" DokumenPegawai : memiliki
    JenisDokumen "1" -- "*" DokumenPegawai : mengklasifikasi
```

### Penjelasan Entitas, Relasi & Operasi (Methods)
- **Atribut & Relasi Kardinalitas**: Relasi antar diagram dijabarkan dengan kardinalitas _(One-to-Many, Many-to-Many via pivot, One-to-One)_. 
- **Method CRUD (`create`, `read`, `update`, `delete`)**: Menggambarkan operasi standar untuk membaca, menyimpan, memperbarui, dan menghapus setiap baris *record* database yang pada implementasi nyatanya ditangani oleh Eloquent Model dan Controller.
- **Method Spesifik Bisnis**: Beberapa model sengaja dicantumkan *action methods* tambahan yang dikelola oleh *Controller/Service* spesifik secara logika OOP:
  - `Pengguna`: method `login()` dan `logout()` untuk alur autentikasi.
  - `Pegawai`: method `assignShift()` untuk menjadwalkan shift.
  - `Presensi`: method `checkIn()` dan `checkOut()` mewakili logika pencatatan waktu *scan* kehadiran.
  - `PengajuanCuti`, `PerjalananDinas`, `PengajuanTukarShift`: method *approval* seperti `approve()` (Setujui) dan `reject()` (Tolak). Secara khusus pada *Tukar Shift*, proses persetujuan memiliki hierarki (`approveByResponder`, `approveByHR`).
