@extends('layouts.admin')

@section('title', 'Statistik Dokumen Kadaluarsa')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Statistik Dokumen Kadaluarsa"
        description="Analisis kondisi dokumen pegawai berdasarkan urgensi"
        icon="fas fa-chart-line">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.document-expiry.index') }}" class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">
                    <i class="fas fa-list mr-2"></i>
                    Daftar Dokumen
                </a>
                <a href="{{ route('admin.document-expiry.export') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <i class="fas fa-download mr-2"></i>
                    Export CSV
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stats-card title="Expired" :value="$stats['expired']" icon="fas fa-circle-xmark" color="red" trend="Butuh perpanjangan" />
        <x-stats-card title="<= 30 Hari" :value="$stats['expiring_30_days']" icon="fas fa-triangle-exclamation" color="yellow" trend="Sangat mendesak" />
        <x-stats-card title="<= 60 Hari" :value="$stats['expiring_60_days']" icon="fas fa-clock" color="blue" trend="Perlu diproses" />
        <x-stats-card title="<= 90 Hari" :value="$stats['expiring_90_days']" icon="fas fa-calendar" color="purple" trend="Monitoring" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Distribusi Urgensi">
            <div class="h-72">
                <canvas id="urgencyChart"></canvas>
            </div>
        </x-card>

        <x-card title="Ringkasan Kategori">
            <div class="space-y-4">
                @php
                    $items = [
                        ['label' => 'Critical', 'count' => $documentsByUrgency['critical']->count(), 'class' => 'bg-red-500'],
                        ['label' => 'Urgent', 'count' => $documentsByUrgency['urgent']->count(), 'class' => 'bg-amber-500'],
                        ['label' => 'Warning', 'count' => $documentsByUrgency['warning']->count(), 'class' => 'bg-sky-500'],
                        ['label' => 'Watch', 'count' => $documentsByUrgency['watch']->count(), 'class' => 'bg-violet-500'],
                    ];
                    $total = collect($items)->sum('count');
                @endphp

                @foreach($items as $item)
                    @php
                        $percent = $total > 0 ? round(($item['count'] / $total) * 100) : 0;
                    @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-700">{{ $item['label'] }}</span>
                            <span class="font-semibold text-gray-900">{{ $item['count'] }} ({{ $percent }}%)</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100">
                            <div class="h-2 rounded-full {{ $item['class'] }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <x-card title="Pegawai Dengan Dokumen Kritis Kadaluarsa">
        @if($criticalWorkers->isEmpty())
            <x-empty-state
                icon="fas fa-shield-check"
                title="Tidak ada pegawai dengan dokumen kritis kadaluarsa"
                description="Kondisi compliance saat ini aman untuk dokumen kritis." />
        @else
            <div class="md:hidden space-y-3">
                @foreach($criticalWorkers as $item)
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-red-900">{{ $item['worker']->name }}</p>
                                <p class="text-xs text-red-700">{{ $item['worker']->employee_id ?? '-' }} • {{ $item['worker']->unit?->name ?? '-' }}</p>
                            </div>
                            <span class="rounded-full bg-red-200 px-2 py-1 text-xs font-semibold text-red-800">{{ $item['count'] }} dokumen</span>
                        </div>

                        <div class="mt-3 space-y-2 text-sm text-red-800">
                            @foreach($item['documents'] as $doc)
                                <div class="rounded-md border border-red-200 bg-white px-3 py-2">
                                    <p class="font-medium">{{ $doc->documentType?->name ?? $doc->departmentDocumentType?->customDocumentType?->name ?? '-' }}</p>
                                    <p class="text-xs">Kadaluarsa: {{ $doc->expired_date?->format('d/m/Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Pegawai</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Unit / Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Dokumen Kritis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($criticalWorkers as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $item['worker']->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item['worker']->employee_id ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <p>{{ $item['worker']->unit?->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $item['worker']->position?->name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <div class="space-y-1">
                                        @foreach($item['documents'] as $doc)
                                            <div class="text-xs">
                                                <span class="font-medium">{{ $doc->documentType?->name ?? $doc->departmentDocumentType?->customDocumentType?->name ?? '-' }}</span>
                                                <span class="text-red-600">({{ $doc->expired_date?->format('d/m/Y') }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">{{ $item['count'] }} dokumen</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.workers.show', $item['worker']->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">Lihat Profil</a>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('urgencyChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'Urgent', 'Warning', 'Watch'],
            datasets: [{
                data: [
                    {{ $documentsByUrgency['critical']->count() }},
                    {{ $documentsByUrgency['urgent']->count() }},
                    {{ $documentsByUrgency['warning']->count() }},
                    {{ $documentsByUrgency['watch']->count() }}
                ],
                backgroundColor: ['#ef4444', '#f59e0b', '#0ea5e9', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
