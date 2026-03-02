@extends('layouts.admin')

@section('title', 'Manajemen Dokumen Pegawai')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Manajemen Dokumen Pegawai</h1>
            <p class="text-gray-600 mt-1">Kelola dan pantau kelengkapan dokumen pegawai</p>
        </div>
        <a href="{{ route('admin.worker-documents.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Dokumen
        </a>
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

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        @php
            $completedWorkers = $workersWithDocStats->filter(function($w) {
                return $w->completionPercentage >= 100;
            })->count();
            $incompleteWorkers = $workersWithDocStats->filter(function($w) {
                return $w->completionPercentage < 100 && $w->completionPercentage > 0;
            })->count();
            $noDocsWorkers = $workersWithDocStats->filter(function($w) {
                return $w->uploadedCount == 0;
            })->count();
        @endphp

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 border border-blue-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-700">Total Pegawai</p>
                    <p class="text-3xl font-bold text-blue-900 mt-1">{{ $workersWithDocStats->total() }}</p>
                </div>
                <div class="p-3 bg-blue-600 rounded-full">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 border border-green-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-700">Dokumen Lengkap</p>
                    <p class="text-3xl font-bold text-green-900 mt-1">{{ $completedWorkers }}</p>
                </div>
                <div class="p-3 bg-green-600 rounded-full">
                    <i class="fas fa-check-circle text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-5 border border-yellow-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-700">Belum Lengkap</p>
                    <p class="text-3xl font-bold text-yellow-900 mt-1">{{ $incompleteWorkers }}</p>
                </div>
                <div class="p-3 bg-yellow-600 rounded-full">
                    <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-5 border border-red-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-700">Belum Upload</p>
                    <p class="text-3xl font-bold text-red-900 mt-1">{{ $noDocsWorkers }}</p>
                </div>
                <div class="p-3 bg-red-600 rounded-full">
                    <i class="fas fa-times-circle text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div x-data="{ showFilters: false }" class="mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <button @click="showFilters = !showFilters" 
                    class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-filter text-indigo-600"></i>
                    <span class="font-semibold text-gray-900">Filter & Pencarian</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform" 
                   :class="{ 'rotate-180': showFilters }"></i>
            </button>

            <div x-show="showFilters" 
                 x-collapse 
                 class="border-t border-gray-200">
                <form method="GET" action="{{ route('admin.worker-documents.index') }}" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                            <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Pegawai</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ ($filters['worker_id'] ?? '') == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Dokumen</label>
                            <select name="document_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Tipe</option>
                                @foreach($documentTypes as $type)
                                    <option value="{{ $type->id }}" {{ ($filters['document_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="verified" {{ ($filters['status'] ?? '') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                                <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md transition duration-150 flex items-center">
                                <i class="fas fa-search mr-2"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.worker-documents.index') }}" 
                               class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg shadow-md transition duration-150 flex items-center">
                                <i class="fas fa-redo mr-2"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($workersWithDocStats as $worker)
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $worker->name }}</p>
                        <p class="text-xs text-gray-500">{{ $worker->nip ?? '-' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $worker->department->name ?? '-' }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $worker->completionPercentage >= 100 ? 'bg-green-100 text-green-700' : ($worker->completionPercentage > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ $worker->completionPercentage }}%
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                    <div class="bg-gray-50 rounded p-2 text-center">
                        <p class="text-gray-500">Wajib</p>
                        <p class="font-bold text-gray-800">{{ $worker->totalRequired }}</p>
                    </div>
                    <div class="bg-blue-50 rounded p-2 text-center">
                        <p class="text-blue-600">Upload</p>
                        <p class="font-bold text-blue-700">{{ $worker->uploadedCount }}</p>
                    </div>
                    <div class="bg-green-50 rounded p-2 text-center">
                        <p class="text-green-600">Verified</p>
                        <p class="font-bold text-green-700">{{ $worker->verifiedCount }}</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                    <a href="{{ route('admin.worker-documents.worker-documents', $worker->id) }}"
                       class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition duration-150">
                        Detail
                    </a>
                    <a href="{{ route('admin.worker-documents.create', ['worker_id' => $worker->id]) }}"
                       class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm transition duration-150">
                        Upload
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow px-6 py-10 text-center text-gray-500">
                <p class="text-sm">Tidak ada data pegawai</p>
            </div>
        @endforelse
    </div>

    {{-- Workers Table --}}
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pegawai
                        </th>
                        <th scope="col" class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Departemen
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dokumen Wajib
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dokumen Terupload
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Terverifikasi
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kelengkapan
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($workersWithDocStats as $worker)
                    @php
                        $statusColor = 'gray';
                        if ($worker->completionPercentage >= 100) {
                            $statusColor = 'green';
                        } elseif ($worker->completionPercentage >= 50) {
                            $statusColor = 'yellow';
                        } elseif ($worker->uploadedCount > 0) {
                            $statusColor = 'orange';
                        } else {
                            $statusColor = 'red';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $worker->uploadedCount == 0 ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if(($worker->photo_url ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo_url))
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ \Illuminate\Support\Facades\Storage::url($worker->photo_url) }}" 
                                             alt="{{ $worker->name }}">
                                    @elseif(($worker->photo ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo))
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ \Illuminate\Support\Facades\Storage::url($worker->photo) }}" 
                                             alt="{{ $worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($worker->name ?? ($worker->nip ?? '-'), 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $worker->nip ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $worker->department->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-lg font-bold text-gray-900">{{ $worker->totalRequired }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-lg font-bold {{ $worker->uploadedCount > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                {{ $worker->uploadedCount }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold {{ $worker->verifiedCount > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $worker->verifiedCount }}
                                </span>
                                @if($worker->expiredCount > 0)
                                    <span class="text-xs text-red-600 flex items-center mt-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $worker->expiredCount }} kadaluarsa
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 max-w-[100px] mb-1">
                                    <div class="bg-{{ $statusColor }}-600 h-2.5 rounded-full transition-all duration-300" 
                                         style="width: {{ min($worker->completionPercentage, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-{{ $statusColor }}-700">
                                    {{ $worker->completionPercentage }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.worker-documents.worker-documents', $worker->id) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition duration-150"
                                   title="Lihat Detail Dokumen">
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </a>
                                <a href="{{ route('admin.worker-documents.create', ['worker_id' => $worker->id]) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm transition duration-150"
                                   title="Upload Dokumen">
                                    <i class="fas fa-upload mr-1"></i>
                                    Upload
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center py-8">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-700 mb-1">Tidak ada data pegawai</p>
                                <p class="text-sm text-gray-500">Silakan tambahkan pegawai terlebih dahulu</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $workersWithDocStats->links() }}
        </div>
    </div>

    {{-- Legend Info --}}
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Baris dengan <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-semibold">latar merah</span> menunjukkan pegawai yang <strong>belum upload dokumen sama sekali</strong></li>
                        <li><strong>Dokumen Wajib:</strong> Jumlah dokumen yang harus dilengkapi oleh pegawai</li>
                        <li><strong>Dokumen Terupload:</strong> Total dokumen yang sudah diupload (termasuk pending dan rejected)</li>
                        <li><strong>Terverifikasi:</strong> Dokumen yang sudah diverifikasi dan disetujui</li>
                        <li><strong>Kelengkapan:</strong> Persentase kelengkapan dokumen berdasarkan dokumen terverifikasi</li>
                        <li>Klik tombol <span class="px-2 py-0.5 bg-blue-600 text-white rounded font-semibold">Detail</span> untuk melihat dokumen lengkap pegawai</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
