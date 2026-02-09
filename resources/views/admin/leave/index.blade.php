@extends('layouts.admin')

@section('title', 'Manajemen Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Manajemen Cuti"
        description="Kelola pengajuan cuti pegawai"
        icon="fas fa-calendar-times">
        <x-slot:actions>
            {{-- Export Dropdown --}}
            <x-export-dropdown route="admin.leave.export" />

            @can('create-leave')
                <x-button
                    variant="success"
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.leave.create') }}'">
                    Ajukan Cuti
                </x-button>
            @endcan
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
    <x-filter-section action="{{ route('admin.leave.index') }}">
        <x-form.select
            name="worker_id"
            label="Pegawai"
            :selected="$filters['worker_id'] ?? ''"
            placeholder="Semua Pegawai">
            @foreach($workers as $worker)
                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="leave_type_id"
            label="Jenis Cuti"
            :selected="$filters['leave_type_id'] ?? ''"
            placeholder="Semua Jenis">
            @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="status"
            label="Status"
            :options="[
                'Pending' => 'Menunggu',
                'Approved' => 'Disetujui',
                'Rejected' => 'Ditolak',
                'Cancelled' => 'Dibatalkan'
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

    {{-- Leave Requests Table --}}
    <x-card>
        @if(isset($leaves) && $leaves->isEmpty())
            <x-empty-state
                icon="fas fa-calendar-times"
                title="Tidak ada data pengajuan cuti"
                description="Pengajuan cuti akan ditampilkan di sini"
                actionText="Ajukan Cuti"
                :actionUrl="route('admin.leave.create')" />
        @elseif(isset($leaves))
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Jenis Cuti</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Durasi</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($leaves as $index => $leave)
                    <x-table.row>
                        <x-table.cell>{{ $leaves->firstItem() + $index }}</x-table.cell>

                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $leave->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $leave->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $leave->leaveType->name ?? '-' }}</x-table.cell>

                        <x-table.cell>
                            <div class="text-sm">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $leave->total_days ?? 0 }} hari</x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                    'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                                ];
                                $badge = $statusBadges[$leave->status] ?? ['variant' => 'secondary', 'label' => $leave->status];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                {{-- Always show view button --}}
                                <a href="{{ route('admin.leave.show', $leave->id) }}"
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if($leave->status == 'pending')
                                    {{-- Approve button --}}
                                    <button onclick="approveLeave('{{ $leave->id }}')"
                                            class="text-green-600 hover:text-green-900"
                                            title="Setujui">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button onclick="rejectLeave('{{ $leave->id }}')"
                                            class="text-red-600 hover:text-red-900"
                                            title="Tolak">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif

                                @if($leave->status == 'pending')
                                        <a href="{{ route('admin.leave.edit', $leave->id) }}"
                                           class="text-indigo-600 hover:text-indigo-900"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.leave.destroy', $leave->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-900"
                                                title="Hapus"
                                                onclick="return confirm('Yakin ingin menghapus pengajuan cuti ini?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            @if($leaves->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$leaves" />
                </div>
            @endif
        @else
            <x-empty-state
                icon="fas fa-calendar-times"
                title="Tidak ada data pengajuan cuti"
                description="Gunakan filter di atas untuk melihat data" />
        @endif
    </x-card>
</div>

@push('scripts')
<script>
    function approveLeave(id) {
        if (confirm('Setujui pengajuan cuti ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/leaves/${id}/approve`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;

            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function rejectLeave(id) {
        const reason = prompt('Alasan penolakan:');
        if (reason && reason.trim() !== '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/leaves/${id}/reject`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = reason;

            form.appendChild(csrfInput);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
