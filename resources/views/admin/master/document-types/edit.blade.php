@extends('layouts.admin')

@section('title', 'Edit Tipe Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.master.document-types.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Edit Tipe Dokumen</h1>
            <p class="text-sm text-gray-600 mt-1">Ubah data tipe dokumen</p>
        </div>
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

    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.master.document-types.update', $documentType->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Tipe Dokumen <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $documentType->name) }}"
                       class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border rounded-lg">{{ old('description', $documentType->description) }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Format File -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Format File yang Diizinkan <span class="text-red-500">*</span>
                    </label>
                    @php
                        $currentFormats = old('file_formats', explode(',', $documentType->file_format ?? 'pdf,jpg,png'));
                        $currentFormats = array_filter($currentFormats);
                    @endphp
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="file_formats[]" value="pdf" 
                                   {{ in_array('pdf', $currentFormats) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">PDF (.pdf)</span>
                            <span class="text-xs text-gray-500">- Dokumen resmi</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="file_formats[]" value="jpg" 
                                   {{ in_array('jpg', $currentFormats) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">JPEG (.jpg)</span>
                            <span class="text-xs text-gray-500">- Foto/gambar</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="file_formats[]" value="jpeg" 
                                   {{ in_array('jpeg', $currentFormats) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">JPEG (.jpeg)</span>
                            <span class="text-xs text-gray-500">- Foto/gambar</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="file_formats[]" value="png" 
                                   {{ in_array('png', $currentFormats) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">PNG (.png)</span>
                            <span class="text-xs text-gray-500">- Gambar transparan</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="file_formats[]" value="doc" 
                                   {{ in_array('doc', $currentFormats) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Word (.doc)</span>
                            <span class="text-xs text-gray-500">- Dokumen teks</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="file_formats[]" value="docx" 
                                   {{ in_array('docx', $currentFormats) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Word (.docx)</span>
                            <span class="text-xs text-gray-500">- Dokumen teks modern</span>
                        </label>
                    </div>
                    <!-- Hidden input untuk backward compatibility -->
                    <input type="hidden" name="file_format" id="file_format_hidden" value="{{ old('file_format', $documentType->file_format ?? 'pdf,jpg,png') }}">
                    @error('file_format')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('file_formats')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ukuran File -->
                <div>
                    <label for="max_file_size_mb" class="block text-sm font-medium text-gray-700 mb-3">
                        Ukuran Maksimal File <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <input type="number" 
                                   id="max_file_size_mb" 
                                   min="0.1" 
                                   max="100" 
                                   step="0.1"
                                   value="{{ old('max_file_size_mb', ($documentType->max_file_size ?? 2048) / 1024) }}"
                                   class="w-24 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-center"
                                   placeholder="2.0">
                            <span class="text-sm font-medium text-gray-700">MB</span>
                        </div>
                        
                        <!-- Quick size buttons -->
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="setFileSize(1)" 
                                    class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border">
                                1 MB
                            </button>
                            <button type="button" onclick="setFileSize(2)" 
                                    class="px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 rounded-md border border-blue-300">
                                2 MB (Rekomendasi)
                            </button>
                            <button type="button" onclick="setFileSize(5)" 
                                    class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border">
                                5 MB
                            </button>
                            <button type="button" onclick="setFileSize(10)" 
                                    class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-md border">
                                10 MB
                            </button>
                        </div>

                        <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Panduan:</strong><br>
                            • 1-2 MB: Untuk dokumen scan biasa<br>
                            • 3-5 MB: Untuk foto berkualitas tinggi<br>
                            • 5-10 MB: Untuk dokumen dengan banyak halaman
                        </div>
                        
                        <!-- Hidden input for backend -->
                        <input type="hidden" 
                               name="max_file_size" 
                               id="max_file_size_kb" 
                               value="{{ old('max_file_size', $documentType->max_file_size ?? 2048) }}">
                    </div>
                    @error('max_file_size')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $documentType->is_active) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('admin.master.document-types.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi untuk mengatur ukuran file
function setFileSize(sizeInMB) {
    document.getElementById('max_file_size_mb').value = sizeInMB;
    document.getElementById('max_file_size_kb').value = sizeInMB * 1024;
}

// Konversi MB ke KB saat input berubah
document.getElementById('max_file_size_mb').addEventListener('input', function() {
    const mbValue = parseFloat(this.value) || 0;
    const kbValue = Math.round(mbValue * 1024);
    document.getElementById('max_file_size_kb').value = kbValue;
});

// Sinkronisasi checkbox dengan hidden input untuk file format
function updateFileFormatInput() {
    const checkboxes = document.querySelectorAll('input[name="file_formats[]"]:checked');
    const formats = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('file_format_hidden').value = formats.join(',');
}

// Event listener untuk checkbox
document.querySelectorAll('input[name="file_formats[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateFileFormatInput);
});

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Set initial file format
    updateFileFormatInput();
    
    // Set initial KB value based on MB input
    const mbInput = document.getElementById('max_file_size_mb');
    if (mbInput.value) {
        const mbValue = parseFloat(mbInput.value);
        document.getElementById('max_file_size_kb').value = Math.round(mbValue * 1024);
    }
});
</script>
@endsection
