@extends('layouts.admin')

@section('title', 'Approval Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Approval Perjalanan Dinas" 
        description="Kelola pengajuan perjalanan dinas pegawai"
        icon="fas fa-plane">
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
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Tujuan</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Durasi</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($trips as $index => $trip)
                    <x-table.row>
                        <x-table.cell>{{ $trips->firstItem() + $index }}</x-table.cell>
                        
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $trip->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $trip->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $trip->destination ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($trip->purpose ?? '', 40) }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="text-sm">{{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($trip->end_date)->format('d M Y') }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ \Carbon\Carbon::parse($trip->start_date)->diffInDays(\Carbon\Carbon::parse($trip->end_date)) + 1 }} hari</x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                    'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                                ];
                                $badge = $statusBadges[$trip->status] ?? ['variant' => 'secondary', 'label' => $trip->status];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('approvals.business-trips.show', $trip->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" 
                                   title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if($trip->status == 'pending')
                                    <form action="{{ route('approvals.business-trips.approve', $trip->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-900" 
                                                title="Setujui"
                                                onclick="return confirm('Apakah Anda yakin ingin menyetujui perjalanan dinas ini?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('approvals.business-trips.reject', $trip->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" 
                                                title="Tolak"
                                                onclick="return confirm('Apakah Anda yakin ingin menolak perjalanan dinas ini?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

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
