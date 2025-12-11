@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.workers.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl sm:text-xl sm:text-2xl font-bold text-gray-900">Tambah Pegawai Baru</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">Lengkapi form di bawah untuk menambah pegawai</p>
        </div>
    </div>

    <form action="{{ route('admin.workers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
        @csrf
        
        <!-- Photo Upload -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-base sm:text-lg font-semibold text-gray-900 mb-4">Foto Pegawai</h3>
            <div x-data="{ preview: null }" class="flex items-start space-x-6">
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-lg border-2 border-gray-300 overflow-hidden bg-gray-100 flex items-center justify-center">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <i class="fas fa-user text-4xl text-gray-400"></i>
                        </template>
                    </div>
                </div>
                <div class="flex-grow">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                    <input type="file" name="photo" accept="image/*"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="mt-2 text-xs text-gray-500">Format: JPG, PNG (Max: 2MB)</p>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-base sm:text-lg font-semibold text-gray-900 mb-4">Data Pribadi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input type="text" name="place_of_birth" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="date_of_birth" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender_id" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Jenis Kelamin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Agama <span class="text-red-500">*</span></label>
                    <select name="religion_id" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Agama</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="address" rows="3" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Kontak</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">No. Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Data Kepegawaian</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Jabatan <span class="text-red-500">*</span></label>
                    <select name="position_id" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Jabatan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Lokasi Kerja <span class="text-red-500">*</span></label>
                    <select name="location_id" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Lokasi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Tanggal Masuk <span class="text-red-500">*</span></label>
                    <input type="date" name="hire_date" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="Active">Aktif</option>
                        <option value="Inactive">Non-Aktif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Informasi Bank</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                    <input type="text" name="bank_name" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">No. Rekening</label>
                    <input type="text" name="bank_account" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Atas Nama</label>
                    <input type="text" name="bank_account_holder" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                <a href="{{ route('admin.workers.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg">
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
