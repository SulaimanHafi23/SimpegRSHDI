$seqDir = "c:\laragon\www\SimpegRSHDI\docs\SequenceDiagram"
$indexPath = Join-Path $seqDir "INDEX.md"

if (-not (Test-Path $seqDir)) {
    throw "Folder tidak ditemukan: $seqDir"
}

function Split-PascalWords([string]$value) {
    if ([string]::IsNullOrWhiteSpace($value)) { return $value }
    $s = $value -creplace '(?<=[A-Z])(?=[A-Z][a-z])', ' '
    $s = $s -creplace '(?<=[a-z0-9])(?=[A-Z])', ' '
    return $s.Trim()
}

function To-Label([string]$value) {
    $v = Split-PascalWords $value
    return ($v -replace '\s+', ' ').Trim()
}

function Get-Route([string]$group, [string]$actionLabel) {
    $a = $actionLabel.ToLowerInvariant()
    switch -Regex ($group) {
        '^Autentikasi$' {
            if ($a -match 'login') { return 'POST /login' }
            if ($a -match 'logout') { return 'POST /logout' }
            return 'GET /login'
        }
        '^KelolaPengguna$' { return '/users' }
        '^KelolaDepartmen$' { return '/master/departments' }
        '^KelolaShift$' { return '/master/shifts' }
        '^KelolaJenisCuti$' { return '/master/leave-types' }
        '^KelolaPegawai$' { return '/workers' }
        '^KelolaAbsensi$' { return '/attendance' }
        '^KelolaPermintaanCuti$' { return '/approvals/leaves' }
        '^PengajuanPermintaanCuti$' { return '/employee/leaves' }
        '^KelolaTukarShift$' { return '/manager/shift-swap-approvals' }
        '^KelolaDokumen$' { return '/approvals/documents' }
        '^PengajuanPerjalananDinas$' { return '/employee/business-trips' }
        '^KelolaPerjalananDinas$' { return '/approvals/business-trips' }
        '^BuatLaporan$' { return '/reports/*' }
        '^Profil$' { return '/profile' }
        '^PortalPegawai$' { return '/employee/calendar' }
        default { return '/module' }
    }
}

function Get-ControllerAndModel([string]$group, [string]$actionLabel) {
    switch -Regex ($group) {
        '^Autentikasi$' { return @{ Controller = 'AuthController'; Model = 'User'; Actor = 'Pengguna' } }
        '^KelolaPengguna$' { return @{ Controller = 'UserController'; Model = 'User'; Actor = 'Admin Sistem' } }
        '^KelolaDepartmen$' { return @{ Controller = 'DepartmentController'; Model = 'Department'; Actor = 'HR/Admin' } }
        '^KelolaShift$' { return @{ Controller = 'ShiftController'; Model = 'Shift'; Actor = 'HR/Admin' } }
        '^Profil$' { return @{ Controller = 'ProfileController'; Model = 'User'; Actor = 'Pengguna' } }
        '^PortalPegawai$' { return @{ Controller = 'CalendarController'; Model = 'CalendarEvent'; Actor = 'Pegawai' } }
        '^KelolaAbsensi$' { return @{ Controller = 'AttendanceController'; Model = 'Attendance'; Actor = 'Pegawai/Admin' } }
        '^KelolaPegawai$' { return @{ Controller = 'WorkerController'; Model = 'Worker'; Actor = 'HR/Admin' } }
        '^KelolaJenisCuti$' { return @{ Controller = 'LeaveTypeController'; Model = 'LeaveType'; Actor = 'HR/Admin' } }
        '^KelolaPermintaanCuti$' { return @{ Controller = 'LeaveRequestController'; Model = 'LeaveRequest'; Actor = 'Manager/HR' } }
        '^PengajuanPermintaanCuti$' { return @{ Controller = 'LeaveRequestController'; Model = 'LeaveRequest'; Actor = 'Pegawai' } }
        '^KelolaTukarShift$' { return @{ Controller = 'ShiftSwapController'; Model = 'ShiftSwapRequest'; Actor = 'Manager/HR' } }
        '^KelolaDokumen$' { return @{ Controller = 'DocumentController'; Model = 'WorkerDocument'; Actor = 'Pegawai/HR' } }
        '^PengajuanPerjalananDinas$' { return @{ Controller = 'BusinessTripController'; Model = 'BusinessTrip'; Actor = 'Pegawai' } }
        '^KelolaPerjalananDinas$' { return @{ Controller = 'BusinessTripController'; Model = 'BusinessTrip'; Actor = 'Manager/HR' } }
        '^BuatLaporan$' { return @{ Controller = 'ReportController'; Model = 'Report'; Actor = 'HR/Admin' } }
        default { return @{ Controller = 'SystemController'; Model = 'Record'; Actor = 'Pengguna' } }
    }
}

