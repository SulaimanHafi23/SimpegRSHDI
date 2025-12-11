@extends('layouts.admin')

@section('title', 'Detail Absensi')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.absents.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Absensi</h1>
                <p class="text-sm text-gray-600 mt-1">Informasi lengkap kehadiran pegawai</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <button onclick="if(confirm('Yakin ingin menghapus?')) document.getElementById('delete-form').submit()" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                <i class="fas fa-trash mr-2"></i>Hapus
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - Summary Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex flex-col items-center mb-6">
                    <div class="w-24 h-24 rounded-full border-4 border-green-500 overflow-hidden bg-gray-100 flex items-center justify-center mb-4">
                        <i class="fas fa-user text-4xl text-gray-400"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 text-center">[Nama Pegawai]</h2>
                    <p class="text-sm text-gray-600">[Jabatan]</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-t">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Hadir
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-t">
                        <span class="text-sm text-gray-600">Tanggal</span>
                        <span class="font-semibold text-gray-900">[Tanggal]</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-t">
                        <span class="text-sm text-gray-600">Shift</span>
                        <span class="font-semibold text-gray-900">[Shift]</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-t">
                        <span class="text-sm text-gray-600">Lokasi</span>
                        <span class="font-semibold text-gray-900">[Lokasi]</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Riwayat Bulan Ini</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Hadir</span>
                            <span class="font-semibold text-green-600">0 hari</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Terlambat</span>
                            <span class="font-semibold text-yellow-600">0 hari</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Alpha</span>
                            <span class="font-semibold text-red-600">0 hari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Attendance Time -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-green-600 mr-2"></i>
                    Waktu Kehadiran
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-medium mb-1">Check In</p>
                                <p class="text-xl sm:text-xl sm:text-2xl font-bold text-blue-900">[Jam Masuk]</p>
                                <p class="text-xs text-blue-700 mt-1">[Tanggal]</p>
                            </div>
                            <i class="fas fa-sign-in-alt text-3xl text-blue-400"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-purple-600 font-medium mb-1">Check Out</p>
                                <p class="text-xl sm:text-xl sm:text-2xl font-bold text-purple-900">[Jam Pulang]</p>
                                <p class="text-xs text-purple-700 mt-1">[Tanggal]</p>
                            </div>
                            <i class="fas fa-sign-out-alt text-3xl text-purple-400"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Total Jam Kerja</p>
                        <p class="text-xl font-bold text-gray-900">8 Jam</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Keterlambatan</p>
                        <p class="text-xl font-bold text-yellow-600">0 Menit</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600 mb-1">Lembur</p>
                        <p class="text-xl font-bold text-green-600">0 Jam</p>
                    </div>
                </div>
            </div>

            <!-- Shift Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                    Informasi Shift
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nama Shift</p>
                        <p class="font-semibold text-gray-900">[Nama Shift]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jadwal Shift</p>
                        <p class="font-semibold text-gray-900">[Jam Mulai] - [Jam Selesai]</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Toleransi Terlambat</p>
                        <p class="font-semibold text-gray-900">15 Menit</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Lokasi Absen</p>
                        <p class="font-semibold text-gray-900">[Lokasi]</p>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-green-600 mr-2"></i>
                    Informasi Tambahan
                </h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Catatan</p>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900">[Catatan atau keterangan tambahan]</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Dibuat Oleh</p>
                            <p class="font-semibold text-gray-900">Admin</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Dibuat Pada</p>
                            <p class="font-semibold text-gray-900">[Tanggal Dibuat]</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos (if available) -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-camera text-green-600 mr-2"></i>
                    Foto Absensi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Foto Check In</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg h-48 flex items-center justify-center bg-gray-50">
                            <div class="text-center">
                                <i class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">Tidak ada foto</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Foto Check Out</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg h-48 flex items-center justify-center bg-gray-50">
                            <div class="text-center">
                                <i class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">Tidak ada foto</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" action="{{ route('admin.absents.destroy', 1) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection
