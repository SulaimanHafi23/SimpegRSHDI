@extends('layouts.admin')

@section('title', 'Dokumen Pegawai - ' . $worker->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.worker-documents.index') }}" class="hover:text-indigo-600">Dokumen Pegawai</a>
            <span>/</span>
            <span class="text-gray-800">{{ $worker->name }}</span>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Dokumen Pegawai</h1>
                <p class="text-gray-600 mt-1">Detail dan kelengkapan dokumen {{ $worker->name }}</p>
            </div>
            <a href="{{ route('admin.worker-documents.create', ['worker_id' => $worker->id]) }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Dokumen
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Worker Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-6">
            <div class="flex-shrink-0">
                @if(($worker->photo_url ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo_url))
                    <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-200"
                         src="{{ \Illuminate\Support\Facades\Storage::url($worker->photo_url) }}"
                         alt="{{ $worker->name }}">
                @elseif(($worker->photo ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo))
                    <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-200"
                         src="{{ \Illuminate\Support\Facades\Storage::url($worker->photo) }}"
                         alt="{{ $worker->name }}">
                @else
                    <div class="h-24 w-24 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-3xl border-4 border-gray-200">
                        {{ strtoupper(substr($worker->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">{{ $worker->name }}</h2>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-id-card mr-2 text-indigo-600"></i>
                        <span><strong>NIP:</strong> {{ $worker->nip ?? '-' }}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-building mr-2 text-indigo-600"></i>
                        <span><strong>Departemen:</strong> {{ $worker->department->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-briefcase mr-2 text-indigo-600"></i>
                        <span><strong>Departemen:</strong> {{ $worker->department->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
            <p class="text-xs font-medium text-blue-700 mb-1">Dokumen Wajib</p>
            <p class="text-2xl font-bold text-blue-900">{{ $totalRequired }}</p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
            <p class="text-xs font-medium text-purple-700 mb-1">Total Upload</p>
            <p class="text-2xl font-bold text-purple-900">{{ $uploadedCount }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
            <p class="text-xs font-medium text-green-700 mb-1">Terverifikasi</p>
            <p class="text-2xl font-bold text-green-900">{{ $verifiedCount }}</p>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
            <p class="text-xs font-medium text-yellow-700 mb-1">Menunggu</p>
            <p class="text-2xl font-bold text-yellow-900">{{ $pendingCount }}</p>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
            <p class="text-xs font-medium text-red-700 mb-1">Ditolak</p>
            <p class="text-2xl font-bold text-red-900">{{ $rejectedCount }}</p>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
            <p class="text-xs font-medium text-orange-700 mb-1">Kadaluarsa</p>
            <p class="text-2xl font-bold text-orange-900">{{ $expiredCount }}</p>
        </div>
    </div>

    <!-- Completion Progress -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-800">Kelengkapan Dokumen</h3>
            <span class="text-2xl font-bold {{ $completionPercentage >= 100 ? 'text-green-600' : ($completionPercentage >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $completionPercentage }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-gradient-to-r {{ $completionPercentage >= 100 ? 'from-green-500 to-green-600' : ($completionPercentage >= 50 ? 'from-yellow-500 to-yellow-600' : 'from-red-500 to-red-600') }} h-4 rounded-full transition-all duration-500"
                 style="width: {{ min($completionPercentage, 100) }}%"></div>
        </div>
        <p class="text-sm text-gray-600 mt-2">
            {{ $verifiedCount }} dari {{ $totalRequired }} dokumen wajib telah terverifikasi
        </p>
    </div>

    <!-- Document Checklist -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-clipboard-check text-indigo-600 mr-2"></i>
                Daftar Dokumen
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($documentChecklist as $item)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="flex-shrink-0 mr-4">
                                @if($item['status'] === 'verified')
                                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-check text-green-600 text-lg"></i>
                                    </div>
                                @elseif($item['status'] === 'pending')
                                    <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <i class="fas fa-clock text-yellow-600 text-lg"></i>
                                    </div>
                                @elseif($item['status'] === 'rejected')
                                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-times text-red-600 text-lg"></i>
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-minus text-gray-400 text-lg"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900">{{ $item['document_type']->name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($item['is_uploaded'])
                                        @if($item['is_expired'])
                                            <span class="text-red-600">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Dokumen kadaluarsa
                                            </span>
                                        @elseif($item['status'] === 'verified')
                                            <span class="text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Terverifikasi pada {{ $item['latest_document']->updated_at->format('d M Y') }}
                                            </span>
                                        @elseif($item['status'] === 'pending')
                                            <span class="text-yellow-600">
                                                <i class="fas fa-clock mr-1"></i>
                                                Menunggu verifikasi
                                            </span>
                                        @elseif($item['status'] === 'rejected')
                                            <span class="text-red-600">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Ditolak
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">
                                            <i class="fas fa-upload mr-1"></i>
                                            Belum diupload
                                        </span>
                                    @endif
                                </p>
                                @if($item['is_uploaded'] && $item['versions']->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($item['versions'] as $versionItem)
                                            <a href="{{ route('admin.worker-documents.show', $versionItem['document']->id) }}"
                                               class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-semibold border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition"
                                               title="Lihat detail dokumen versi {{ $versionItem['version'] }}">
                                                Versi {{ $versionItem['version'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($item['is_uploaded'])
                                <a href="{{ route('admin.worker-documents.show', $item['latest_document']->id) }}"
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </a>
                            @endif
                            <a href="{{ route('admin.worker-documents.create', ['worker_id' => $worker->id, 'document_type_id' => $item['document_type']->id]) }}"
                               class="inline-flex items-center px-3 py-1 {{ $item['is_uploaded'] ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-xs font-semibold rounded-lg transition"
                               title="{{ $item['is_uploaded'] ? 'Upload Ulang' : 'Upload Dokumen' }}">
                                <i class="fas fa-upload mr-1"></i>
                                {{ $item['is_uploaded'] ? 'Upload Ulang' : 'Upload' }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                    <p>Tidak ada dokumen wajib untuk departemen ini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
