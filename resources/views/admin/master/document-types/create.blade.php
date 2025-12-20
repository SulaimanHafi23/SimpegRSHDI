@extends('layouts.admin')

@section('title', 'Tambah Tipe Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.master.document-types.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Tambah Tipe Dokumen</h1>
            <p class="text-sm text-gray-600 mt-1">Tambah tipe dokumen baru</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.master.document-types.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Tipe Dokumen <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                       placeholder="Contoh: KTP, NPWP, Ijazah, Sertifikat" required>
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border rounded-lg">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="file_format" class="block text-sm font-medium text-gray-700 mb-2">Format File (pisahkan dengan koma)</label>
                    <input type="text" name="file_format" id="file_format" value="{{ old('file_format', 'pdf,jpg,png') }}" class="w-full px-3 py-2 border rounded-lg">
                    @error('file_format')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_file_size" class="block text-sm font-medium text-gray-700 mb-2">Ukuran Maks (KB)</label>
                    <input type="number" name="max_file_size" id="max_file_size" value="{{ old('max_file_size', 2048) }}" class="w-full px-3 py-2 border rounded-lg">
                    @error('max_file_size')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('admin.master.document-types.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
