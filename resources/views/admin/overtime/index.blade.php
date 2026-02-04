@extends('layouts.admin')

@section('title', 'Manajemen Lembur')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Manajemen Lembur" 
        description="Kelola data lembur pegawai"
        icon="fas fa-business-time">
        <x-slot:actions>
            @can('create-overtime')
                <x-button 
                    variant="success" 
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.overtime.create') }}'">
                    Input Lembur
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stats-card 
            title="Total Lembur" 
            :value="$statistics['total'] ?? 0" 
            icon="fas fa-clock" 
            color="blue" />
        
        <x-stats-card 
            title="Pending" 
            :value="$statistics['pending'] ?? 0" 
            icon="fas fa-hourglass-half" 
            color="yellow" />
        
        <x-stats-card 
            title="Approved" 
            :value="$statistics['approved'] ?? 0" 
            icon="fas fa-check-circle" 
            color="green" />
        
        <x-stats-card 
            title="Total Jam" 
            :value="($statistics['total_hours'] ?? 0) . ' Jam'" 
            icon="fas fa-stopwatch" 
            color="purple" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.overtime.index') }}">
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
            name="status" 
            label="Status"
            :options="[
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Rejected' => 'Rejected'
            ]"
            :selected="$filters['status'] ?? ''"
            placeholder="Semua Status" />

        <x-form.input 
            name="start_date" 
            label="Tanggal Mulai" 
            type="date"
            :value="$filters['start_date'] ?? ''" />

        <x-form.input 
            name="end_date" 
            label="Tanggal Akhir" 
            type="date"
            :value="$filters['end_date'] ?? ''" />

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bulan & Tahun</label>
            <div class="flex gap-2">
                <select name="month" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                    <option value="">Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ ($filters['month'] ?? '') == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('M') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                    <option value="">Tahun</option>
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ ($filters['year'] ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </x-filter-section>

    {{-- Overtime Table --}}
    <x-card>
        @if(isset($overtimes) && $overtimes->isEmpty())
            <x-empty-state 
                icon="fas fa-business-time"
                title="Tidak ada data lembur"
                description="Data lembur akan ditampilkan di sini"
                actionText="Input Lembur"
                :actionUrl="route('admin.overtime.create')" />
        @elseif(isset($overtimes))
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Waktu</x-table.cell>
                        <x-table.cell header>Durasi</x-table.cell>
                        <x-table.cell header>Keterangan</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($overtimes as $index => $overtime)
                    <x-table.row>
                        <x-table.cell>{{ $overtimes->firstItem() + $index }}</x-table.cell>
                        
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $overtime->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $overtime->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $overtime->date ?? '-' }}</x-table.cell>

                        <x-table.cell>
                            <div class="text-sm">{{ $overtime->start_time ?? '-' }}</div>
                            <div class="text-xs text-gray-500">s/d {{ $overtime->end_time ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $overtime->duration ?? 0 }} jam</x-table.cell>

                        <x-table.cell>
                            <span class="text-sm text-gray-600">{{ Str::limit($overtime->description ?? '-', 30) }}</span>
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                ];
                                $badge = $statusBadges[$overtime->status] ?? ['variant' => 'secondary', 'label' => ucfirst($overtime->status)];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.overtime.show', $overtime->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 inline-flex items-center" 
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($overtime->status === 'pending')
                                    <button onclick="approveOvertime('{{ $overtime->id }}')" 
                                            class="text-green-600 hover:text-green-900 inline-flex items-center" 
                                            title="Setujui">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="rejectOvertime('{{ $overtime->id }}')" 
                                            class="text-red-600 hover:text-red-900 inline-flex items-center" 
                                            title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            @if($overtimes->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$overtimes" />
                </div>
            @endif
        @else
            <x-empty-state 
                icon="fas fa-business-time"
                title="Tidak ada data lembur"
                description="Gunakan filter di atas untuk melihat data" />
        @endif
    </x-card>
</div>

@push('scripts')
<script>
    function approveOvertime(id) {
        if (confirm('Apakah Anda yakin ingin menyetujui pengajuan lembur ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/overtimes/${id}/approve`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function rejectOvertime(id) {
        const reason = prompt('Masukkan alasan penolakan:');
        if (reason && reason.trim() !== '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/overtimes/${id}/reject`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);
            
            document.body.appendChild(form);
            form.submit();
        } else if (reason !== null) {
            alert('Alasan penolakan harus diisi!');
        }
    }
</script>
@endpush
@endsection
