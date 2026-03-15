@extends('layouts.public-info')

@section('title', 'Privacy Policy')
@section('page_heading', 'Privacy Policy')
@section('hero_title', 'Perlindungan data pengguna dalam operasional SIDIA')
@section('hero_description', 'Halaman ini menjelaskan data apa saja yang dikelola SIDIA, alasan pemrosesannya, siapa yang dapat mengaksesnya, dan bagaimana data tersebut dijaga dalam konteks layanan kepegawaian RSUD HDI.')

@section('content')
    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">1. Data yang dikumpulkan</h3>
        <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <li>Data akun dan identitas pegawai seperti nama, email, jabatan, unit kerja, role, dan informasi profil.</li>
            <li>Data operasional absensi seperti waktu check-in, check-out, foto kehadiran, titik lokasi, dan status kehadiran.</li>
            <li>Data administrasi pengajuan seperti cuti, lembur, perjalanan dinas, shift swap, dokumen pegawai, serta catatan persetujuan atasan.</li>
            <li>Data notifikasi sistem, audit log, dan jejak aktivitas yang diperlukan untuk keamanan serta pelacakan perubahan.</li>
        </ul>
    </div>

    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">2. Tujuan penggunaan data</h3>
        <ul class="mt-3 space-y-2 text-sm leading-7 text-emerald-50/80 sm:text-base">
            <li>Mengelola proses kehadiran, penjadwalan kerja, pengajuan administrasi, dan approval lintas role.</li>
            <li>Memastikan data kepegawaian yang tampil di dashboard, laporan, dan export PDF tetap akurat dan dapat dipertanggungjawabkan.</li>
            <li>Mendukung keamanan akun, reset password, kontrol hak akses, dan investigasi insiden operasional bila diperlukan.</li>
        </ul>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">3. Akses dan pembagian data</h3>
            <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">Data hanya diakses sesuai role dan permission dalam sistem. Super Admin, HR, Manager, dan Employee melihat data yang berbeda sesuai tanggung jawab kerja. Data tidak ditujukan untuk distribusi publik di luar kebutuhan internal layanan RSUD HDI.</p>
        </div>
        <div class="section-card p-5 sm:p-6">
            <h3 class="text-xl font-bold text-white">4. Penyimpanan dan retensi</h3>
            <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">Dokumen, foto, dan catatan transaksi disimpan selama masih dibutuhkan untuk kebutuhan operasional, audit, kepatuhan internal, atau rekonsiliasi administrasi. Penghapusan dan perubahan mengikuti kebijakan internal pengelola sistem.</p>
        </div>
    </div>

    <div class="section-card p-5 sm:p-6">
        <h3 class="text-xl font-bold text-white">5. Hak pengguna</h3>
        <p class="mt-3 text-sm leading-7 text-emerald-50/80 sm:text-base">Pengguna dapat meminta koreksi data profil yang tidak akurat, mengajukan pembaruan dokumen, dan melaporkan penggunaan akun yang mencurigakan kepada admin atau pengelola layanan. Untuk kendala akses, gunakan alur bantuan pada halaman Help.</p>
    </div>
@endsection
