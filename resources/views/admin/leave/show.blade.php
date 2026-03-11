@extends('layouts.admin')

@section('title', 'Detail Pengajuan Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Detail Pengajuan Cuti"
        description="Informasi lengkap pengajuan cuti pegawai"
        icon="fas fa-calendar-check">
        <x-slot:actions>
            @if($leaveRequest->status == 'Pending')
                @can('edit-leave')
                    <x-button
                        variant="primary"
                        icon="fas fa-edit"
                        onclick="window.location.href='{{ route('admin.leave.edit', $leaveRequest->id) }}'">
                        Edit
                    </x-button>
                @endcan
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
                        'Pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu Persetujuan'],
                        'Approved' => ['variant' => 'success', 'icon' => 'fas fa-check-circle', 'label' => 'Disetujui'],
                        'Rejected' => ['variant' => 'danger', 'icon' => 'fas fa-times-circle', 'label' => 'Ditolak'],
                        'Cancelled' => ['variant' => 'secondary', 'icon' => 'fas fa-ban', 'label' => 'Dibatalkan'],
                    ];
                    $config = $statusConfig[$leaveRequest->status] ?? ['variant' => 'secondary', 'icon' => 'fas fa-info-circle', 'label' => $leaveRequest->status];
                @endphp

                <div>
                    <p class="text-sm text-gray-600 mb-2">Status Pengajuan</p>
                    <x-badge :variant="$config['variant']" :icon="$config['icon']" size="lg">
                        {{ $config['label'] }}
                    </x-badge>
                </div>
            </div>

            {{-- Approval Actions --}}
            @if($leaveRequest->status == 'Pending')
                @can('approve-leave')
                    <div class="flex gap-3">
                        <form action="{{ route('admin.leave.approve', $leaveRequest->id) }}" method="POST" class="inline">
                            @csrf
                            <x-button
                                type="submit"
                                variant="success"
                                icon="fas fa-check"
                                onclick="return confirm('Setujui pengajuan cuti ini?')">
                                Setujui
                            </x-button>
                        </form>
                        <form action="{{ route('admin.leave.reject', $leaveRequest->id) }}" method="POST" class="inline">
                            @csrf
                            <x-button
                                type="submit"
                                variant="danger"
                                icon="fas fa-times"
                                onclick="return confirm('Tolak pengajuan cuti ini?')">
                                Tolak
                            </x-button>
                        </form>
                    </div>
                @endcan
            @endif
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Worker Information --}}
            <x-card title="Informasi Pegawai">
                <div class="flex items-start space-x-4">
                    @if($leaveRequest->worker->photo)
                        <img class="h-20 w-20 rounded-lg object-cover"
                             src="{{ asset('storage/' . $leaveRequest->worker->photo) }}"
                             alt="{{ $leaveRequest->worker->name }}">
                    @else
                        <div class="h-20 w-20 rounded-lg bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-3xl">
                                {{ substr($leaveRequest->worker->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $leaveRequest->worker->name }}</h3>
                        <p class="text-gray-600">{{ $leaveRequest->worker->department->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500 mt-1">NIP: {{ $leaveRequest->worker->nip ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Leave Details --}}
            <x-card title="Detail Pengajuan Cuti">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jenis Cuti</label>
                            <p class="text-base font-semibold text-gray-900 mt-1">{{ $leaveRequest->leave_type }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Durasi</label>
                            <p class="text-base font-semibold text-gray-900 mt-1">{{ $leaveRequest->total_days }} Hari</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-200">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Mulai</label>
                            <p class="text-base text-gray-900 mt-1">{{ $leaveRequest->start_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Selesai</label>
                            <p class="text-base text-gray-900 mt-1">{{ $leaveRequest->end_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-sm font-medium text-gray-500">Alasan Cuti</label>
                        <p class="text-base text-gray-700 mt-2 leading-relaxed">{{ $leaveRequest->reason }}</p>
                    </div>

                    @if($leaveRequest->attachment)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500 mb-2 block">Lampiran</label>
                            <a href="{{ asset('storage/' . $leaveRequest->attachment) }}"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                                <i class="fas fa-paperclip mr-2"></i>
                                Lihat Lampiran
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Approval History --}}
            @if($leaveRequest->approved_at || $leaveRequest->rejected_at)
                <x-card title="Riwayat Persetujuan">
                    <div class="space-y-3">
                        @if($leaveRequest->approved_at)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Disetujui</p>
                                    <p class="text-sm text-gray-500">{{ $leaveRequest->approved_at->format('d M Y, H:i') }}</p>
                                    @if($leaveRequest->approved_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh: {{ $leaveRequest->approvedBy->name ?? '-' }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($leaveRequest->rejected_at)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-times text-red-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Ditolak</p>
                                    <p class="text-sm text-gray-500">{{ $leaveRequest->rejected_at->format('d M Y, H:i') }}</p>
                                    @if($leaveRequest->rejected_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh: {{ $leaveRequest->rejectedBy->name ?? '-' }}</p>
                                    @endif
                                    @if($leaveRequest->rejection_reason)
                                        <p class="text-sm text-gray-700 mt-2 italic">"{{ $leaveRequest->rejection_reason }}"</p>
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
            {{-- Quick Info --}}
            <x-card title="Informasi Tambahan">
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Diajukan Pada</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $leaveRequest->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    @if($leaveRequest->updated_at && $leaveRequest->updated_at != $leaveRequest->created_at)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-xs font-medium text-gray-500">Terakhir Diupdate</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $leaveRequest->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-xs font-medium text-gray-500">ID Pengajuan</label>
                        <p class="text-sm text-gray-900 mt-1 font-mono">#{{ str_pad($leaveRequest->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Quick Actions --}}
            <x-card title="Aksi Cepat">
                <div class="space-y-2">
                    @if($leaveRequest->status == 'Pending')
                        @can('edit-leave')
                            <x-button
                                variant="outline"
                                icon="fas fa-edit"
                                class="w-full justify-start"
                                onclick="window.location.href='{{ route('admin.leave.edit', $leaveRequest->id) }}'">
                                Edit Pengajuan
                            </x-button>
                        @endcan

                        @can('delete-leave')
                            <x-button
                                variant="outline"
                                icon="fas fa-trash"
                                class="w-full justify-start text-red-600 hover:bg-red-50"
                                onclick="if(confirm('Yakin ingin menghapus pengajuan ini?')) { document.getElementById('delete-form').submit(); }">
                                Hapus Pengajuan
                            </x-button>

                            <form id="delete-form" action="{{ route('admin.leave.destroy', $leaveRequest->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endcan
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
