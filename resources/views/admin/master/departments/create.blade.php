@extends('layouts.admin')

@section('title', 'Tambah Departemen')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.departments.index') }}" class="hover:text-green-600">Departemen</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Tambah</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-plus-circle text-green-600 mr-2"></i>
            Tambah Departemen Baru
        </h1>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="font-bold">Terdapat kesalahan pada form!</strong>
                    <ul class="mt-2 ml-4 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.master.departments.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        Kode Departemen <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="code"
                           id="code"
                           value="{{ old('code') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('code') border-red-500 @enderror"
                           placeholder="Contoh: DEPT-001"
                           required>
                    @error('code')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Departemen <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Nama departemen"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="description"
                              id="description"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Deskripsi departemen (opsional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">Departemen Aktif</span>
                    </label>
                </div>

                <!-- Standby Hari Libur -->
                <div class="md:col-span-2">
                    <label class="flex items-start space-x-2">
                        <input type="checkbox"
                               name="requires_holiday_attendance"
                               value="1"
                               {{ old('requires_holiday_attendance') ? 'checked' : '' }}
                               class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 mt-0.5">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Tetap Standby pada Hari Libur Nasional</span>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle text-orange-500 mr-1"></i>
                                Jika dicentang, pegawai di departemen ini tetap wajib absen pada tanggal merah / hari libur nasional (contoh: IGD, Rawat Inap, UGD).
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                <a href="{{ route('admin.master.departments.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