function Build-Sequence([string]$actor, [string]$controller, [string]$model, [string]$title, [string]$group) {
    $lower = $title.ToLowerInvariant()
    $route = Get-Route -group $group -actionLabel $title

    $header = @(
        'sequenceDiagram'
        '    autonumber'
        "    actor A as $actor"
        '    participant UI as Web UI'
        "    participant C as $controller"
        "    participant M as $model Model"
        '    database DB as Database'
        ''
    )

    if ($lower -match 'login') {
        $body = @(
            "    A->>UI: Isi username dan password"
            "    UI->>C: POST /login"
            "    C->>M: Cari user berdasarkan username"
            "    M->>DB: SELECT user"
            "    DB-->>M: Data user"
            "    M-->>C: User ditemukan / tidak"
            "    alt Kredensial valid"
            "        C-->>UI: Buat session dan redirect dashboard"
            "        UI-->>A: Login berhasil"
            "    else Kredensial tidak valid"
            "        C-->>UI: Kembalikan error login"
            "        UI-->>A: Tampilkan pesan gagal login"
            "    end"
        )
    }
    elseif ($lower -match 'logout') {
        $body = @(
            "    A->>UI: Klik logout"
            "    UI->>C: $route"
            "    C->>M: Invalidate token/session user"
            "    M->>DB: UPDATE session/token"
            "    DB-->>M: Session ditutup"
            "    M-->>C: Status logout"
            "    C-->>UI: Redirect ke halaman login"
            "    UI-->>A: Logout berhasil"
        )
    }
    elseif ($lower -match '^kalender|events|calendar') {
        $body = @(
            "    A->>UI: Buka kalender kerja"
            "    UI->>C: GET $route"
            "    C->>M: Ambil event shift, cuti, hari libur"
            "    M->>DB: SELECT event berdasarkan periode"
            "    DB-->>M: Daftar event"
            "    M-->>C: Event terformat"
            "    C-->>UI: Kirim event kalender"
            "    UI-->>A: Tampilkan kalender (read-only)"
        )
    }
    elseif ($lower -match 'setujui|approve|accept') {
        $body = @(
            "    A->>UI: Klik Setujui pada data $title"
            "    UI->>C: POST $route/{id}/approve"
            "    C->>M: Update status = approved"
            "    M->>DB: UPDATE status dan approved_by"
            "    DB-->>M: Data berhasil diperbarui"
            "    M-->>C: Status terbaru"
            "    C-->>UI: Response sukses"
            "    UI-->>A: Notifikasi persetujuan berhasil"
        )
    }
    elseif ($lower -match 'tolak|reject') {
        $body = @(
            "    A->>UI: Isi alasan penolakan"
            "    UI->>C: POST $route/{id}/reject"
            "    C->>M: Update status = rejected"
            "    M->>DB: UPDATE status dan rejection_reason"
            "    DB-->>M: Data berhasil diperbarui"
            "    M-->>C: Status terbaru"
            "    C-->>UI: Response sukses"
            "    UI-->>A: Notifikasi penolakan berhasil"
        )
    }
    elseif ($lower -match 'verifikasi|verify') {
        $body = @(
            "    A->>UI: Klik verifikasi dokumen"
            "    UI->>C: POST $route/{id}/verify"
            "    C->>M: Update status = verified"
            "    M->>DB: UPDATE status_verifikasi"
            "    DB-->>M: Status verifikasi tersimpan"
            "    M-->>C: Hasil verifikasi"
            "    C-->>UI: Response sukses"
            "    UI-->>A: Dokumen terverifikasi"
        )
    }
    elseif ($lower -match 'execute|revert') {
        $verb = 'execute'
        if ($lower -match 'revert') { $verb = 'revert' }
        $body = @(
            "    A->>UI: Jalankan aksi $verb"
            "    UI->>C: POST $route/{id}/$verb"
            "    C->>M: Validasi status sebelum $verb"
            "    M->>DB: UPDATE data jadwal/riwayat swap"
            "    DB-->>M: Perubahan tersimpan"
            "    M-->>C: Hasil aksi $verb"
            "    C-->>UI: Response sukses/gagal"
            "    UI-->>A: Tampilkan hasil aksi"
        )
    }
    elseif ($lower -match 'check in') {
        $body = @(
            "    A->>UI: Pilih menu check-in"
            "    UI->>C: POST $route/check-in"
            "    C->>M: Validasi lokasi, waktu, dan jadwal shift"
            "    M->>DB: INSERT log jam_masuk"
            "    DB-->>M: Data check-in tersimpan"
            "    M-->>C: Hasil check-in"
            "    C-->>UI: Response sukses"
            "    UI-->>A: Check-in berhasil"
        )
    }
    elseif ($lower -match 'check out') {
        $body = @(
            "    A->>UI: Pilih menu check-out"
            "    UI->>C: POST $route/check-out"
            "    C->>M: Validasi sudah check-in dan jam pulang"
            "    M->>DB: UPDATE log jam_pulang"
            "    DB-->>M: Data check-out tersimpan"
            "    M-->>C: Hasil check-out"
            "    C-->>UI: Response sukses"
            "    UI-->>A: Check-out berhasil"
        )
    }
    elseif ($lower -match 'tambah|kirim|isi form|ajukan') {
        $body = @(
            "    A->>UI: Isi form $title"
            "    UI->>C: POST $route"
            "    C->>C: Validasi input"
            "    alt Data valid"
            "        C->>M: Simpan data baru"
            "        M->>DB: INSERT"
            "        DB-->>M: Data tersimpan"
            "        M-->>C: Hasil simpan"
            "        C-->>UI: Response sukses"
            "        UI-->>A: Notifikasi berhasil"
            "    else Data tidak valid"
            "        C-->>UI: Response gagal validasi"
            "        UI-->>A: Tampilkan error form"
            "    end"
        )
    }
    elseif ($lower -match 'lihat|detail|profil|laporan') {
        $body = @(
            "    A->>UI: Buka menu $title"
            "    UI->>C: GET $route"
            "    C->>M: Ambil data sesuai filter"
            "    M->>DB: SELECT"
            "    DB-->>M: Dataset"
            "    M-->>C: Data siap tampil"
            "    C-->>UI: Kirim data ke halaman"
            "    UI-->>A: Tampilkan data"
        )
    }
    elseif ($lower -match 'update|ubah|edit|nonaktifkan|reset') {
        $statusHint = 'status/atribut'
        if ($lower -match 'nonaktifkan') { $statusHint = 'is_active = false' }
        if ($lower -match 'reset') { $statusHint = 'password baru ter-hash' }

        $body = @(
            "    A->>UI: Jalankan aksi $title"
            "    UI->>C: Submit perubahan"
            "    C->>M: Update data ($statusHint)"
            "    M->>DB: UPDATE"
            "    DB-->>M: Perubahan tersimpan"
            "    M-->>C: Hasil update"
            "    C-->>UI: Response sukses/gagal"
            "    UI-->>A: Tampilkan hasil proses"
        )
    }
    elseif ($lower -match 'hapus|delete|batalkan') {
        $body = @(
            "    A->>UI: Pilih data untuk $title"
            "    UI->>C: Kirim permintaan hapus/batal"
            "    C->>M: Ubah status atau hapus data"
            "    M->>DB: DELETE/UPDATE status"
            "    DB-->>M: Operasi selesai"
            "    M-->>C: Hasil operasi"
            "    C-->>UI: Response berhasil/gagal"
            "    UI-->>A: Tampilkan notifikasi"
        )
    }
    elseif ($lower -match 'eksport|export') {
        $body = @(
            "    A->>UI: Pilih filter lalu klik export"
            "    UI->>C: GET $route/export"
            "    C->>M: Ambil dataset export"
            "    M->>DB: SELECT sesuai filter"
            "    DB-->>M: Dataset"
            "    M-->>C: Data export siap"
            "    C->>C: Bentuk file (xlsx/pdf/csv)"
            "    C-->>UI: Kirim file unduhan"
            "    UI-->>A: File berhasil diunduh"
        )
    }
    elseif ($group -eq $title) {
        $body = @(
            "    A->>UI: Buka modul $title"
            "    UI->>C: GET $route"
            "    C->>M: Ambil ringkasan data modul"
            "    M->>DB: SELECT summary, counter, status"
            "    DB-->>M: Data ringkasan"
            "    M-->>C: Data siap tampil"
            "    C-->>UI: Render halaman modul"
            "    UI-->>A: Pilih use case turunan (Create/Read/Update/Delete/Extend)"
        )
    }
    else {
        $body = @(
            "    A->>UI: Akses fitur $title"
            "    UI->>C: Request $route"
            "    C->>M: Ambil data utama"
            "    M->>DB: SELECT"
            "    DB-->>M: Data"
            "    M-->>C: Data diproses"
            "    C-->>UI: Tampilkan hasil"
            "    UI-->>A: Fitur siap digunakan"
        )
    }

    return (($header + $body) -join "`r`n") + "`r`n"
}

