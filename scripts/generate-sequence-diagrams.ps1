$base = "c:\laragon\www\SimpegRSHDI\docs\SequenceDiagram"
if (-not (Test-Path $base)) { New-Item -ItemType Directory -Path $base | Out-Null }

$entries = @(
    @{Group='Autentikasi'; Item='Autentikasi'},
    @{Group='Autentikasi'; Item='login'},
    @{Group='Autentikasi'; Item='logout'},
    @{Group='Kelola Pengguna'; Item='Kelola Pengguna'},
    @{Group='Kelola Pengguna'; Item='Tambah Pengguna'},
    @{Group='Kelola Pengguna'; Item='Lihat Pengguna'},
    @{Group='Kelola Pengguna'; Item='update Pengguna'},
    @{Group='Kelola Pengguna'; Item='delete Pengguna'},
    @{Group='Kelola Pengguna'; Item='Menetapkan Role'},
    @{Group='Kelola Pengguna'; Item='Reset Password'},
    @{Group='Kelola Pengguna'; Item='Nonaktifkan Pengguna'},
    @{Group='Kelola departmen'; Item='Kelola departmen'},
    @{Group='Kelola departmen'; Item='tambah departmen'},
    @{Group='Kelola departmen'; Item='lihat departmen'},
    @{Group='Kelola departmen'; Item='ubah departmen'},
    @{Group='Kelola departmen'; Item='hapus departmen'},
    @{Group='kelola Shift'; Item='kelola Shift'},
    @{Group='kelola Shift'; Item='tambah shift'},
    @{Group='kelola Shift'; Item='lihat shift'},
    @{Group='kelola Shift'; Item='update shift'},
    @{Group='kelola Shift'; Item='delete shift'},
    @{Group='Profil'; Item='Profil'},
    @{Group='Profil'; Item='lihat profile'},
    @{Group='Profil'; Item='edit profile'},
    @{Group='Profil'; Item='ganti foto profile'},
    @{Group='Profil'; Item='ubah password'},
    @{Group='Kelola Absensi'; Item='Kelola Absensi'},
    @{Group='Kelola Absensi'; Item='Check in Pegawai'},
    @{Group='Kelola Absensi'; Item='Check out pegawai'},
    @{Group='Kelola Absensi'; Item='Check in oleh Admin'},
    @{Group='Kelola Absensi'; Item='Check out oleh Admin'},
    @{Group='Kelola Absensi'; Item='Ekspor Absensi'},
    @{Group='Kelola Pegawai'; Item='Kelola Pegawai'},
    @{Group='Kelola Pegawai'; Item='tambah'},
    @{Group='Kelola Pegawai'; Item='lihat'},
    @{Group='Kelola Pegawai'; Item='ubah'},
    @{Group='Kelola Pegawai'; Item='Hapus'},
    @{Group='Kelola Jenis Cuti'; Item='Kelola Jenis Cuti'},
    @{Group='Kelola Jenis Cuti'; Item='Tambah Jenis cuti'},
    @{Group='Kelola Jenis Cuti'; Item='Lihat Jenis Cuti'},
    @{Group='Kelola Jenis Cuti'; Item='Update Jenis Cuti'},
    @{Group='Kelola Jenis Cuti'; Item='delete Jenis Cuti'},
    @{Group='Kelola permintaan cuti'; Item='Kelola permintaan cuti'},
    @{Group='Kelola permintaan cuti'; Item='Setujui permintaan cuti'},
    @{Group='Kelola permintaan cuti'; Item='tolak permintaan cuti'},
    @{Group='Kelola permintaan cuti'; Item='Lihat detail permintaan cuti'},
    @{Group='Pengajuan Permintaan cuti'; Item='Pengajuan Permintaan cuti'},
    @{Group='Pengajuan Permintaan cuti'; Item='isi form pengajuan'},
    @{Group='Pengajuan Permintaan cuti'; Item='Batalkan Pengajuan'},
    @{Group='Pengajuan Permintaan cuti'; Item='eksport Pengajuan permintaan cuti'},
    @{Group='Kelola Tukar Shift'; Item='Kelola Tukar Shift'},
    @{Group='Kelola Tukar Shift'; Item='Ajukan Tukar Shift'},
    @{Group='Kelola Tukar Shift'; Item='Setujui permintaan tukar shift'},
    @{Group='Kelola Tukar Shift'; Item='tolak permintaan tukar shift'},
    @{Group='Kelola Tukar Shift'; Item='lihat detail tukar shift'},
    @{Group='Kelola dokumen'; Item='Kelola dokumen'},
    @{Group='Kelola dokumen'; Item='kirim dokumen'},
    @{Group='Kelola dokumen'; Item='verifikasi dokumen'},
    @{Group='Kelola dokumen'; Item='lihat detail dokumen'},
    @{Group='Kelola dokumen'; Item='hapus dokumen'},
    @{Group='Pengajuan Perjalanan dinas'; Item='Pengajuan Perjalanan dinas'},
    @{Group='Pengajuan Perjalanan dinas'; Item='isi form pengajuan'},
    @{Group='Pengajuan Perjalanan dinas'; Item='Batalkan Pengajuan'},
    @{Group='Pengajuan Perjalanan dinas'; Item='eksport Pengajuan perjalanan dinas'},
    @{Group='Kelola perjalanan dinas'; Item='Kelola perjalanan dinas'},
    @{Group='Kelola perjalanan dinas'; Item='Setujui permintaan perjalanan dinas'},
    @{Group='Kelola perjalanan dinas'; Item='tolak permintaan perjalanan dinas'},
    @{Group='Kelola perjalanan dinas'; Item='lihat detail permintaan perjalanan dinas'},
    @{Group='Kelola perjalanan dinas'; Item='eksport riwayat perjalanan dinas'},
    @{Group='buat laporan'; Item='buat laporan'},
    @{Group='buat laporan'; Item='Laporan Absensi'},
    @{Group='buat laporan'; Item='Laporan Cuti'},
    @{Group='buat laporan'; Item='Perjalanan dinas'},
    @{Group='buat laporan'; Item='Laporan Tukar Shift'}
)

