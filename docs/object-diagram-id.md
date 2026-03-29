# Object Diagram SIMPEG (Sistem Informasi Kepegawaian)

Berikut adalah *Object Diagram* yang mengilustrasikan keadaan *real* (instansiasi objek beserta contoh data/nilai atributnya) dari sistem SIMPEG pada suatu titik waktu tertentu. 

Diagram ini menggambarkan skenario nyata di mana dua orang pegawai (Budi dan Siti) berada di departemen yang sama, memiliki catatan presensi, pengajuan cuti, dan alur permintaan tukar shift.

```mermaid
classDiagram
    %% Instansiasi Objek Departemen
    class Departemen_RawatInap {
        id = 1
        nama = "Rawat Inap"
        kode = "RI-01"
    }

    %% Instansiasi Objek Akun Pengguna
    class User_Budi {
        id = 101
        nama = "Budi Santoso"
        email = "budi@rs.com"
        aktif = true
    }

    class User_Siti {
        id = 102
        nama = "Siti Aminah"
        email = "siti@rs.com"
        aktif = true
    }

    %% Instansiasi Objek Pegawai
    class Pegawai_Budi {
        id = 201
        nip = "19900101"
        nama_depan = "Budi"
        jabatan = "Perawat Pelaksana"
        departemen_id = 1
    }

    class Pegawai_Siti {
        id = 202
        nip = "19920202"
        nama_depan = "Siti"
        jabatan = "Perawat Kepala"
        departemen_id = 1
    }

    %% Instansiasi Objek Shift & Jadwal
    class Shift_Pagi {
        id = 1
        nama = "Shift Pagi"
        waktu_mulai = "07:00"
        waktu_selesai = "15:00"
    }

    class Shift_Siang {
        id = 2
        nama = "Shift Siang"
        waktu_mulai = "15:00"
        waktu_selesai = "23:00"
    }

    class JadwalPegawai_Budi_01April {
        id = 301
        pegawai_id = 201
        shift_id = 1
        tanggal_berlaku = "2026-04-01"
    }

    %% Instansiasi Objek Presensi
    class Presensi_Budi_01April {
        id = 401
        pegawai_id = 201
        tanggal_kerja = "2026-04-01"
        waktu_masuk = "06:55"
        status = "HADIR"
    }

    %% Instansiasi Objek Cuti
    class JenisCuti_Tahunan {
        id = 1
        nama = "Cuti Tahunan"
        jatah_hari_default = 12
    }

    class PengajuanCuti_Siti {
        id = 501
        pegawai_id = 202
        jenis_cuti_id = 1
        tanggal_mulai = "2026-04-10"
        tanggal_selesai = "2026-04-12"
        status = "DISETUJUI"
    }

    %% Instansiasi Objek Tukar Shift
    class TukarShift_Budi_Siti {
        id = 601
        pemohon_id = 201
        penerima_id = 202
        status = "MENUNGGU"
        alasan = "Ada acara keluarga mendadak"
    }

    %% Hubungan dan Link antar Objek
    User_Budi -- Pegawai_Budi : instance of
    User_Siti -- Pegawai_Siti : instance of
    
    Departemen_RawatInap -- Pegawai_Budi : memiliki anggota
    Departemen_RawatInap -- Pegawai_Siti : memiliki anggota

    Pegawai_Budi -- Presensi_Budi_01April : mencatat kehadiran

    Pegawai_Siti -- PengajuanCuti_Siti : membuat pengajuan
    PengajuanCuti_Siti -- JenisCuti_Tahunan : berkategori

    Pegawai_Budi -- TukarShift_Budi_Siti : sebagai pemohon
    Pegawai_Siti -- TukarShift_Budi_Siti : sebagai penerima

    Pegawai_Budi -- JadwalPegawai_Budi_01April : memiliki jadwal
    Shift_Pagi -- JadwalPegawai_Budi_01April : referensi shift
```

### Penjelasan Skenario Object Diagram
Berdasarkan Class Diagram sebelumnya, pada Object Diagram di atas digambarkan suatu snapshot data saat sistem berjalan:
1. **Pekerja & Departemen**: Terdapat dua objek pegawai (Budi & Siti) yang sama-sama tergabung dalam satu departemen (Departemen Rawat Inap). Keduanya juga terhubung langsung dengan Akun Pengguna (`User_Budi`, `User_Siti`).
2. **Jadwal & Shift**: Budi memiliki relasi pivot jadwal masuk (`JadwalPegawai_Budi_01April`) yang mengarah padanya dan `Shift_Pagi`.
3. **Presensi**: Budi melakukan check-in tepat waktu pukul 06:55 dan membuat objek presensi (`Presensi_Budi_01April`) dengan status HADIR.
4. **Cuti**: Siti mengajukan sebuah Cuti (`PengajuanCuti_Siti`) terkait jenis Cuti Tahunan (`JenisCuti_Tahunan`) yang saat ini berstatus DISETUJUI.
5. **Tukar Shift**: Budi ingin bertukar shift dengan Siti dengan alasan acara keluarga, dan menghasilkan record obyek (`TukarShift_Budi_Siti`) dengan status saat ini MENUNGGU persetujuan dari Siti / HR.
