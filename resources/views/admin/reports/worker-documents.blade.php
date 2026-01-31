@extends('layouts.admin')

@section('title', 'Persetujuan Dokumen Pegawai')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                <i class="fas fa-file-certificate text-orange-600 mr-2"></i>
                 Dokumen Pegawai
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Verifikasi dan kelola dokumen karyawan</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="flex gap-3">
            @php
                $pendingCount = $documents->where('status', 'pending')->count();
                $verifiedCount = $documents->where('status', 'verified')->count();
                $rejectedCount = $documents->where('status', 'rejected')->count();
            @endphp
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2">
                <div class="text-yellow-800 text-xs font-medium">Pending</div>
                <div class="text-yellow-900 text-xl font-bold">{{ $pendingCount }}</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                <div class="text-green-800 text-xs font-medium">Terverifikasi</div>
                <div class="text-green-900 text-xl font-bold">{{ $verifiedCount }}</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                <div class="text-red-800 text-xs font-medium">Ditolak</div>
                <div class="text-red-900 text-xl font-bold">{{ $rejectedCount }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}" 
                           class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ $filters['date_to'] ?? now()->endOfMonth()->format('Y-m-d') }}" 
                           class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ (isset($filters['status']) && $filters['status']=='pending') ? 'selected' : '' }}>⏳ Pending</option>
                        <option value="verified" {{ (isset($filters['status']) && $filters['status']=='verified') ? 'selected' : '' }}>✓ Terverifikasi</option>
                        <option value="rejected" {{ (isset($filters['status']) && $filters['status']=='rejected') ? 'selected' : '' }}>✗ Ditolak</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-sm font-medium shadow-sm">
                        <i class="fas fa-filter mr-1"></i><span class="hidden sm:inline">Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-orange-50 to-yellow-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Pegawai</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden md:table-cell">Jenis Dokumen</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden lg:table-cell">File</th>
                        <th class="px-3 sm:px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden lg:table-cell">Tanggal</th>
                        <th class="px-3 sm:px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-orange-50 transition-colors duration-150">
                        <td class="px-3 sm:px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 mr-3">
                                    @if($doc->worker && $doc->worker->photo_url && Storage::disk('public')->exists($doc->worker->photo_url))
                                        <img src="{{ Storage::url($doc->worker->photo_url) }}" 
                                             alt="{{ $doc->worker->name }}" 
                                             class="h-10 w-10 rounded-full object-cover border-2 border-orange-200">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-400 to-yellow-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr(optional($doc->worker)->name ?? 'N', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-semibold text-gray-900">{{ optional($doc->worker)->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-id-badge mr-1"></i>{{ optional($doc->worker)->nip ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-building mr-1"></i>{{ optional($doc->worker->department)->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-orange-600 md:hidden mt-1">
                                        <i class="fas fa-file-alt mr-1"></i>{{ optional($doc->documentType)->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm hidden md:table-cell">
                            <div class="font-medium text-gray-900">{{ optional($doc->documentType)->name ?? 'N/A' }}</div>
                            @if($doc->documentType && $doc->documentType->description)
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($doc->documentType->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm hidden lg:table-cell">
                            <div class="flex items-center">
                                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                <div>
                                    <div class="font-medium text-gray-700">{{ Str::limit($doc->file_name, 25) }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($doc->file_size / 1024, 2) }} KB</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-center">
                            @if($doc->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-300">
                                    <i class="fas fa-clock mr-1"></i>
                                    Pending
                                </span>
                            @elseif($doc->status === 'verified')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-300">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Terverifikasi
                                </span>
                            @elseif($doc->status === 'rejected')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-300">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Ditolak
                                </span>
                            @endif
                            
                            @if($doc->expired_date && \Carbon\Carbon::parse($doc->expired_date)->isPast())
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Expired
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm hidden lg:table-cell">
                            <div class="text-gray-700">
                                <i class="fas fa-calendar-upload text-blue-500 mr-1"></i>
                                {{ $doc->created_at->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $doc->created_at->format('H:i') }}
                            </div>
                            @if($doc->expired_date)
                                <div class="text-xs {{ \Carbon\Carbon::parse($doc->expired_date)->isPast() ? 'text-red-600' : 'text-gray-500' }} mt-1">
                                    <i class="fas fa-calendar-times mr-1"></i>
                                    Exp: {{ \Carbon\Carbon::parse($doc->expired_date)->format('d M Y') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4">
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                                <!-- View/Download Button -->
                                @if($doc->file_path)
                                    <a href="{{ route('admin.worker-documents.show', $doc->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs font-medium shadow-sm"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye mr-1"></i>
                                        <span class="hidden xl:inline">Detail</span>
                                    </a>
                                @endif
                                
                                <!-- Approve Button -->
                                @if($doc->status === 'pending')
                                    <button onclick="approveDocument('{{ $doc->id }}')" 
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium shadow-sm"
                                            title="Setujui Dokumen">
                                        <i class="fas fa-check mr-1"></i>
                                        <span class="hidden xl:inline">Approve</span>
                                    </button>
                                    
                                    <!-- Reject Button -->
                                    <button onclick="rejectDocument('{{ $doc->id }}')" 
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-xs font-medium shadow-sm"
                                            title="Tolak Dokumen">
                                        <i class="fas fa-times mr-1"></i>
                                        <span class="hidden xl:inline">Reject</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada dokumen</h3>
                                <p class="text-gray-500">Tidak ada dokumen yang sesuai dengan filter.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($documents, 'links') && $documents->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Hidden Forms for Approve/Reject -->
<form id="approveForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="rejectForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="notes" id="rejectNotes">
</form>

<script>
function approveDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin menyetujui dokumen ini?')) {
        const form = document.getElementById('approveForm');
        form.action = `/admin/worker-documents/${documentId}/verify`;
        form.submit();
    }
}

function rejectDocument(documentId) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason !== null && reason.trim() !== '') {
        const form = document.getElementById('rejectForm');
        document.getElementById('rejectNotes').value = reason;
        form.action = `/admin/worker-documents/${documentId}/reject`;
        form.submit();
    } else if (reason !== null) {
        alert('Alasan penolakan harus diisi!');
    }
}
</script>
@endsection
