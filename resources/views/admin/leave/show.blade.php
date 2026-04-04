@extends('layouts.admin')

@section('title', 'Detail Pengajuan Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Detail Pengajuan Cuti"
        description="Informasi lengkap pengajuan cuti pegawai"
        icon="fas fa-calendar-check">
    </x-page-header>

    {{-- Alert Messages --}}


    {{-- Status Card --}}
    <x-card>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @php
                    $normalizedStatus = strtolower((string) $leaveRequest->status);
                    $statusConfig = [
                        'pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu Persetujuan'],
                        'approved' => ['variant' => 'success', 'icon' => 'fas fa-check-circle', 'label' => 'Disetujui'],
                        'rejected' => ['variant' => 'danger', 'icon' => 'fas fa-times-circle', 'label' => 'Ditolak'],
                        'cancelled' => ['variant' => 'secondary', 'icon' => 'fas fa-ban', 'label' => 'Dibatalkan'],
                    ];
                    $config = $statusConfig[$normalizedStatus] ?? ['variant' => 'secondary', 'icon' => 'fas fa-info-circle', 'label' => $leaveRequest->status];
                @endphp

                <div>
                    <p class="text-sm text-gray-600 mb-2">Status Pengajuan</p>
                    <x-badge :variant="$config['variant']" :icon="$config['icon']" size="lg">
                        {{ $config['label'] }}
                    </x-badge>
                </div>
            </div>

            {{-- Approval Actions --}}
            @if($normalizedStatus === 'pending')
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
                    <x-button
                        type="button"
                        variant="danger"
                        icon="fas fa-times"
                        onclick="openLeaveRejectModal()">
                        Tolak
                    </x-button>
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
            @if($leaveRequest->approved_at)
                <x-card title="Riwayat Persetujuan">
                    <div class="space-y-3">
                        @if($normalizedStatus === 'approved')
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
                                        <p class="text-xs text-gray-400 mt-1">Oleh:
                                            {{ $leaveRequest->approver->worker->name ?? $leaveRequest->approver->username ?? $leaveRequest->approver->email ?? '-' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($normalizedStatus === 'rejected')
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-times text-red-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Ditolak</p>
                                    <p class="text-sm text-gray-500">{{ $leaveRequest->approved_at->format('d M Y, H:i') }}</p>
                                    @if($leaveRequest->approved_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh:
                                            {{ $leaveRequest->approver->worker->name ?? $leaveRequest->approver->username ?? $leaveRequest->approver->email ?? '-' }}
                                        </p>
                                    @endif
                                    @if($leaveRequest->rejection_reason)
                                        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Alasan Penolakan</p>
                                            <p class="mt-1 text-sm leading-relaxed text-red-800">{{ $leaveRequest->rejection_reason }}</p>
                                        </div>
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
                    @if($normalizedStatus === 'pending')
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
                </div>
            </x-card>
        </div>
    </div>
 </div>

@if($normalizedStatus === 'pending')
    <div id="leave-reject-modal" class="hidden fixed inset-0 z-50" onclick="if(event.target === this) closeLeaveRejectModal()">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-xl" onclick="event.stopPropagation()">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Tolak Pengajuan Cuti</h3>
                    <p class="mt-1 text-sm text-gray-500">Isi alasan penolakan agar pegawai memahami keputusan.</p>
                </div>
                <form id="reject-form" action="{{ route('admin.leave.reject', $leaveRequest->id) }}" method="POST" class="px-5 py-4" onsubmit="return validateLeaveRejectForm()">
                    @csrf
                    <label for="rejection_reason" class="mb-2 block text-sm font-medium text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100" placeholder="Tuliskan alasan penolakan..."></textarea>
                    <p id="leave-reject-error" class="mt-2 hidden text-xs text-red-600">Alasan penolakan wajib diisi.</p>
                    <div class="mt-4 flex justify-end gap-2 border-t border-gray-200 pt-4">
                        <button type="button" onclick="closeLeaveRejectModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="rounded-lg border border-red-700 bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Tolak Cuti</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    function openLeaveRejectModal() {
        const modal = document.getElementById('leave-reject-modal');
        const textarea = document.getElementById('rejection_reason');
        const error = document.getElementById('leave-reject-error');

        if (!modal || !textarea) {
            return;
        }

        textarea.value = '';
        textarea.classList.remove('border-red-500');
        if (error) {
            error.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        setTimeout(() => textarea.focus(), 50);
    }

    function closeLeaveRejectModal() {
        const modal = document.getElementById('leave-reject-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function validateLeaveRejectForm() {
        const textarea = document.getElementById('rejection_reason');
        const error = document.getElementById('leave-reject-error');

        if (!textarea) {
            return false;
        }

        const reason = textarea.value.trim();
        if (!reason) {
            textarea.classList.add('border-red-500');
            if (error) {
                error.classList.remove('hidden');
            }
            textarea.focus();
            return false;
        }

        textarea.value = reason;
        textarea.classList.remove('border-red-500');
        if (error) {
            error.classList.add('hidden');
        }

        return true;
    }
</script>
@endpush
@endsection
