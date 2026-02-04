@extends('layouts.admin')

@section('title', 'Laporan Dokumen Pegawai')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Laporan Dokumen Pegawai" 
        description="Lihat ringkasan dan ekspor data dokumen pegawai"
        icon="fas fa-folder-open">
        <x-slot:actions>
            <a href="{{ route('reports.worker-documents', array_merge($filters ?? [], ['export' => 'csv'])) }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                <i class="fas fa-file-csv mr-2"></i>
                Export CSV
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    @php
        $pendingCount = $documents->where('status', 'pending')->count();
        $verifiedCount = $documents->where('status', 'verified')->count();
        $rejectedCount = $documents->where('status', 'rejected')->count();
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stats-card 
            title="Total Dokumen" 
            :value="$documents->total() ?? 0" 
            icon="fas fa-folder" 
            color="blue" />
        
        <x-stats-card 
            title="Pending" 
            :value="$pendingCount" 
            icon="fas fa-clock" 
            color="yellow" />
        
        <x-stats-card 
            title="Terverifikasi" 
            :value="$verifiedCount" 
            icon="fas fa-check-circle" 
            color="green" />
        
        <x-stats-card 
            title="Ditolak" 
            :value="$rejectedCount" 
            icon="fas fa-times-circle" 
            color="red" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('reports.worker-documents') }}">
        <x-form.select 
            name="worker_id" 
            label="Pegawai"
            :selected="$filters['worker_id'] ?? ''"
            placeholder="Semua Pegawai">
            @if(isset($workers))
                @foreach($workers as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            @endif
        </x-form.select>

        <x-form.select 
            name="document_type_id" 
            label="Jenis Dokumen"
            :selected="$filters['document_type_id'] ?? ''"
            placeholder="Semua Jenis">
            @if(isset($documentTypes))
                @foreach($documentTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            @endif
        </x-form.select>

        <x-form.select 
            name="status" 
            label="Status"
            :options="[
                'pending' => 'Pending',
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak'
            ]"
            :selected="$filters['status'] ?? ''"
            placeholder="Semua Status" />

        <x-form.select 
            name="month" 
            label="Bulan"
            :selected="$filters['month'] ?? ''"
            placeholder="Semua Bulan">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
            @endfor
        </x-form.select>

        <x-form.select 
            name="year" 
            label="Tahun"
            :selected="$filters['year'] ?? ''"
            placeholder="Semua Tahun">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </x-form.select>
    </x-filter-section>

    {{-- Table --}}
    <x-card>
        @if($documents->isEmpty())
            <x-empty-state 
                icon="fas fa-folder-open"
                title="Tidak ada data dokumen"
                description="Data dokumen pegawai akan ditampilkan di sini" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Jenis Dokumen</x-table.cell>
                        <x-table.cell header>File</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
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

                        <x-table.cell>{{ $doc->documentType->name ?? '-' }}</x-table.cell>

                        <x-table.cell>
                            @if($doc->file_path)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-file-download mr-1"></i>
                                    Lihat
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Pending'],
                                    'verified' => ['variant' => 'success', 'label' => 'Terverifikasi'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                ];
                                $badge = $statusBadges[$doc->status] ?? ['variant' => 'secondary', 'label' => ucfirst($doc->status)];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>{{ $doc->created_at->format('d M Y') }}</x-table.cell>

                        <x-table.cell>
                            <a href="{{ route('approvals.documents.show', $doc->id) }}" 
                               class="text-blue-600 hover:text-blue-900" 
                               title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            @if($documents->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$documents" />
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