$files = Get-ChildItem -Path $seqDir -Filter *.mermaid | Sort-Object Name
foreach ($f in $files) {
    $baseName = [System.IO.Path]::GetFileNameWithoutExtension($f.Name)
    $parts = $baseName -split '-', 2

    if ($parts.Count -eq 2) {
        $group = $parts[0]
        $action = $parts[1]
    }
    else {
        $group = $parts[0]
        $action = $parts[0]
    }

    $actionLabel = To-Label $action
    $ctx = Get-ControllerAndModel -group $group -actionLabel $actionLabel
    $content = Build-Sequence -actor $ctx.Actor -controller $ctx.Controller -model $ctx.Model -title $actionLabel -group $group
    Set-Content -Path $f.FullName -Value $content -Encoding UTF8
}

$grouped = $files | Group-Object {
    $base = [System.IO.Path]::GetFileNameWithoutExtension($_.Name)
    if ($base -like '*-*') { ($base -split '-', 2)[0] } else { $base }
} | Sort-Object Name

$lines = @()
$lines += '# Index Sequence Diagram'
$lines += ''
$lines += "Total diagram: $($files.Count)"
$lines += "Digenerate otomatis: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
$lines += ''

foreach ($g in $grouped) {
    $groupLabel = To-Label $g.Name
    $lines += "## $groupLabel"
    $lines += ''

    foreach ($file in ($g.Group | Sort-Object Name)) {
        $basename = [System.IO.Path]::GetFileNameWithoutExtension($file.Name)
        $segments = $basename -split '-', 2
        if ($segments.Count -eq 2) {
            $itemLabel = To-Label $segments[1]
        }
        else {
            $itemLabel = To-Label $segments[0]
        }
        $lines += "- [$itemLabel](./$($file.Name))"
    }

    $lines += ''
}

Set-Content -Path $indexPath -Value ($lines -join "`r`n") -Encoding UTF8

Write-Output "UPDATED_FILES=$($files.Count)"
Write-Output "INDEX_FILE=$indexPath"
