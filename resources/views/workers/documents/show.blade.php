@extends('layouts.workers')

@section('title', 'Detail Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">Detail Dokumen</h2>
            <p class="text-sm sm:text-base text-gray-600">Informasi lengkap dokumen Anda</p>
        </div>
        <a href="{{ route('workers.documents') }}" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Document Preview -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Preview Dokumen</h3>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50" style="min-height: 400px;">
                    @php
                        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                    @endphp

                    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                        <img src="{{ Storage::url($document->file_path) }}" alt="Document Preview" class="w-full h-auto">
                    @elseif(strtolower($extension) === 'pdf')
                        <iframe src="{{ Storage::url($document->file_path) }}" class="w-full" style="height: 600px;"></iframe>
                    @else
                        <div class="flex flex-col items-center justify-center h-96 text-gray-500">
                            <i class="fas fa-file text-6xl mb-4"></i>
                            <p class="text-lg">Preview tidak tersedia untuk tipe file ini</p>
                            <a href="{{ route('workers.documents.download', $document->id) }}" class="mt-4 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-download mr-2"></i>Download File
                            </a>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-4">
                    <a href="{{ route('workers.documents.download', $document->id) }}" class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center">
                        <i class="fas fa-download mr-2"></i>Download Dokumen
                    </a>
                    @if($document->status !== 'Approved')
                    <button onclick="deleteDocument({{ $document->id }})" class="flex-1 sm:flex-none px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Information -->
        <div class="space-y-4">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Status Dokumen</h3>
                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold mb-3
                        {{ $document->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $document->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $document->status === 'Rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        <i class="fas fa-circle mr-2 text-xs"></i>
                        @if($document->status === 'Approved')
                            Disetujui
                        @elseif($document->status === 'Rejected')
                            Ditolak
                        @else
                            Pending
                        @endif
                    </div>
                    
                    @if($document->status === 'Approved')
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-600 mr-1"></i>
                            Diverifikasi oleh {{ $document->verifier->name ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $document->verified_at?->format('d M Y H:i') }}
                        </p>
                    @elseif($document->status === 'Rejected')
                        <div class="mt-3 p-3 bg-red-50 rounded-lg text-left">
                            <p class="text-sm font-semibold text-red-900 mb-1">Alasan Penolakan:</p>
                            <p class="text-sm text-red-700">{{ $document->rejection_reason ?? 'Tidak ada keterangan' }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            Menunggu verifikasi dari admin
                        </p>
                    @endif
                </div>
            </div>

            <!-- Document Details -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Informasi Dokumen</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Jenis:</span>
                        <span class="font-semibold text-gray-900">{{ $document->fileRequirement->documentType->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Nama File:</span>
                        <span class="font-semibold text-gray-900 truncate max-w-[150px]" title="{{ $document->file_name }}">
                            {{ $document->file_name }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Ukuran:</span>
                        <span class="font-semibold text-gray-900">
                            @if($document->file_size < 1024)
                                {{ $document->file_size }} bytes
                            @elseif($document->file_size < 1048576)
                                {{ number_format($document->file_size / 1024, 2) }} KB
                            @else
                                {{ number_format($document->file_size / 1048576, 2) }} MB
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Tipe:</span>
                        <span class="font-semibold text-gray-900">
                            {{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}
                        </span>
                    </div>
                    @if($document->expired_date)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Berlaku Hingga:</span>
                        <span class="font-semibold {{ $document->is_expired ? 'text-red-600' : 'text-gray-900' }}">
                            {{ \Carbon\Carbon::parse($document->expired_date)->format('d M Y') }}
                            @if($document->is_expired)
                                <i class="fas fa-exclamation-triangle ml-1"></i>
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Diupload:</span>
                        <span class="font-semibold text-gray-900">{{ $document->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @if($document->notes)
                    <div class="py-2">
                        <span class="text-gray-600 block mb-2">Catatan:</span>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg text-xs">{{ $document->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Warning if expired -->
            @if($document->is_expired)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-600 mt-1 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-red-900 mb-1">Dokumen Kadaluarsa</h4>
                        <p class="text-xs text-red-700">Dokumen ini sudah melewati masa berlaku. Silakan upload dokumen terbaru.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteDocument(id) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/workers/documents/${id}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
