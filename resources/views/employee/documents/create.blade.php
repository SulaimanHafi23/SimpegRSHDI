@extends('layouts.employee')

@section('title', 'Upload Dokumen')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Upload Dokumen</h1>
        <p class="text-gray-600 mt-1">Upload dokumen pribadi baru</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Document Type -->
            <div class="mb-4">
                <label for="document_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Jenis Dokumen <span class="text-red-500">*</span>
                </label>
                <select name="document_type_id" 
                        id="document_type_id" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('document_type_id') border-red-500 @enderror">
                    <option value="">Pilih Jenis Dokumen</option>
                    @forelse($documentTypes as $type)
                        <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                            @if($type->is_required ?? false)
                                <span class="text-red-500">*</span>
                            @endif
                        </option>
                    @empty
                        <option value="" disabled>Tidak ada dokumen yang tersedia untuk departemen Anda</option>
                    @endforelse
                </select>
                @error('document_type_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                @if(auth()->user()->worker?->department_id)
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i> Menampilkan dokumen untuk departemen: {{ auth()->user()->worker->department->name ?? '-' }}
                    </p>
                @endif
            </div>

            <!-- Expiry Date -->
            <div class="mb-4">
                <label for="expired_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Kadaluarsa (Opsional)
                </label>
                <input type="date" 
                       name="expired_date" 
                       id="expired_date" 
                       value="{{ old('expired_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('expired_date') border-red-500 @enderror">
                @error('expired_date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika dokumen tidak memiliki masa berlaku</p>
            </div>

            <!-- File -->
            <div class="mb-4">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                    File Dokumen <span class="text-red-500">*</span>
                </label>
                <input type="file" 
                       name="file" 
                       id="file" 
                       required
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('file') border-red-500 @enderror">
                @error('file')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Format: PDF, JPG, JPEG, PNG. Maksimal 5MB</p>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('notes') border-red-500 @enderror"
                          placeholder="Tambahkan catatan jika diperlukan">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload Dokumen
                </button>
                <a href="{{ route('employee.documents.index') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
