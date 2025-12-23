@extends('layouts.admin')

@section('title', 'Manajemen Dokumen Pegawai')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Manajemen Dokumen Pegawai" 
        description="Kelola dokumen yang diupload pegawai"
        icon="fas fa-file-alt">
        <x-slot:actions>
            <x-button 
                variant="success" 
                icon="fas fa-plus"
                onclick="window.location.href='{{ route('admin.worker-documents.create') }}'">
                Unggah Dokumen
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stats-card 
            title="Total Dokumen" 
            :value="$documents->total() ?? 0" 
            icon="fas fa-file-alt" 
            color="blue" />
        
        <x-stats-card 
            title="Menunggu Verifikasi" 
            :value="$documents->where('status', 'pending')->count() ?? 0" 
            icon="fas fa-clock" 
            color="yellow" />
        
        <x-stats-card 
            title="Terverifikasi" 
            :value="$documents->where('status', 'verified')->count() ?? 0" 
            icon="fas fa-check-circle" 
            color="green" />
        
        <x-stats-card 
            title="Ditolak" 
            :value="$documents->where('status', 'rejected')->count() ?? 0" 
            icon="fas fa-times-circle" 
            color="red" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.worker-documents.index') }}">
        <x-form.select 
            name="worker_id" 
            label="Pegawai"
            :selected="request('worker_id') ?? ''"
            placeholder="Semua Pegawai">
            @foreach($workers as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select 
            name="document_type_id" 
            label="Tipe Dokumen"
            :selected="request('document_type_id') ?? ''"
            placeholder="Semua Tipe">
            @foreach($documentTypes as $dt)
                <option value="{{ $dt->id }}">{{ $dt->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select 
            name="status" 
            label="Status"
            :options="[
                'pending' => 'Menunggu',
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak'
            ]"
            :selected="request('status') ?? ''"
            placeholder="Semua Status" />
    </x-filter-section>

    {{-- Documents Table --}}
    <x-card>
        @if($documents->isEmpty())
            <x-empty-state 
                icon="fas fa-file-alt"
                title="Tidak ada dokumen"
                description="Dokumen yang diupload pegawai akan ditampilkan di sini"
                actionText="Unggah Dokumen"
                :actionUrl="route('admin.worker-documents.create')" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Tipe Dokumen</x-table.cell>
                        <x-table.cell header>File</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Tanggal Upload</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($documents as $index => $doc)
                    <x-table.row>
                        <x-table.cell>{{ $documents->firstItem() + $index }}</x-table.cell>
                        
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $doc->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $doc->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="font-medium">{{ $doc->documentType->name ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            <a href="{{ route('admin.worker-documents.download', $doc->id) }}" 
                               class="text-blue-600 hover:text-blue-900 flex items-center">
                                <i class="fas fa-download mr-2"></i>
                                {{ Str::limit($doc->file_name, 30) }}
                            </a>
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                    'verified' => ['variant' => 'success', 'label' => 'Terverifikasi'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                ];
                                $badge = $statusBadges[$doc->status] ?? ['variant' => 'secondary', 'label' => $doc->status];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="text-sm">{{ $doc->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $doc->created_at->format('H:i') }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                {{-- View button --}}
                                <a href="{{ route('admin.worker-documents.show', $doc->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" 
                                   title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if($doc->status === 'pending')
                                    {{-- Verify button --}}
                                    <button onclick="verifyDocument('{{ $doc->id }}')" 
                                            class="text-green-600 hover:text-green-900" 
                                            title="Verifikasi">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    
                                    {{-- Reject button --}}
                                    <button onclick="rejectDocument('{{ $doc->id }}')" 
                                            class="text-red-600 hover:text-red-900" 
                                            title="Tolak">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Download button --}}
                                <a href="{{ route('admin.worker-documents.download', $doc->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900" 
                                   title="Download">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            @if($documents->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$documents" />
                </div>
            @endif
        @endif
    </x-card>
</div>

{{-- Hidden forms for verify/reject actions --}}
<form id="verifyForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="rejectForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="reason" id="rejectReason">
</form>

<script>
function verifyDocument(documentId) {
    if (confirm('Apakah Anda yakin ingin memverifikasi dokumen ini?')) {
        const form = document.getElementById('verifyForm');
        form.action = `/admin/worker-documents/${documentId}/verify`;
        form.submit();
    }
}

function rejectDocument(documentId) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason !== null && reason.trim() !== '') {
        const form = document.getElementById('rejectForm');
        document.getElementById('rejectReason').value = reason;
        form.action = `/admin/worker-documents/${documentId}/reject`;
        form.submit();
    }
}
</script>
@endsection
