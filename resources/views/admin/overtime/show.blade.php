@extends('layouts.admin')

@section('title', 'Detail Lembur')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Detail Data Lembur"
        description="Informasi lengkap data lembur pegawai"
        icon="fas fa-clock">
        <x-slot:actions>
            @if(strtolower($overtime->status) == 'pending')
                <x-button
                    variant="primary"
                    icon="fas fa-edit"
                    onclick="window.location.href='{{ route('admin.overtime.edit', $overtime->id) }}'">
                    Edit
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Alert Messages --}}
    @if(session('success'))
        <x-alert type="success" dismissible>
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" dismissible>
            {{ session('error') }}
        </x-alert>
    @endif

    {{-- Status Card --}}
    <x-card>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @php
                    $statusConfig = [
                        'Pending' => ['variant' => 'warning', 'icon' => 'fas fa-hourglass-half', 'label' => 'Pending'],
                        'Approved' => ['variant' => 'success', 'icon' => 'fas fa-check-circle', 'label' => 'Approved'],
                        'Rejected' => ['variant' => 'danger', 'icon' => 'fas fa-times-circle', 'label' => 'Rejected'],
                    ];
                    $config = $statusConfig[$overtime->status] ?? ['variant' => 'secondary', 'icon' => 'fas fa-info-circle', 'label' => $overtime->status];
                @endphp

                <div>
                    <p class="text-sm text-gray-600 mb-2">Status Lembur</p>
                    <x-badge :variant="$config['variant']" :icon="$config['icon']" size="lg">
                        {{ $config['label'] }}
                    </x-badge>
                </div>
            </div>

            {{-- Approval Actions --}}
            @if(strtolower($overtime->status) == 'pending')
                <div class="flex gap-3">
                    <form action="{{ route('admin.overtime.approve', $overtime->id) }}" method="POST" class="inline">
                        @csrf
                        <x-button
                            type="submit"
                            variant="success"
                            icon="fas fa-check"
                            onclick="return confirm('Approve data lembur ini?')">
                            Approve
                        </x-button>
                    </form>
                    <form action="{{ route('admin.overtime.reject', $overtime->id) }}" method="POST" class="inline">
                        @csrf
                        <x-button
                            type="submit"
                            variant="danger"
                            icon="fas fa-times"
                            onclick="return confirm('Reject data lembur ini?')">
                            Reject
                        </x-button>
                    </form>
                </div>
            @endif
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Worker Information --}}
            <x-card title="Informasi Pegawai">
                <div class="flex items-start space-x-4">
                    @if($overtime->worker->photo)
                        <img class="h-20 w-20 rounded-lg object-cover"
                             src="{{ asset('storage/' . $overtime->worker->photo) }}"
                             alt="{{ $overtime->worker->name }}">
                    @else
                        <div class="h-20 w-20 rounded-lg bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-3xl">
                                {{ substr($overtime->worker->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $overtime->worker->name }}</h3>
                        <p class="text-gray-600">{{ $overtime->worker->department->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500 mt-1">NIP: {{ $overtime->worker->nip ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Overtime Details --}}
            <x-card title="Detail Lembur">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Lembur</label>
                            <p class="text-base font-semibold text-gray-900 mt-1">{{ $overtime->overtime_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Total Jam</label>
                            <p class="text-base font-semibold text-gray-900 mt-1">{{ $overtime->total_hours }} Jam</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 pt-3 border-t border-gray-200">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Shift pada Hari Lembur</label>
                            @if($overtime->actual_shift)
                                <p class="text-base font-semibold text-gray-900 mt-1">{{ $overtime->actual_shift->name }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($overtime->actual_shift->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($overtime->actual_shift->end_time)->format('H:i') }}
                                </p>
                            @else
                                <p class="text-base text-gray-400 mt-1">-</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-200">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Waktu Mulai</label>
                            <p class="text-base text-gray-900 mt-1">{{ $overtime->start_time }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Waktu Selesai</label>
                            <p class="text-base text-gray-900 mt-1">{{ $overtime->end_time }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-sm font-medium text-gray-500">Keterangan/Alasan Lembur</label>
                        <p class="text-base text-gray-700 mt-2 leading-relaxed">{{ $overtime->reason }}</p>
                    </div>

                    @if($overtime->attachment)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500 mb-2 block">Lampiran</label>
                            <a href="{{ asset('storage/' . $overtime->attachment) }}"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                                <i class="fas fa-paperclip mr-2"></i>
                                Lihat Lampiran
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Approval Timeline --}}
            @if($overtime->approved_at || $overtime->rejected_at)
                <x-card title="Timeline Persetujuan">
                    <div class="space-y-3">
                        @if($overtime->approved_at)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Approved</p>
                                    <p class="text-sm text-gray-500">{{ $overtime->approved_at->format('d M Y, H:i') }}</p>
                                    @if($overtime->approved_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh: {{ $overtime->approvedBy->name ?? '-' }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($overtime->rejected_at)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-times text-red-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Rejected</p>
                                    <p class="text-sm text-gray-500">{{ $overtime->rejected_at->format('d M Y, H:i') }}</p>
                                    @if($overtime->rejected_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh: {{ $overtime->rejectedBy->name ?? '-' }}</p>
                                    @endif
                                    @if($overtime->rejection_reason)
                                        <p class="text-sm text-gray-700 mt-2 italic">"{{ $overtime->rejection_reason }}"</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Statistics --}}
            <div class="grid grid-cols-1 gap-4">
                <x-stats-card
                    title="Total Jam"
                    :value="$overtime->total_hours . ' Jam'"
                    icon="fas fa-stopwatch"
                    color="purple" />

                @if($overtime->status == 'Approved')
                    <x-stats-card
                        title="Kompensasi"
                        :value="'Rp ' . number_format($overtime->total_hours * 50000, 0, ',', '.')"
                        icon="fas fa-money-bill-wave"
                        color="green" />
                @endif
            </div>

            {{-- Quick Info --}}
            <x-card title="Informasi Tambahan">
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Diinput Pada</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $overtime->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    @if($overtime->updated_at && $overtime->updated_at != $overtime->created_at)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-xs font-medium text-gray-500">Terakhir Diupdate</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $overtime->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-xs font-medium text-gray-500">ID Lembur</label>
                        <p class="text-sm text-gray-900 mt-1 font-mono">#{{ str_pad($overtime->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Quick Actions --}}
            <x-card title="Aksi Cepat">
                <div class="space-y-2">
                    @if(strtolower($overtime->status) == 'pending')
                        <x-button
                            variant="outline"
                            icon="fas fa-edit"
                            class="w-full justify-start"
                            onclick="window.location.href='{{ route('admin.overtime.edit', $overtime->id) }}'">
                            Edit Lembur
                        </x-button>
                        <x-button
                            variant="outline"
                            icon="fas fa-trash"
                            class="w-full justify-start text-red-600 hover:bg-red-50"
                            onclick="if(confirm('Yakin ingin menghapus data lembur ini?')) { document.getElementById('delete-form').submit(); }">
                            Hapus Data
                        </x-button>
                        <form id="delete-form" action="{{ route('admin.overtime.destroy', $overtime->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <form action="{{ route('admin.overtime.approve', $overtime->id) }}" method="POST" class="inline">
                            @csrf
                            <x-button
                                type="submit"
                                variant="success"
                                icon="fas fa-check"
                                class="w-full justify-start"
                                onclick="return confirm('Approve data lembur ini?')">
                                Approve
                            </x-button>
                        </form>
                        <form action="{{ route('admin.overtime.reject', $overtime->id) }}" method="POST" class="inline">
                            @csrf
                            <x-button
                                type="submit"
                                variant="danger"
                                icon="fas fa-times"
                                class="w-full justify-start"
                                onclick="return confirm('Reject data lembur ini?')">
                                Reject
                            </x-button>
                        </form>
                    @endif

                    <x-button
                        variant="outline"
                        icon="fas fa-print"
                        class="w-full justify-start"
                        onclick="window.print()">
                        Cetak Detail
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
