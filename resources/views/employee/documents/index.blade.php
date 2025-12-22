@extends('layouts.employee')

@section('title', 'Dokumen Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    <i class="fas fa-file-alt text-indigo-600 mr-2"></i>
                    Dokumen Saya
                </h1>
                <p class="text-gray-600 mt-2">Kelola dokumen pribadi Anda</p>
            </div>
            <a href="{{ route('employee.documents.create') }}" 
               class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-150">
                <i class="fas fa-cloud-upload-alt mr-2"></i>
                <span class="hidden sm:inline">Upload Dokumen</span>
                <span class="sm:hidden">Upload</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <i class="fas fa-file-alt text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['rejected'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-filter mr-2 text-indigo-600"></i>
                Pencarian & Filter Dokumen
            </h3>
            <button @click="showFilters = !showFilters" class="text-gray-600 hover:text-gray-800">
                <i class="fas" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('employee.documents.index') }}" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Search -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>
                        Cari
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari nomor dokumen, jenis dokumen, catatan, status..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Document Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-alt mr-1"></i>
                        Jenis Dokumen
                    </label>
                    <select name="document_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Jenis</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}" {{ ($filters['document_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list-check mr-1"></i>
                        Status Verifikasi
                    </label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>
                            🕐 Pending
                        </option>
                        <option value="approved" {{ ($filters['status'] ?? '') == 'approved' ? 'selected' : '' }}>
                            ✅ Disetujui
                        </option>
                        <option value="rejected" {{ ($filters['status'] ?? '') == 'rejected' ? 'selected' : '' }}>
                            ❌ Ditolak
                        </option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list-ol mr-1"></i>
                        Tampilkan
                    </label>
                    <select name="per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10" {{ ($filters['per_page'] ?? 15) == 10 ? 'selected' : '' }}>10 per halaman</option>
                        <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15 per halaman</option>
                        <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25 per halaman</option>
                        <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50 per halaman</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button type="submit" 
                        class="flex-1 px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>
                    Terapkan Filter
                </button>
                <a href="{{ route('employee.documents.index') }}" 
                   class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                    <i class="fas fa-redo mr-2"></i>
                    Reset
                </a>
            </div>

            <!-- Active Filters Display -->
            @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['document_type_id']))
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-sm text-gray-600 font-medium">Filter Aktif:</span>
                @if(!empty($filters['search']))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-search mr-1"></i>
                        "{{ $filters['search'] }}"
                        <a href="{{ route('employee.documents.index', array_diff_key(request()->all(), ['search' => ''])) }}" 
                           class="ml-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(!empty($filters['status']))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                        {{ $filters['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $filters['status'] == 'approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $filters['status'] == 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        Status: {{ ucfirst($filters['status']) }}
                        <a href="{{ route('employee.documents.index', array_diff_key(request()->all(), ['status' => ''])) }}" 
                           class="ml-2 hover:opacity-75">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(!empty($filters['document_type_id']))
                    @php
                        $selectedType = $documentTypes->firstWhere('id', $filters['document_type_id']);
                    @endphp
                    @if($selectedType)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                        Jenis: {{ $selectedType->name }}
                        <a href="{{ route('employee.documents.index', array_diff_key(request()->all(), ['document_type_id' => ''])) }}" 
                           class="ml-2 text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    @endif
                @endif
            </div>
            @endif
        </form>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-tag mr-1"></i>Jenis Dokumen
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-file mr-1"></i>Nama File
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="far fa-calendar-times mr-1"></i>Kadaluarsa
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-info-circle mr-1"></i>Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-cog mr-1"></i>Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $document->documentType->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $document->file_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $document->expired_date ? \Carbon\Carbon::parse($document->expired_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($document->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($document->status === 'approved')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="{{ route('employee.documents.show', $document->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('employee.documents.download', $document->id) }}" 
                                       class="text-green-600 hover:text-green-900" title="Download">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                    @if($document->status === 'pending')
                                        <form action="{{ route('employee.documents.destroy', $document->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Hapus"
                                                    onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                    <p class="text-lg font-medium text-gray-500 mb-2">Belum ada dokumen</p>
                                    <p class="text-sm text-gray-400">Upload dokumen untuk melihat data di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
