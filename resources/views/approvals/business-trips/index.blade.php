@extends('layouts.admin')

@section('title', 'Approval Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Approval Perjalanan Dinas"
        description="Kelola pengajuan perjalanan dinas pegawai"
        icon="fas fa-plane">
        <x-slot:actions>
            {{-- Export Buttons --}}
            <x-export-buttons :route="route('approvals.business-trips.export')" title="Export Perjalanan Dinas">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </x-export-buttons>
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-stats-card
            title="Total Pengajuan"
            :value="$statistics['total'] ?? 0"
            icon="fas fa-file-alt"
            color="blue" />

        <x-stats-card
            title="Menunggu"
            :value="$statistics['pending'] ?? 0"
            icon="fas fa-clock"
            color="yellow" />

        <x-stats-card
            title="Disetujui"
            :value="$statistics['approved'] ?? 0"
            icon="fas fa-check-circle"
            color="green" />

        <x-stats-card
            title="Ditolak"
            :value="$statistics['rejected'] ?? 0"
            icon="fas fa-times-circle"
            color="red" />

        <x-stats-card
            title="Dibatalkan"
            :value="$statistics['cancelled'] ?? 0"
            icon="fas fa-ban"
            color="gray" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('approvals.business-trips.index') }}">
        <x-form.select
            name="worker_id"
            label="Pegawai"
            :selected="request('worker_id') ?? ''"
            placeholder="Semua Pegawai">
            @if(isset($workers))
                @foreach($workers as $worker)
                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                @endforeach
            @endif
        </x-form.select>

        <x-form.select
            name="status"
            label="Status"
            :options="[
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan'
            ]"
            :selected="request('status') ?? ''"
            placeholder="Semua Status" />

        <x-form.select
            name="month"
            label="Bulan"
            :selected="request('month') ?? ''"
            placeholder="Semua Bulan">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
            @endfor
        </x-form.select>

        <x-form.select
            name="year"
            label="Tahun"
            :selected="request('year') ?? ''"
            placeholder="Semua Tahun">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </x-form.select>
    </x-filter-section>

    {{-- Business Trip Requests Table --}}
    <x-card>
        @if($trips->isEmpty())
            <x-empty-state
                icon="fas fa-plane"
                title="Tidak ada data pengajuan perjalanan dinas"
                description="Pengajuan perjalanan dinas akan ditampilkan di sini" />
        @else
            {{-- Mobile View --}}
            <div class="md:hidden space-y-4 p-4 bg-gray-50/50">
                @foreach($trips as $index => $trip)
                    @php
                        $statusBadges = [
                            'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                            'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                        ];
                        $badge = $statusBadges[$trip->status] ?? ['variant' => 'secondary', 'label' => ucfirst($trip->status)];
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-3">
                            <div>
                                <span class="text-xs text-gray-500 font-medium">#{{ $trips->firstItem() + $index }}</span>
                                <div class="text-xs text-gray-400 mt-0.5 font-medium">
                                    <i class="far fa-clock mr-1"></i>{{ $trip->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-sm border border-gray-200">
                                    {{ substr($trip->worker->name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400 block mb-0.5">Pegawai</span>
                                    <span class="text-sm font-bold text-gray-900 leading-tight block">{{ $trip->worker->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">{{ $trip->worker->nip ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-3 grid grid-cols-2 gap-4 border border-gray-100">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                        <i class="fas fa-map-marker-alt text-indigo-400 mr-1"></i>Tujuan
                                    </span>
                                    <span class="text-sm font-bold text-indigo-700 block">{{ $trip->destination ?? '-' }}</span>
                                    <span class="text-xs text-gray-500 font-medium block truncate">{{ $trip->purpose ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                        <i class="fas fa-calendar-alt text-emerald-400 mr-1"></i>Tanggal & Durasi
                                    </span>
                                    <span class="text-xs font-bold text-emerald-700 block">{{ \Carbon\Carbon::parse($trip->start_date)->format('d M y') }} - {{ \Carbon\Carbon::parse($trip->end_date)->format('d M y') }}</span>
                                    <span class="text-xs text-gray-500 font-medium">{{ $trip->duration_label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ route('approvals.business-trips.show', $trip->id) }}"
                               class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition active:scale-95">
                                <i class="fas fa-search mr-1.5"></i> Periksa Pengajuan
                            </a>
                            <form action="{{ route('approvals.business-trips.destroy', $trip->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center rounded-xl bg-red-50 px-4 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition active:scale-95"
                                        title="Hapus"
                                        onclick="event.preventDefault(); showDeleteConfirm(() => this.closest('form').submit());">
                                    <i class="fas fa-trash-alt mr-1.5"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">No</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Pegawai</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Tujuan</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Durasi</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($trips as $index => $trip)
                            <tr>
                                <td class="px-6 py-3">{{ $trips->firstItem() + $index }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900">{{ $trip->worker->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ $trip->worker->nip ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900">{{ $trip->destination ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($trip->purpose ?? '', 40) }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="text-sm">{{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($trip->end_date)->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-3">{{ $trip->duration_label }}</td>
                                <td class="px-6 py-3">
                                    @php
                                        $statusBadges = [
                                            'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                            'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                                        ];
                                        $badge = $statusBadges[$trip->status] ?? ['variant' => 'secondary', 'label' => ucfirst($trip->status)];
                                    @endphp
                                    <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('approvals.business-trips.show', $trip->id) }}" class="text-blue-600 hover:text-blue-900" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('approvals.business-trips.destroy', $trip->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus" onclick="event.preventDefault(); showDeleteConfirm(() => this.closest('form').submit());">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($trips->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$trips" />
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
