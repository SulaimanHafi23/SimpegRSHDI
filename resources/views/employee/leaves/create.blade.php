@extends('layouts.employee')

@section('title', 'Ajukan Cuti')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Ajukan Cuti</h1>
        <p class="text-gray-600 mt-1">Buat permohonan cuti baru</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.leaves.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Leave Type -->
            <div class="mb-4">
                <label for="leave_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Jenis Cuti <span class="text-red-500">*</span>
                </label>
                <select name="leave_type_id" 
                        id="leave_type_id" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('leave_type_id') border-red-500 @enderror">
                    <option value="">Pilih Jenis Cuti</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->max_days }} hari)
                        </option>
                    @endforeach
                </select>
                @error('leave_type_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Start Date -->
            <div class="mb-4">
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Mulai <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       name="start_date" 
                       id="start_date" 
                       required
                       value="{{ old('start_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('start_date') border-red-500 @enderror">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- End Date -->
            <div class="mb-4">
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Selesai <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       name="end_date" 
                       id="end_date" 
                       required
                       value="{{ old('end_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('end_date') border-red-500 @enderror">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason -->
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" 
                          id="reason" 
                          rows="4" 
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('reason') border-red-500 @enderror"
                          placeholder="Jelaskan alasan pengajuan cuti Anda">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Document -->
            <div class="mb-6">
                <label for="document" class="block text-sm font-medium text-gray-700 mb-2">
                    Dokumen Pendukung (Opsional)
                </label>
                <input type="file" 
                       name="document" 
                       id="document" 
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('document') border-red-500 @enderror">
                @error('document')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Format: PDF, JPG, JPEG, PNG. Maksimal 2MB</p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Ajukan Cuti
                </button>
                <a href="{{ route('employee.leaves.index') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
