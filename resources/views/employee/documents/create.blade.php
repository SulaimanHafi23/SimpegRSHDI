@extends('layouts.employee')

@section('title', 'Upload Dokumen')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Upload Dokumen</h1>
        <p class="text-gray-600 mt-1">Upload dokumen sesuai dengan posisi Anda</p>
        @if(auth()->user()->worker?->department)
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-building mr-2"></i>
                {{ auth()->user()->worker->department->name }}
            </div>
        @endif
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        @if($documentTypes->isNotEmpty())
            <!-- Document Summary Info -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Informasi Dokumen Posisi Anda:</h3>
                        @php
                            $requiredDocs = $documentTypes->where('is_required', true);
                            $optionalDocs = $documentTypes->where('is_required', false);
                            $uploadedCount = count($uploadedDocTypes ?? []);
                        @endphp
                        
                        <div class="mb-3 p-2 bg-white rounded border border-blue-200">
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="text-center">
                                    <div class="font-bold text-lg text-blue-600">{{ $documentTypes->count() }}</div>
                                    <div class="text-gray-600">Total Dokumen</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-lg text-green-600">{{ $uploadedCount }}</div>
                                    <div class="text-gray-600">Sudah Upload</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-lg text-orange-600">{{ $documentTypes->count() - $uploadedCount }}</div>
                                    <div class="text-gray-600">Belum Upload</div>
                                </div>
                            </div>
                        </div>
                        
                        @if($requiredDocs->isNotEmpty())
                            <div class="mb-2">
                                <span class="text-sm font-medium text-red-700">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Dokumen Wajib ({{ $requiredDocs->count() }}):
                                </span>
                                <ul class="text-xs text-gray-700 ml-4 mt-1 list-disc">
                                    @foreach($requiredDocs->take(3) as $doc)
                                        <li>
                                            {{ $doc->name }}
                                            @if(in_array($doc->id, $uploadedDocTypes ?? []))
                                                <span class="text-green-600 font-semibold">✓</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if($requiredDocs->count() > 3)
                                        <li class="text-gray-500">... dan {{ $requiredDocs->count() - 3 }} lainnya</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                        
                        @if($optionalDocs->isNotEmpty())
                            <div class="mb-2">
                                <span class="text-sm font-medium text-blue-700">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Dokumen Opsional ({{ $optionalDocs->count() }}):
                                </span>
                                <ul class="text-xs text-gray-700 ml-4 mt-1 list-disc">
                                    @foreach($optionalDocs->take(3) as $doc)
                                        <li>
                                            {{ $doc->name }}
                                            @if(in_array($doc->id, $uploadedDocTypes ?? []))
                                                <span class="text-green-600 font-semibold">✓</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if($optionalDocs->count() > 3)
                                        <li class="text-gray-500">... dan {{ $optionalDocs->count() - 3 }} lainnya</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mt-3 pt-3 border-t border-blue-200">
                            <p class="text-xs text-gray-700">
                                <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                                <strong>Tip:</strong> Dokumen dengan tanda ✓ sudah pernah diupload. Anda tetap bisa mengupload ulang untuk pembaruan atau perpanjangan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
                        @php
                            $isUploaded = in_array($type->id, $uploadedDocTypes ?? []);
                            $stats = $documentStats[$type->id] ?? null;
                        @endphp
                        <option value="{{ $type->id }}" 
                                data-formats="{{ $type->file_format ?? 'pdf,jpg,jpeg,png' }}"
                                data-max-size="{{ $type->max_file_size ?? 5120 }}"
                                data-required="{{ $type->is_required ? 'true' : 'false' }}"
                                data-uploaded="{{ $isUploaded ? 'true' : 'false' }}"
                                data-stats="{{ $stats ? json_encode($stats) : '' }}"
                                {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
                            @if($isUploaded)
                                ✓ 
                            @endif
                            {{ $type->name }}
                            @if($stats)
                                ({{ $stats['approved'] }}/{{ $stats['total'] }})
                            @endif
                            @if($type->description)
                                - {{ Str::limit($type->description, 30) }}
                            @endif
                        </option>
                    @empty
                        <option value="" disabled>
                            @if(auth()->user()->worker?->department)
                                Tidak ada dokumen yang diperlukan untuk posisi {{ auth()->user()->worker->department->name }}
                            @else
                                Tidak ada dokumen yang tersedia
                            @endif
                        </option>
                    @endforelse
                </select>
                @error('document_type_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                
                <!-- Document Status Info -->
                <div id="document-status-info" class="mt-2 hidden">
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Status Dokumen Ini:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs" id="status-details">
                                    <!-- Will be populated by JavaScript -->
                                </ul>
                                <p class="mt-2 text-xs italic">
                                    <i class="fas fa-upload mr-1"></i>
                                    Anda dapat mengupload dokumen baru untuk pembaruan atau perpanjangan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if(auth()->user()->worker?->department_id)
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i> 
                        Menampilkan {{ $documentTypes->count() }} jenis dokumen untuk posisi {{ auth()->user()->worker->department->name ?? '-' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-check text-green-600"></i> 
                        Tanda centang (✓) menunjukkan dokumen yang sudah pernah diupload
                    </p>
                @endif
                @if($documentTypes->isEmpty())
                    <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            @if(auth()->user()->worker?->department)
                                Tidak ada dokumen yang diperlukan untuk posisi Anda.
                            @else
                                Anda belum terdaftar di departemen mana pun. Hubungi HR untuk informasi lebih lanjut.
                            @endif
                        </p>
                    </div>
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
                <p class="mt-1 text-xs text-gray-500" id="file-info">
                    Format: PDF, JPG, JPEG, PNG. Maksimal 5MB
                </p>
                <div id="file-preview" class="mt-2 hidden">
                    <div class="flex items-center p-2 bg-gray-50 rounded border">
                        <i class="fas fa-file text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600" id="file-name"></span>
                        <span class="text-xs text-gray-500 ml-2" id="file-size"></span>
                    </div>
                </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const documentTypeSelect = document.getElementById('document_type_id');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('file-info');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    // Update file constraints based on document type selection
    documentTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const formats = selectedOption.getAttribute('data-formats') || 'pdf,jpg,jpeg,png';
        const maxSize = selectedOption.getAttribute('data-max-size') || 5120;
        const isUploaded = selectedOption.getAttribute('data-uploaded') === 'true';
        const statsJson = selectedOption.getAttribute('data-stats');

        // Update accept attribute
        const acceptFormats = formats.split(',').map(format => '.' + format.trim()).join(',');
        fileInput.setAttribute('accept', acceptFormats);

        // Update info text
        const maxSizeMB = Math.round(maxSize / 1024);
        fileInfo.textContent = `Format: ${formats.toUpperCase().replace(/,/g, ', ')}. Maksimal ${maxSizeMB}MB`;

        // Show/hide document status info
        const statusInfo = document.getElementById('document-status-info');
        const statusDetails = document.getElementById('status-details');
        
        if (isUploaded && statsJson) {
            try {
                const stats = JSON.parse(statsJson);
                statusDetails.innerHTML = '';
                
                // Add status items
                if (stats.total) {
                    statusDetails.innerHTML += `<li>Total dokumen diupload: <strong>${stats.total}</strong></li>`;
                }
                if (stats.approved > 0) {
                    statusDetails.innerHTML += `<li class="text-green-700">✓ Terverifikasi: <strong>${stats.approved}</strong></li>`;
                }
                if (stats.pending > 0) {
                    statusDetails.innerHTML += `<li class="text-yellow-700">⏳ Menunggu verifikasi: <strong>${stats.pending}</strong></li>`;
                }
                if (stats.rejected > 0) {
                    statusDetails.innerHTML += `<li class="text-red-700">✗ Ditolak: <strong>${stats.rejected}</strong></li>`;
                }
                if (stats.latest_status) {
                    const statusMap = {
                        'approved': '✓ Terverifikasi',
                        'pending': '⏳ Menunggu',
                        'rejected': '✗ Ditolak'
                    };
                    statusDetails.innerHTML += `<li>Status terakhir: <strong>${statusMap[stats.latest_status] || stats.latest_status}</strong></li>`;
                }
                
                statusInfo.classList.remove('hidden');
            } catch (e) {
                statusInfo.classList.add('hidden');
            }
        } else {
            statusInfo.classList.add('hidden');
        }

        // Clear file input if format no longer valid
        if (fileInput.files.length > 0) {
            const currentFile = fileInput.files[0];
            const fileExtension = currentFile.name.split('.').pop().toLowerCase();
            if (!formats.split(',').map(f => f.trim()).includes(fileExtension)) {
                fileInput.value = '';
                filePreview.classList.add('hidden');
            }
        }
    });

    // File preview and validation
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        
        if (file) {
            // Get current constraints
            const selectedOption = documentTypeSelect.options[documentTypeSelect.selectedIndex];
            const allowedFormats = (selectedOption.getAttribute('data-formats') || 'pdf,jpg,jpeg,png').split(',');
            const maxSize = parseInt(selectedOption.getAttribute('data-max-size') || 5120) * 1024; // Convert to bytes

            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileSizeBytes = file.size;

            // Validate file format
            if (!allowedFormats.map(f => f.trim()).includes(fileExtension)) {
                alert('Format file tidak sesuai dengan jenis dokumen yang dipilih!');
                this.value = '';
                filePreview.classList.add('hidden');
                return;
            }

            // Validate file size
            if (fileSizeBytes > maxSize) {
                const maxSizeMB = Math.round(maxSize / 1024 / 1024);
                alert(`Ukuran file terlalu besar! Maksimal ${maxSizeMB}MB untuk jenis dokumen ini.`);
                this.value = '';
                filePreview.classList.add('hidden');
                return;
            }

            // Show preview
            fileName.textContent = file.name;
            fileSize.textContent = `(${(fileSizeBytes / 1024 / 1024).toFixed(2)} MB)`;
            filePreview.classList.remove('hidden');
        } else {
            filePreview.classList.add('hidden');
        }
    });

    // Form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        if (documentTypeSelect.value === '') {
            e.preventDefault();
            alert('Pilih jenis dokumen terlebih dahulu!');
            documentTypeSelect.focus();
            return;
        }

        if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Pilih file dokumen yang akan diupload!');
            fileInput.focus();
            return;
        }
    });
});
</script>
@endsection
