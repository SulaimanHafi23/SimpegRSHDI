@extends('layouts.admin')

@section('title', 'Detail Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.workers.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-xl sm:text-2xl font-bold text-gray-900">Detail Pegawai</h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Informasi lengkap pegawai</p>
            </div>
        </div>
        <div class="flex space-x-2 w-full sm:w-auto">
            <a href="{{ route('admin.workers.edit', 1) }}" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-center text-sm">
                <i class="fas fa-edit mr-1 sm:mr-2"></i>Edit
            </a>
            <button onclick="if(confirm('Yakin ingin menghapus?')) document.getElementById('delete-form').submit()" 
                    class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm">
                <i class="fas fa-trash mr-1 sm:mr-2"></i>Hapus
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - Profile Card -->
        <div class="lg:col-span-1 space-y-4 sm:space-y-6">
            <!-- Profile Photo & Status -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-green-500 overflow-hidden bg-gray-100 flex items-center justify-center mb-4">
                        <i class="fas fa-user text-4xl sm:text-5xl text-gray-400"></i>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-2">[Nama Pegawai]</h2>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-circle text-xs mr-1"></i> Aktif
                    </span>
                </div>
                <div class="mt-6 space-y-3 text-center">
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">NIK</p>
                        <p class="font-semibold text-gray-900">[NIK]</p>
                    </div>
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">Jabatan</p>
                        <p class="font-semibold text-gray-900">[Jabatan]</p>
                    </div>
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">Lokasi</p>
                        <p class="font-semibold text-gray-900">[Lokasi]</p>
                    </div>
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">Bergabung</p>
                        <p class="font-semibold text-gray-900">[Tanggal]</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="#" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-700">
                        <i class="fas fa-file-invoice w-5"></i>
                        <span class="ml-3">Lihat Slip Gaji</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span class="ml-3">Riwayat Absensi</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg text-yellow-700">
                        <i class="fas fa-plane w-5"></i>
                        <span class="ml-3">Daftar Cuti</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-700">
                        <i class="fas fa-clock w-5"></i>
                        <span class="ml-3">Lembur</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column - Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-circle text-green-600 mr-2"></i>
                    Data Pribadi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Tempat, Tanggal Lahir</p>
                        <p class="font-semibold text-gray-900">[Tempat], [Tanggal]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jenis Kelamin</p>
                        <p class="font-semibold text-gray-900">[Jenis Kelamin]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Agama</p>
                        <p class="font-semibold text-gray-900">[Agama]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status Perkawinan</p>
                        <p class="font-semibold text-gray-900">-</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Alamat</p>
                        <p class="font-semibold text-gray-900">[Alamat Lengkap]</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-phone text-green-600 mr-2"></i>
                    Informasi Kontak
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">No. Telepon</p>
                        <p class="font-semibold text-gray-900">[Telepon]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-semibold text-gray-900">[Email]</p>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-briefcase text-green-600 mr-2"></i>
                    Informasi Kepegawaian
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Jabatan</p>
                        <p class="font-semibold text-gray-900">[Jabatan]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Lokasi Kerja</p>
                        <p class="font-semibold text-gray-900">[Lokasi]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Masuk</p>
                        <p class="font-semibold text-gray-900">[Tanggal Masuk]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Masa Kerja</p>
                        <p class="font-semibold text-gray-900">[Masa Kerja]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status Pegawai</p>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Aktif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-university text-green-600 mr-2"></i>
                    Informasi Bank
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nama Bank</p>
                        <p class="font-semibold text-gray-900">[Bank]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">No. Rekening</p>
                        <p class="font-semibold text-gray-900">[Rekening]</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Atas Nama</p>
                        <p class="font-semibold text-gray-900">[Nama Pemegang]</p>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Hadir Bulan Ini</p>
                            <p class="text-xl sm:text-2xl font-bold">0 Hari</p>
                        </div>
                        <i class="fas fa-calendar-check text-3xl opacity-50"></i>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Sisa Cuti</p>
                            <p class="text-xl sm:text-2xl font-bold">12 Hari</p>
                        </div>
                        <i class="fas fa-plane text-3xl opacity-50"></i>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Total Lembur</p>
                            <p class="text-xl sm:text-2xl font-bold">0 Jam</p>
                        </div>
                        <i class="fas fa-clock text-3xl opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" action="{{ route('admin.workers.destroy', 1) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection
