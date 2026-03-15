@extends('layouts.public-info')

@section('title', 'Help Center')
@section('page_heading', 'Help Center')
@section('hero_title', 'Panduan operasional SIDIA untuk setiap role dan alur kerja utama')
@section('hero_description', 'Halaman bantuan ini merangkum alur yang paling sering dipakai di proyek ini, mulai dari login, reset password, absensi, pengajuan administrasi, approval, sampai pemakaian fitur oleh Admin, HR, Manager, dan Employee.')

@section('content')
    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">1. Mulai dari akses akun</h3>
        <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <li>Masuk menggunakan email dan password resmi yang diberikan oleh admin atau pengelola sistem.</li>
            <li>Jika lupa password, gunakan halaman lupa password untuk meminta tautan reset sebelum menghubungi admin.</li>
            <li>Setelah login, pengguna akan diarahkan otomatis ke dashboard sesuai role dan hak aksesnya.</li>
        </ul>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">2. Employee</h3>
            <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
                <li>Gunakan menu attendance untuk check-in, check-out, melihat riwayat, dan export data bila diizinkan.</li>
                <li>Ajukan cuti, lembur, perjalanan dinas, atau shift swap dari menu yang tersedia dengan data tanggal, alasan, dan lampiran yang valid.</li>
                <li>Perbarui profil serta dokumen pegawai agar data administratif tetap akurat dan mudah diverifikasi.</li>
            </ul>
        </div>
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">3. Manager</h3>
            <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
                <li>Tinjau pengajuan yang membutuhkan persetujuan, terutama shift swap dan proses approval lain yang menjadi kewenangan atasan.</li>
                <li>Pastikan keputusan approve atau reject didasarkan pada data pegawai, jadwal, dan lampiran yang tersedia di sistem.</li>
                <li>Gunakan dashboard manager untuk memantau status permintaan yang menunggu tindak lanjut.</li>
            </ul>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">4. HR</h3>
            <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">HR berfokus pada monitoring dashboard HR, validasi data kepegawaian, pengelolaan data yang berkaitan dengan administrasi pegawai, serta koordinasi terhadap permintaan yang melibatkan dokumen, cuti, jadwal, dan kepatuhan data.</p>
        </div>
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">5. Admin dan Super Admin</h3>
            <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">Admin mengelola master data, role, user, worker, lokasi, shift, kalender, audit log, serta konfigurasi operasional lain yang mempengaruhi seluruh sistem. Perubahan data inti sebaiknya dilakukan hati-hati karena berdampak ke dashboard, approval, laporan, dan export.</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">6. Absensi dan lokasi</h3>
            <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
                <li>Pastikan izin kamera dan lokasi aktif saat melakukan check-in atau check-out.</li>
                <li>Gunakan foto yang jelas dan lokasi yang sesuai titik kehadiran yang berlaku.</li>
                <li>Jika data absensi tidak muncul atau gagal tersimpan, cek jaringan lalu ulangi proses dari menu attendance.</li>
            </ul>
        </div>
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">7. Pengajuan dan dokumen</h3>
            <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
                <li>Unggah lampiran yang relevan untuk cuti, lembur, perjalanan dinas, atau dokumen pegawai.</li>
                <li>Pastikan format dan ukuran file mengikuti batasan yang ditetapkan form.</li>
                <li>Pantau status approval secara berkala untuk melihat apakah permintaan disetujui, ditolak, atau masih menunggu.</li>
            </ul>
        </div>
    </div>

    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">8. Jika mengalami kendala</h3>
        <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <li>Periksa kembali koneksi internet, izin kamera/lokasi, dan validitas data input.</li>
            <li>Untuk masalah login, mulai dari halaman lupa password sebelum meminta bantuan admin.</li>
            <li>Untuk data yang tidak sinkron, approval yang tertahan, atau error operasional, hubungi tim pengelola SIDIA atau admin internal RSUD HDI.</li>
        </ul>
    </div>

    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">9. FAQ singkat</h3>
        <div class="mt-4 space-y-4 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <div>
                <p class="font-semibold text-white">Mengapa saya tidak bisa check-in?</p>
                <p>Biasanya karena izin lokasi atau kamera belum aktif, jaringan tidak stabil, atau Anda berada di luar ketentuan lokasi absensi.</p>
            </div>
            <div>
                <p class="font-semibold text-white">Mengapa pengajuan saya belum diproses?</p>
                <p>Periksa kembali status approval di menu terkait. Pengajuan bisa menunggu tindakan atasan, HR, atau admin sesuai alur masing-masing.</p>
            </div>
            <div>
                <p class="font-semibold text-white">Bagaimana jika dokumen gagal diunggah?</p>
                <p>Pastikan format file sesuai, ukuran tidak melebihi batas, dan koneksi internet cukup stabil saat upload berlangsung.</p>
            </div>
            <div>
                <p class="font-semibold text-white">Bagaimana jika saya mendapat error 403, 404, 419, atau 500?</p>
                <p>Gunakan tombol kembali atau login ulang bila sesi habis, lalu buka Help Center ini untuk panduan. Jika error berulang, hubungi pengelola SIDIA.</p>
            </div>
        </div>
    </div>
@endsection
