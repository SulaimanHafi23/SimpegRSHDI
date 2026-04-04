@extends('layouts.public-info')

@section('title', 'Terms of Service')
@section('page_heading', 'Terms of Service')
@section('hero_title', 'Ketentuan penggunaan SIDIA untuk operasional internal')
@section('hero_description', 'Ketentuan ini mengatur penggunaan akun, penginputan data, unggahan dokumen, proses approval, dan tanggung jawab pengguna saat memanfaatkan fitur SIDIA dalam lingkungan kerja RSUD HDI.')

@section('content')
    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">1. Penggunaan akun</h3>
        <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <li>Akun SIDIA hanya boleh digunakan oleh pengguna yang telah diberikan akses resmi oleh pengelola sistem.</li>
            <li>Pengguna wajib menjaga kerahasiaan password dan segera mengganti password bila ada indikasi akses tidak sah.</li>
            <li>Dilarang meminjamkan akun, membagikan kredensial, atau menggunakan akun pengguna lain tanpa izin.</li>
        </ul>
    </div>

    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">2. Integritas data operasional</h3>
        <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <li>Data absensi, cuti, perjalanan dinas, dan shift swap harus diinput sesuai kondisi nyata dan jadwal kerja yang berlaku.</li>
            <li>Foto absensi, lokasi, dan dokumen pendukung tidak boleh dimanipulasi untuk memperoleh persetujuan yang tidak semestinya.</li>
            <li>Setiap approval atau penolakan yang diberikan atasan harus berdasarkan kewenangan dan bukti administrasi yang memadai.</li>
        </ul>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">3. Dokumen dan lampiran</h3>
            <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">Pengguna bertanggung jawab memastikan file yang diunggah relevan, aman, tidak merusak sistem, dan sesuai kebutuhan administrasi kepegawaian. Lampiran yang tidak valid dapat ditolak atau dihapus oleh pengelola.</p>
        </div>
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">4. Ketersediaan layanan</h3>
            <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">SIDIA dapat mengalami pemeliharaan, pembaruan, atau gangguan sementara. Pengelola berhak menyesuaikan fitur, tampilan, dan alur sistem untuk kebutuhan operasional tanpa mengurangi kontrol akses yang telah ditetapkan.</p>
        </div>
    </div>

    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">5. Pelanggaran dan tindak lanjut</h3>
        <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">Penyalahgunaan akun, manipulasi data, atau tindakan yang mengganggu proses kerja dapat menyebabkan pembatasan akses, audit lebih lanjut, atau tindak lanjut internal sesuai kebijakan instansi.</p>
    </div>
@endsection
