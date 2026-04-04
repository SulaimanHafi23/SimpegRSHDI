@extends('layouts.employee')

@section('title', 'Detail Dokumen')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <!-- <a href="{{ route('employee.documents.index') }}"
           class="mr-4 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a> -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Detail Dokumen</h1>
            <p class="text-gray-600 mt-1">{{ $document->documentType->name ?? '-' }}</p>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if($document->status === 'pending')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Menunggu Verifikasi
            </span>
        @elseif($document->status === 'verified')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Terverifikasi
            </span>
        @else
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Ditolak
            </span>
        @endif
    </div>

    <!-- Document Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dokumen</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Jenis Dokumen</label>
                <p class="text-lg font-medium text-gray-800">{{ $document->documentType->name ?? '-' }}</p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Nama File</label>
                <p class="text-lg font-medium text-gray-800">{{ $document->file_name }}</p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Ukuran File</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ number_format($document->file_size / 1024, 2) }} KB
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Tanggal Kadaluarsa</label>
                <p class="text-lg font-medium text-gray-800">
                    @if($document->expired_date)
                        {{ \Carbon\Carbon::parse($document->expired_date)->format('d F Y') }}
                        @if(\Carbon\Carbon::parse($document->expired_date)->isPast())
                            <span class="text-xs text-red-600 ml-2">(Kadaluarsa)</span>
                        @endif
                    @else
                        Tidak ada
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($document->notes)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Catatan</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $document->notes }}</p>
        </div>
    @endif

    <!-- File Preview -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">File Dokumen</h2>

        <!-- File Info -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mb-4">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @php
                        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                        $isPdf = $extension === 'pdf';
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    @endphp

                    @if($isPdf)
                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    @elseif($isImage)
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    @else
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ $document->file_name }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-calendar mr-1"></i>
                        Uploaded: {{ $document->created_at->format('d F Y H:i') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-file mr-1"></i>
                        {{ number_format($document->file_size / 1024, 2) }} KB • {{ strtoupper($extension) }}
                    </p>
                </div>
            </div>
            <a href="{{ route('employee.documents.download', $document->id) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </a>
        </div>

        <!-- Preview Section -->
        <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
            @if($isPdf)
                <!-- PDF Preview -->
                <div class="w-full" style="min-height: 600px;">
                    <iframe
                        src="{{ route('employee.documents.preview', $document->id) }}#toolbar=1&navpanes=0&scrollbar=1"
                        class="w-full"
                        style="height: 800px; border: none;"
                        type="application/pdf">
                        <p class="p-4 text-center text-gray-600">
                            Browser Anda tidak mendukung preview PDF.
                            <a href="{{ route('employee.documents.download', $document->id) }}" class="text-blue-600 hover:underline">
                                Klik di sini untuk download
                            </a>
                        </p>
                    </iframe>
                </div>

                <!-- Alternative PDF viewer button -->
                <div class="p-4 bg-white border-t">
                    <button onclick="openPdfInNewTab()"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka di Tab Baru
                    </button>
                </div>
            @elseif($isImage)
                <!-- Image Preview -->
                <div class="relative group">
                    <img
                        src="{{ route('employee.documents.preview', $document->id) }}"
                        alt="{{ $document->file_name }}"
                        class="w-full h-auto max-h-[800px] object-contain cursor-pointer"
                        onclick="openImageModal()"
                        id="documentImage">

                    <!-- Overlay hint -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300 flex items-center justify-center pointer-events-none">
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white px-4 py-2 rounded-lg shadow-lg text-gray-800 text-sm font-medium">
                            <i class="fas fa-search-plus mr-2"></i>
                            Klik untuk memperbesar
                        </span>
                    </div>
                </div>
            @else
                <!-- Unsupported file type -->
                <div class="p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-600 mb-4">Preview tidak tersedia untuk file tipe {{ strtoupper($extension) }}</p>
                    <a href="{{ route('employee.documents.download', $document->id) }}"
                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download untuk melihat
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Image Modal for Fullscreen View -->
    @if($isImage)
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center p-4" onclick="closeImageModal()">
        <div class="relative max-w-7xl max-h-full">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img
                src="{{ route('employee.documents.preview', $document->id) }}"
                alt="{{ $document->file_name }}"
                class="max-w-full max-h-screen object-contain"
                onclick="event.stopPropagation()">
        </div>
    </div>
    @endif

    <script>
        @if($isPdf)
        function openPdfInNewTab() {
            window.open('{{ route('employee.documents.preview', $document->id) }}', '_blank');
        }
        @endif

        @if($isImage)
        function openImageModal() {
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('imageModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('imageModal').classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
        @endif
    </script>

    <!-- Verification Info -->
    @if($document->status !== 'pending')
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Verifikasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600">Diverifikasi Oleh</label>
                    <p class="text-lg font-medium text-gray-800">{{ $document->verifiedBy->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Tanggal Verifikasi</label>
                    <p class="text-lg font-medium text-gray-800">
                        {{ $document->verified_at ? \Carbon\Carbon::parse($document->verified_at)->format('d F Y H:i') : '-' }}
                    </p>
                </div>
            </div>
            @if($document->verification_notes)
                <div class="mt-4">
                    <label class="text-sm text-gray-600">Catatan Verifikasi</label>
                    <p class="text-gray-700 mt-1">{{ $document->verification_notes }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
