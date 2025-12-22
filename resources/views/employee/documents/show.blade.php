@extends('layouts.employee')

@section('title', 'Detail Dokumen')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee.documents.index') }}" 
           class="mr-4 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
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
        @elseif($document->status === 'approved')
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
        <h2 class="text-lg font-semibold text-gray-800 mb-3">File Dokumen</h2>
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-600">{{ basename($document->file_path) }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    Uploaded: {{ $document->created_at->format('d F Y H:i') }}
                </p>
            </div>
            <a href="{{ route('employee.documents.download', $document->id) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </a>
        </div>
    </div>

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

    <!-- Delete Button -->
    @if($document->status === 'pending')
        <div class="flex justify-end">
            <form action="{{ route('employee.documents.destroy', $document->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                        onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus Dokumen
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
