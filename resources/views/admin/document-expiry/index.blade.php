@extends('layouts.admin')

@section('title', 'Monitoring Dokumen Kadaluarsa')

@section('content')
@php
    $totalMonitored = $documentsByUrgency['critical']->count() + $documentsByUrgency['urgent']->count() + $documentsByUrgency['warning']->count() + $documentsByUrgency['watch']->count();
@endphp

<div class="space-y-6">
    <x-page-header
        title="Monitoring Dokumen Kadaluarsa"
        description="Pantau dokumen pegawai yang sudah atau akan kadaluarsa"
        icon="fas fa-file-contract">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.document-expiry.statistics') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Statistik
                </a>
                <a href="{{ route('admin.document-expiry.export', ['filter' => $filter]) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <i class="fas fa-download mr-2"></i>
                    Export CSV
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stats-card title="Kadaluarsa" :value="$stats['expired']" icon="fas fa-circle-xmark" color="red" trend="Perlu tindakan" />
        <x-stats-card title="Urgent <= 30 Hari" :value="$stats['expiring_30_days']" icon="fas fa-triangle-exclamation" color="yellow" trend="Prioritas tinggi" />
        <x-stats-card title="Warning <= 60 Hari" :value="$stats['expiring_60_days']" icon="fas fa-clock" color="blue" trend="Segera diproses" />
        <x-stats-card title="Watch <= 90 Hari" :value="$stats['expiring_90_days']" icon="fas fa-calendar-day" color="purple" trend="Monitoring" />
    </div>

    <x-card>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.document-expiry.index', ['filter' => 'all']) }}" class="rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium {{ $filter === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Semua ({{ $totalMonitored }})
            </a>
            <a href="{{ route('admin.document-expiry.index', ['filter' => 'critical']) }}" class="rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium {{ $filter === 'critical' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                Critical ({{ $documentsByUrgency['critical']->count() }})
            </a>
            <a href="{{ route('admin.document-expiry.index', ['filter' => 'urgent']) }}" class="rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium {{ $filter === 'urgent' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                Urgent ({{ $documentsByUrgency['urgent']->count() }})
            </a>
            <a href="{{ route('admin.document-expiry.index', ['filter' => 'warning']) }}" class="rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium {{ $filter === 'warning' ? 'bg-sky-600 text-white' : 'bg-sky-50 text-sky-700 hover:bg-sky-100' }}">
                Warning ({{ $documentsByUrgency['warning']->count() }})
            </a>
            <a href="{{ route('admin.document-expiry.index', ['filter' => 'watch']) }}" class="rounded-full px-3 py-1.5 text-xs sm:text-sm font-medium {{ $filter === 'watch' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700 hover:bg-violet-100' }}">
                Watch ({{ $documentsByUrgency['watch']->count() }})
            </a>
        </div>
    </x-card>

    <x-card>
        @if($documents->isEmpty())
            <x-empty-state
                icon="fas fa-circle-check"
                title="Tidak ada dokumen pada filter ini"
                description="Semua dokumen untuk kategori ini masih aman." />
        @else
            <div class="md:hidden space-y-3">
                @foreach($documents as $document)
                    @php
                        $daysUntilExpiry = now()->startOfDay()->diffInDays($document->expired_date, false);
                        $documentName = $document->documentType?->name ?? $document->departmentDocumentType?->customDocumentType?->name ?? '-';
                        $statusClass = $daysUntilExpiry < 0
                            ? 'bg-red-100 text-red-700'
                            : ($daysUntilExpiry <= 30 ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700');
                    @endphp
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $document->worker->name }}</p>
                                <p class="text-xs text-gray-500">{{ $document->worker->employee_id ?? '-' }} • {{ $document->worker->unit?->name ?? '-' }}</p>
                            </div>
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">
                                @if($daysUntilExpiry < 0)
                                    Expired {{ abs($daysUntilExpiry) }} hari
                                @elseif($daysUntilExpiry === 0)
                                    Hari ini
                                @else
                                    {{ $daysUntilExpiry }} hari lagi
                                @endif
                            </span>
                        </div>
                        <div class="mt-3 text-sm text-gray-700">
                            <p><span class="font-medium">Dokumen:</span> {{ $documentName }}</p>
                            <p><span class="font-medium">Kadaluarsa:</span> {{ $document->expired_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="mt-3 flex items-center gap-3 text-sm">
                            <a href="{{ route('admin.worker-documents.show', $document->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">Detail Dokumen</a>
                            <a href="{{ route('admin.workers.show', $document->worker_id) }}" class="text-gray-600 hover:text-gray-800 font-medium">Profil Pegawai</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Pegawai</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Dokumen</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Unit / Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Kadaluarsa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Sisa Hari</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($documents as $document)
                            @php
                                $daysUntilExpiry = now()->startOfDay()->diffInDays($document->expired_date, false);
                                $documentName = $document->documentType?->name ?? $document->departmentDocumentType?->customDocumentType?->name ?? '-';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $document->worker->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $document->worker->employee_id ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $documentName }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <p>{{ $document->worker->unit?->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $document->worker->position?->name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $document->expired_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    @if($daysUntilExpiry < 0)
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Expired {{ abs($daysUntilExpiry) }} hari</span>
                                    @elseif($daysUntilExpiry === 0)
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Hari ini</span>
                                    @elseif($daysUntilExpiry <= 30)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">{{ $daysUntilExpiry }} hari</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700">{{ $daysUntilExpiry }} hari</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.worker-documents.show', $document->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
@endsection

