@extends('layouts.admin')

@section('title', 'Persetujuan Tukar Shift')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Persetujuan Tukar Shift"
        description="Kelola permintaan pertukaran shift dari pegawai"
        icon="fas fa-exchange-alt">
        <x-slot name="actions">
            <x-export-buttons :route="route('manager.shift-swap-approvals.export')" title="Export Tukar Shift">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Diterima</option>
                        <option value="awaiting_approval">Menunggu Persetujuan</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="cancelled">Dibatalkan</option>
                        <option value="executed">Dieksekusi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemohon</label>
                    <select name="requester_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">Semua Pemohon</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </x-export-buttons>
        </x-slot>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-stats-card
            title="Total Permintaan"
            :value="$statistics['total'] ?? 0"
            icon="fas fa-file-alt"
            color="blue" />

        <x-stats-card
            title="Menunggu Persetujuan"
            :value="$statistics['awaiting_approval'] ?? 0"
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
            title="Dieksekusi"
            :value="$statistics['executed'] ?? 0"
            icon="fas fa-check-double"
            color="purple" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('manager.shift-swap-approvals.index') }}">
        <x-form.select
            name="status"
            label="Status"
            :selected="$filters['status'] ?? ''"
            placeholder="Semua Status">
            <option value="pending">Pending</option>
            <option value="accepted">Diterima</option>
            <option value="awaiting_approval">Menunggu Persetujuan</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="executed">Dieksekusi</option>
        </x-form.select>

        <x-form.select
            name="requester_id"
            label="Pemohon"
            :selected="$filters['requester_id'] ?? ''"
            placeholder="Semua Pemohon">
            @foreach($workers as $worker)
                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.input
            type="date"
            name="date_from"
            label="Dari Tanggal"
            :value="$filters['date_from'] ?? ''" />

        <x-form.input
            type="date"
            name="date_to"
            label="Sampai Tanggal"
            :value="$filters['date_to'] ?? ''" />
    </x-filter-section>

    {{-- Main Table --}}
    <x-card>
        @if(isset($items) && $items->isEmpty())
            <x-empty-state
                icon="fas fa-exchange-alt"
                title="Tidak ada data tukar shift"
                description="Permintaan tukar shift akan ditampilkan di sini" />
        @elseif(isset($items))
            {{-- Mobile Card Layout --}}
            <div class="md:hidden space-y-4">
                @foreach($items as $index => $item)
                    @php
                        $statusBadges = [
                            'pending' => ['variant' => 'secondary', 'label' => 'Pending'],
                            'accepted' => ['variant' => 'info', 'label' => 'Diterima'],
                            'awaiting_approval' => ['variant' => 'warning', 'label' => 'Menunggu Persetujuan'],
                            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                            'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                            'executed' => ['variant' => 'success', 'label' => 'Dieksekusi'],
                        ];
                        $badge = $statusBadges[$item->status] ?? ['variant' => 'secondary', 'label' => ucfirst($item->status)];
                        $reqShift = $item->requesterShift?->shift;
                        $tgtShift = $item->targetShift?->shift;
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-xs text-gray-500">#{{ $items->firstItem() + $index }}</span>
                                <div class="text-sm text-gray-600">
                                    {{ $item->requested_at?->format('d M Y H:i') ?? $item->created_at->format('d M Y H:i') }}
                                </div>
                            </div>
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-gray-500">Pemohon:</span>
                                <span class="text-gray-900">{{ $item->requester->name }}</span>
                                <span class="text-xs text-gray-400">({{ $item->requester->nip ?? '-' }})</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Target:</span>
                                @if($item->targetWorker)
                                    <span class="text-gray-900">{{ $item->targetWorker->name }}</span>
                                    <span class="text-xs text-gray-400">({{ $item->targetWorker->nip ?? '-' }})</span>
                                @else
                                    <x-badge variant="secondary">Open Request</x-badge>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="font-medium text-gray-500 block">Shift Pemohon:</span>
                                    @if($reqShift)
                                        <span class="text-gray-900">{{ $reqShift->name }}</span>
                                        <div class="text-xs text-gray-500">{{ $reqShift->start_time }} - {{ $reqShift->end_time }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="font-medium text-gray-500 block">Shift Target:</span>
                                    @if($tgtShift)
                                        <span class="text-gray-900">{{ $tgtShift->name }}</span>
                                        <div class="text-xs text-gray-500">{{ $tgtShift->start_time }} - {{ $tgtShift->end_time }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Tanggal Tukar:</span>
                                @if($item->swap_type === 'single_date' && $item->swap_date)
                                    <span class="text-gray-900">{{ $item->swap_date->format('d M Y') }}</span>
                                @elseif($item->swap_type === 'date_range' && $item->swap_start_date && $item->swap_end_date)
                                    <span class="text-gray-900">{{ $item->swap_start_date->format('d M') }} - {{ $item->swap_end_date->format('d M Y') }}</span>
                                @elseif($item->swap_type === 'recurring' && $item->swap_dates)
                                    <span class="text-gray-900 text-xs">{{ collect($item->swap_dates)->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->join(', ') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                            @if($item->reason)
                                <div>
                                    <span class="font-medium text-gray-500">Alasan:</span>
                                    <span class="text-gray-600">{{ Str::limit($item->reason, 80) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end gap-3 mt-3 pt-3 border-t border-gray-100">
                            <a href="{{ route('manager.shift-swap-approvals.show', $item->id) }}"
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                            @if($item->status === 'awaiting_approval')
                                <button onclick="approveSwap('{{ $item->id }}')"
                                        class="text-green-600 hover:text-green-900 text-sm font-medium">
                                    <i class="fas fa-check mr-1"></i> Setujui
                                </button>
                                <button onclick="rejectSwap('{{ $item->id }}')"
                                        class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    <i class="fas fa-times mr-1"></i> Tolak
                                </button>
                            @endif
                            @if($item->status === 'approved' && !$item->executed_at)
                                <button onclick="executeSwap('{{ $item->id }}')"
                                        class="text-purple-600 hover:text-purple-900 text-sm font-medium">
                                    <i class="fas fa-check-double mr-1"></i> Eksekusi
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block">
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Tanggal Permintaan</x-table.cell>
                        <x-table.cell header>Pemohon</x-table.cell>
                        <x-table.cell header>Target</x-table.cell>
                        <x-table.cell header>Shift Pemohon</x-table.cell>
                        <x-table.cell header>Shift Target</x-table.cell>
                        <x-table.cell header>Tanggal Tukar</x-table.cell>
                        <x-table.cell header>Alasan</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($items as $index => $item)
                    <x-table.row>
                        <x-table.cell>{{ $items->firstItem() + $index }}</x-table.cell>

                        <x-table.cell>
                            <div class="text-sm">{{ $item->requested_at?->format('d M Y') ?? $item->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $item->requested_at?->format('H:i') ?? $item->created_at->format('H:i') }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $item->requester->name }}</div>
                            <div class="text-sm text-gray-500">{{ $item->requester->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            @if($item->targetWorker)
                                <div class="font-medium text-gray-900">{{ $item->targetWorker->name }}</div>
                                <div class="text-sm text-gray-500">{{ $item->targetWorker->nip ?? '-' }}</div>
                            @else
                                <x-badge variant="secondary">Open Request</x-badge>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $reqShift = $item->requesterShift?->shift;
                            @endphp
                            @if($reqShift)
                                <div class="text-sm">{{ $reqShift->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $reqShift->start_time }} - {{ $reqShift->end_time }}
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $tgtShift = $item->targetShift?->shift;
                            @endphp
                            @if($tgtShift)
                                <div class="text-sm">{{ $tgtShift->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $tgtShift->start_time }} - {{ $tgtShift->end_time }}
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            @if($item->swap_type === 'single_date' && $item->swap_date)
                                <div class="text-sm">{{ $item->swap_date->format('d M Y') }}</div>
                            @elseif($item->swap_type === 'date_range' && $item->swap_start_date && $item->swap_end_date)
                                <div class="text-sm">{{ $item->swap_start_date->format('d M') }} - {{ $item->swap_end_date->format('d M Y') }}</div>
                            @elseif($item->swap_type === 'recurring' && $item->swap_dates)
                                <div class="text-xs">{{ collect($item->swap_dates)->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->join(', ') }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            <span class="text-sm text-gray-600">{{ Str::limit($item->reason ?? '-', 40) }}</span>
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'secondary', 'label' => 'Pending'],
                                    'accepted' => ['variant' => 'info', 'label' => 'Diterima'],
                                    'awaiting_approval' => ['variant' => 'warning', 'label' => 'Menunggu Persetujuan'],
                                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                    'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                                    'executed' => ['variant' => 'success', 'label' => 'Dieksekusi'],
                                ];
                                $badge = $statusBadges[$item->status] ?? ['variant' => 'secondary', 'label' => ucfirst($item->status)];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('manager.shift-swap-approvals.show', $item->id) }}"
                                   class="text-blue-600 hover:text-blue-900 inline-flex items-center"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($item->status === 'awaiting_approval')
                                    <button onclick="approveSwap('{{ $item->id }}')"
                                            class="text-green-600 hover:text-green-900 inline-flex items-center"
                                            title="Setujui">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="rejectSwap('{{ $item->id }}')"
                                            class="text-red-600 hover:text-red-900 inline-flex items-center"
                                            title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                                @if($item->status === 'approved' && !$item->executed_at)
                                    <button onclick="executeSwap('{{ $item->id }}')"
                                            class="text-purple-600 hover:text-purple-900 inline-flex items-center"
                                            title="Eksekusi">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                @endif
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>
            </div>

            {{-- Pagination --}}
            @if($items->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$items" />
                </div>
            @endif
        @else
            <x-empty-state
                icon="fas fa-exchange-alt"
                title="Tidak ada data tukar shift"
                description="Gunakan filter di atas untuk melihat data" />
        @endif
    </x-card>
</div>

@push('scripts')
<script>
    function approveSwap(id) {
        if (confirm('Apakah Anda yakin ingin menyetujui permintaan tukar shift ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/manager/shift-swap-approvals/${id}/approve`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function rejectSwap(id) {
        const reason = prompt('Masukkan alasan penolakan:');
        if (reason && reason.trim() !== '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/manager/shift-swap-approvals/${id}/reject`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        } else if (reason !== null) {
            alert('Alasan penolakan harus diisi!');
        }
    }

    function executeSwap(id) {
        if (confirm('Apakah Anda yakin ingin mengeksekusi pertukaran shift ini? Shift akan segera ditukar.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/manager/shift-swap-approvals/${id}/execute`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
