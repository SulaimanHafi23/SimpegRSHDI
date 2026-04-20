@extends('layouts.admin')

@section('title', 'Data Absensi')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Data Absensi"
        description="Kelola data kehadiran pegawai"
        icon="fas fa-calendar-check">
        <x-slot:actions>
            @can('view-attendance')
                <x-button
                    variant="primary"
                    icon="fas fa-chart-bar"
                    onclick="window.location.href='{{ route('admin.absents.report') }}'">
                    Laporan
                </x-button>
            @endcan
            @can('create-attendance')
                <x-button
                    variant="success"
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.absents.create') }}'">
                    Absen Manual
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-stats-card
            title="Hadir"
            :value="$stats['present'] ?? 0"
            icon="fas fa-check-circle"
            color="green" />

        <x-stats-card
            title="Cuti"
            :value="$stats['leave'] ?? 0"
            icon="fas fa-plane"
            color="blue" />

        <x-stats-card
            title="Terlambat"
            :value="$stats['late'] ?? 0"
            icon="fas fa-clock"
            color="yellow" />

        <x-stats-card
            title="Sakit"
            :value="$stats['sick'] ?? 0"
            icon="fas fa-user-md"
            color="purple" />

        <x-stats-card
            title="Alpha"
            :value="$stats['absent'] ?? 0"
            icon="fas fa-times-circle"
            color="red" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.absents.index') }}">
        <x-form.input
            name="search"
            label="Pencarian"
            placeholder="Cari nama pegawai..."
            :value="$filters['search'] ?? ''" />

        <x-form.input
            name="start_date"
            label="Dari Tanggal"
            type="date"
            :value="$filters['start_date'] ?? ''" />

        <x-form.input
            name="end_date"
            label="Sampai Tanggal"
            type="date"
            :value="$filters['end_date'] ?? ''" />

        <x-form.select
            name="status"
            label="Status Kehadiran"
            :options="[
                'Present' => 'Hadir',
                'Late' => 'Terlambat',
                'Leave' => 'Cuti',
                'Sick' => 'Sakit',
                'Absent' => 'Alpha'
            ]"
            :selected="$filters['status'] ?? ''"
            placeholder="Semua Status" />
    </x-filter-section>

    {{-- Attendance Table --}}
    <x-card>
        @if(isset($attendances) && $attendances->isEmpty())
            <x-empty-state
                icon="fas fa-calendar-check"
                title="Data absensi akan ditampilkan di sini"
                description="Gunakan filter di atas untuk melihat data absensi"
                actionText="Absen Manual"
                :actionUrl="route('admin.absents.create')" />
        @elseif(isset($attendances))
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Shift</x-table.cell>
                        <x-table.cell header>Masuk</x-table.cell>
                        <x-table.cell header>Pulang</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($attendances as $index => $attendance)
                    <x-table.row>
                        <x-table.cell>{{ $attendances->firstItem() + $index }}</x-table.cell>
                        <x-table.cell>{{ $attendance->date ?? '-' }}</x-table.cell>
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $attendance->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $attendance->worker->nip ?? '-' }}</div>
                        </x-table.cell>
                        <x-table.cell>{{ $attendance->shift->name ?? '-' }}</x-table.cell>
                        <x-table.cell>{{ $attendance->check_in ?? '-' }}</x-table.cell>
                        <x-table.cell>{{ $attendance->check_out ?? '-' }}</x-table.cell>
                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'Present' => ['variant' => 'success', 'label' => 'Hadir'],
                                    'Late' => ['variant' => 'warning', 'label' => 'Terlambat'],
                                    'Leave' => ['variant' => 'primary', 'label' => 'Cuti'],
                                    'Sick' => ['variant' => 'secondary', 'label' => 'Sakit'],
                                    'Absent' => ['variant' => 'danger', 'label' => 'Alpha'],
                                ];
                                $badge = $statusBadges[$attendance->status] ?? ['variant' => 'secondary', 'label' => $attendance->status];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>
                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                @can('view-attendance')
                                    <a href="{{ route('admin.absents.show', $attendance->id) }}"
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                @endcan

                                @can('delete-attendance')
                                    <form action="{{ route('admin.absents.destroy', $attendance->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-900"
                                                title="Hapus"
                                                onclick="event.preventDefault(); showDeleteConfirm(() => this.closest('form').submit());">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            @if($attendances->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$attendances" />
                </div>
            @endif
        @else
            <x-empty-state
                icon="fas fa-calendar-check"
                title="Data absensi akan ditampilkan di sini"
                description="Gunakan filter di atas untuk melihat data absensi" />
        @endif
    </x-card>
</div>
@endsection