function To-Pascal([string]$text) {
    $clean = ($text -replace '[^a-zA-Z0-9 ]', ' ').Trim()
    if ([string]::IsNullOrWhiteSpace($clean)) { return 'Diagram' }
    ($clean -split '\s+' | ForEach-Object {
        if ($_.Length -gt 1) { $_.Substring(0,1).ToUpper() + $_.Substring(1).ToLower() } else { $_.ToUpper() }
    }) -join ''
}

function Get-Context([string]$group) {
    switch -Regex ($group) {
        'Autentikasi' { return @{Actor='Pengguna'; Controller='AuthController'; Service='AuthService'; Repo='UserRepository'} }
        'Kelola Pengguna' { return @{Actor='Admin Sistem'; Controller='UserController'; Service='UserService'; Repo='UserRepository'} }
        'Kelola departmen' { return @{Actor='HR/Admin'; Controller='DepartmentController'; Service='DepartmentService'; Repo='DepartmentRepository'} }
        'kelola Shift' { return @{Actor='HR/Admin'; Controller='ShiftController'; Service='ShiftService'; Repo='ShiftRepository'} }
        'Profil' { return @{Actor='Pengguna'; Controller='ProfileController'; Service='ProfileService'; Repo='UserRepository'} }
        'Kelola Absensi' { return @{Actor='Pegawai/Admin'; Controller='AttendanceController'; Service='AttendanceService'; Repo='AttendanceRepository'} }
        'Kelola Pegawai' { return @{Actor='HR/Admin'; Controller='WorkerController'; Service='WorkerService'; Repo='WorkerRepository'} }
        'Kelola Jenis Cuti' { return @{Actor='HR/Admin'; Controller='LeaveTypeController'; Service='LeaveTypeService'; Repo='LeaveTypeRepository'} }
        'Kelola permintaan cuti' { return @{Actor='Manager/HR'; Controller='LeaveRequestController'; Service='LeaveRequestService'; Repo='LeaveRequestRepository'} }
        'Pengajuan Permintaan cuti' { return @{Actor='Pegawai'; Controller='LeaveRequestController'; Service='LeaveRequestService'; Repo='LeaveRequestRepository'} }
        'Kelola Tukar Shift' { return @{Actor='Manager/HR'; Controller='ShiftSwapController'; Service='ShiftSwapService'; Repo='ShiftSwapRepository'} }
        'Kelola dokumen' { return @{Actor='Pegawai/HR'; Controller='DocumentController'; Service='DocumentService'; Repo='DocumentRepository'} }
        'Pengajuan Perjalanan dinas' { return @{Actor='Pegawai'; Controller='BusinessTripController'; Service='BusinessTripService'; Repo='BusinessTripRepository'} }
        'Kelola perjalanan dinas' { return @{Actor='Manager/HR'; Controller='BusinessTripController'; Service='BusinessTripService'; Repo='BusinessTripRepository'} }
        'buat laporan' { return @{Actor='HR/Admin'; Controller='ReportController'; Service='ReportService'; Repo='ReportRepository'} }
        default { return @{Actor='Pengguna'; Controller='SystemController'; Service='SystemService'; Repo='SystemRepository'} }
    }
}

foreach ($e in $entries) {
    $ctx = Get-Context $e.Group
    $groupName = To-Pascal $e.Group
    $itemName = To-Pascal $e.Item
    $fileName = if ($e.Item -eq $e.Group) { "$itemName.mermaid" } else { "$groupName-$itemName.mermaid" }
    $filePath = Join-Path $base $fileName

    $title = $e.Item
    $content = @"
sequenceDiagram
    autonumber
    actor A as $($ctx.Actor)
    participant UI as Web UI
    participant C as $($ctx.Controller)
    participant S as $($ctx.Service)
    participant R as $($ctx.Repo)
    database DB as Database

    A->>UI: Pilih menu "$title"
    UI->>C: Request $title
    C->>S: Proses bisnis $title
    S->>R: Akses data
    R->>DB: Query data
    DB-->>R: Hasil query
    R-->>S: Data/Status

    alt Berhasil
        S-->>C: Sukses
        C-->>UI: Response sukses
        UI-->>A: Tampilkan hasil berhasil
    else Gagal validasi/proses
        S-->>C: Error
        C-->>UI: Response error
        UI-->>A: Tampilkan pesan gagal
    end
"@

    Set-Content -Path $filePath -Value $content -Encoding UTF8
}

Write-Output "FILES_CREATED=$($entries.Count)"
Write-Output "FILES_IN_FOLDER=$((Get-ChildItem -Path $base -Filter *.mermaid | Measure-Object).Count)"
