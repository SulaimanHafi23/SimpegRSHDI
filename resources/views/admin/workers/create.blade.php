@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 sm:gap-4 mb-6">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Tambah Pegawai Baru</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Lengkapi formulir data pegawai dengan benar</p>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5">
            <svg class="w-5 h-5 mt-0.5 shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold mb-1">Terdapat {{ $errors->count() }} kesalahan pada form:</p>
                <ul class="text-sm space-y-0.5 list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.workers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
        @csrf

        {{-- Section 1: Foto Pegawai --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800">Foto Pegawai</h2>
                    <p class="text-xs text-gray-400">Opsional</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                <div class="shrink-0">
                    <div id="photoPreview" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-br from-indigo-100 to-violet-100 border-2 border-dashed border-indigo-200 flex items-center justify-center overflow-hidden">
                        <svg id="photoPlaceholder" class="w-10 h-10 text-indigo-300" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <img id="photoImg" class="hidden w-full h-full object-cover" src="" alt="Preview Foto">
                    </div>
                </div>
                <div class="flex-1 w-full">
                    <label for="photo" class="relative flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-indigo-50 hover:border-indigo-400 transition-all group">
                        <div id="uploadText" class="flex flex-col items-center pointer-events-none">
                            <svg class="w-6 h-6 text-gray-300 group-hover:text-indigo-400 mb-1 transition" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs sm:text-sm text-gray-500"><span class="font-semibold text-indigo-600">Pilih foto</span> atau seret ke sini</p>
                            <p class="text-xs text-gray-400 mt-0.5">JPG, PNG (Maks. 2MB)</p>
                        </div>
                        <div id="uploadSelected" class="hidden items-center gap-2 pointer-events-none">
                            <svg class="w-5 h-5 text-indigo-500 shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span id="uploadSelectedName" class="text-sm font-medium text-indigo-700 truncate max-w-xs"></span>
                        </div>
                        <input type="file" name="photo" id="photo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="handlePhotoSelect(this)">
                    </label>
                    @error('photo')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Data Pribadi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Data Pribadi</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700 mb-1.5">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" id="nip" required value="{{ old('nip') }}" placeholder="Masukkan NIP"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm sm:text-base @error('nip') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('nip')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Masukkan nama lengkap"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm sm:text-base @error('name') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="birth_place" class="block text-sm font-medium text-gray-700 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input type="text" name="birth_place" id="birth_place" required value="{{ old('birth_place') }}" placeholder="Masukkan tempat lahir"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm sm:text-base @error('birth_place') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('birth_place')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="birth_date" id="birth_date" required value="{{ old('birth_date') }}" max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm sm:text-base @error('birth_date') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('birth_date')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="gender_id" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender_id" id="gender_id" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm sm:text-base @error('gender_id') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        @foreach($genders as $gender)
                            <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                        @endforeach
                    </select>
                    @error('gender_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="religion_id" class="block text-sm font-medium text-gray-700 mb-1.5">Agama <span class="text-red-500">*</span></label>
                    <select name="religion_id" id="religion_id" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm sm:text-base @error('religion_id') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">-- Pilih Agama --</option>
                        @foreach($religions as $religion)
                            <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>{{ $religion->name }}</option>
                        @endforeach
                    </select>
                    @error('religion_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Alamat <span class="text-xs font-normal text-gray-400 ml-1">(Opsional)</span></label>
                    <textarea name="address" id="address" rows="3" placeholder="Masukkan alamat lengkap"
                              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none text-sm sm:text-base @error('address') border-red-400 bg-red-50 @else border-gray-200 @enderror">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Kontak --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Informasi Kontak</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </span>
                        <input type="tel" name="phone_number" id="phone_number" required value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx"
                               class="w-full pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition text-sm sm:text-base @error('phone_number') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    </div>
                    @error('phone_number')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </span>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="nama@email.com"
                               class="w-full pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition text-sm sm:text-base @error('email') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    </div>
                    @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 4: Data Kepegawaian --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Data Kepegawaian</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">Departemen <span class="text-red-500">*</span></label>
                    <select name="department_id" id="department_id" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-sm sm:text-base @error('department_id') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Masuk <span class="text-red-500">*</span></label>
                    <input type="date" name="hire_date" id="hire_date" required value="{{ old('hire_date') }}"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-sm sm:text-base @error('hire_date') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('hire_date')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="employment_status" class="block text-sm font-medium text-gray-700 mb-1.5">Status Kepegawaian <span class="text-red-500">*</span></label>
                    <select name="employment_status" id="employment_status" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-sm sm:text-base @error('employment_status') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">-- Pilih Status --</option>
                        <option value="permanent"  {{ old('employment_status') == 'permanent' ? 'selected' : '' }}>Tetap</option>
                        <option value="contract"   {{ old('employment_status', 'contract') == 'contract' ? 'selected' : '' }}>Kontrak</option>
                        <option value="internship" {{ old('employment_status') == 'internship' ? 'selected' : '' }}>Magang</option>
                    </select>
                    @error('employment_status')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-sm sm:text-base @error('status') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                        <option value="">-- Pilih Status --</option>
                        <option value="active"   {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        <option value="resigned" {{ old('status') == 'resigned' ? 'selected' : '' }}>Resign</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="resign_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tanggal Resign <span class="ml-1 text-xs font-normal text-gray-400">(Opsional)</span>
                    </label>
                    <input type="date" name="resign_date" id="resign_date" value="{{ old('resign_date') }}"
                           class="w-full sm:w-1/2 px-3 sm:px-4 py-2.5 sm:py-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-sm sm:text-base @error('resign_date') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('resign_date')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1 pb-2">
            <a href="{{ route('admin.workers.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="w-full sm:w-auto sm:flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
                <svg id="submitIcon" class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span id="submitText">Simpan Pegawai</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function handlePhotoSelect(input) {
    const uploadText       = document.getElementById('uploadText');
    const uploadSelected   = document.getElementById('uploadSelected');
    const uploadName       = document.getElementById('uploadSelectedName');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const photoImg         = document.getElementById('photoImg');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        uploadText.classList.add('hidden');
        uploadSelected.classList.remove('hidden');
        uploadSelected.classList.add('flex');
        uploadName.textContent = file.name;

        const reader = new FileReader();
        reader.onload = e => {
            photoPlaceholder.classList.add('hidden');
            photoImg.src = e.target.result;
            photoImg.classList.remove('hidden');
        };
        reader.readAsDataURL(file);

        if (file.size > 2 * 1024 * 1024) {
            compressImage(file, 0.5).then(compressed => {
                const dt = new DataTransfer();
                dt.items.add(compressed);
                input.files = dt.files;
            }).catch(err => console.error('Compress error:', err));
        }
    } else {
        uploadText.classList.remove('hidden');
        uploadSelected.classList.add('hidden');
        uploadSelected.classList.remove('flex');
        photoPlaceholder.classList.remove('hidden');
        photoImg.classList.add('hidden');
        photoImg.src = '';
    }
}

function compressImage(file, maxSizeMB = 0.5) {
    return new Promise((resolve, reject) => {
        const maxSize = maxSizeMB * 1024 * 1024;
        if (file.size <= maxSize) { resolve(file); return; }
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = e => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let { width, height } = img;
                const maxDim = 1200;
                if (width > height && width > maxDim) { height = (height * maxDim) / width; width = maxDim; }
                else if (height > maxDim) { width = (width * maxDim) / height; height = maxDim; }
                canvas.width = width; canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                let quality = 0.8;
                const tryCompress = () => {
                    canvas.toBlob(blob => {
                        if (blob.size <= maxSize || quality <= 0.1) {
                            resolve(new File([blob], file.name, { type: 'image/jpeg', lastModified: Date.now() }));
                        } else { quality -= 0.1; tryCompress(); }
                    }, 'image/jpeg', quality);
                };
                tryCompress();
            };
            img.onerror = reject;
        };
        reader.onerror = reject;
    });
}

document.querySelector('form').addEventListener('submit', function() {
    const btn  = document.getElementById('submitBtn');
    const icon = document.getElementById('submitIcon');
    const text = document.getElementById('submitText');
    btn.disabled = true;
    icon.innerHTML = '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>';
    icon.classList.add('animate-spin');
    text.textContent = 'Menyimpan...';
});
</script>
@endpush
@endsection
