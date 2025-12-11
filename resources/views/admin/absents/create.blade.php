@extends('layouts.admin')

@section('title', 'Absen Manual')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.absents.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Absen Manual</h1>
            <p class="text-sm text-gray-600 mt-1">Input data absensi pegawai secara manual</p>
        </div>
    </div>

    <form action="{{ route('admin.absents.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Date & Worker Selection -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Informasi Absensi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Pegawai <span class="text-red-500">*</span></label>
                    <select name="worker_id" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Pegawai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>
        </div>

        <!-- Attendance Details -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Detail Kehadiran</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Shift <span class="text-red-500">*</span></label>
                    <select name="shift_id" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Shift</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Status Kehadiran <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Status</option>
                        <option value="Present">Hadir</option>
                        <option value="Late">Terlambat</option>
                        <option value="Leave">Cuti</option>
                        <option value="Sick">Sakit</option>
                        <option value="Absent">Alpha</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Jam Masuk</label>
                    <input type="time" name="check_in" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Jam Pulang</label>
                    <input type="time" name="check_out" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Lokasi Absen</label>
                    <input type="text" name="location" placeholder="Contoh: Kantor Pusat, Rumah Sakit, WFH" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan tambahan (opsional)" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                <a href="{{ route('admin.absents.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
