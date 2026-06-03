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
            <x-button variant="outline" icon="fas fa-arrow-left" onclick="window.location.href='{{ route('approvals.leaves.index') }}'">
                Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Alert Messages --}}


    {{-- Status Card --}}
    <x-card>
        @php
            $user = Auth::user();
            $isAdmin = $user->hasRole(['Admin', 'Super Admin', 'admin', 'super admin', 'superadmin']);
            $isHR = $user->hasRole(['HR', 'hr']) || $isAdmin;
            $isManager = $user->hasRole(['Manager', 'manager']) || $isAdmin;
        @endphp
        
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @php
                    $normalizedStatus = strtolower((string) $leave->status);
                    $statusConfig = [
                        'pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu Verifikasi Manager'],
                        'manager_verified' => ['variant' => 'info', 'icon' => 'fas fa-user-check', 'label' => 'Telah Diverifikasi Manager'],
                        'approved' => ['variant' => 'success', 'icon' => 'fas fa-check-circle', 'label' => 'Disetujui'],
                        'rejected' => ['variant' => 'danger', 'icon' => 'fas fa-times-circle', 'label' => 'Ditolak'],
                        'cancelled' => ['variant' => 'secondary', 'icon' => 'fas fa-ban', 'label' => 'Dibatalkan'],
                    ];
                    $config = $statusConfig[$normalizedStatus] ?? ['variant' => 'secondary', 'icon' => 'fas fa-info-circle', 'label' => $leave->status];
                @endphp

                <div>
                    <p class="text-sm text-gray-600 mb-2">Status Pengajuan</p>
                    <x-badge :variant="$config['variant']" :icon="$config['icon']" size="lg">
                        {{ $config['label'] }}
                    </x-badge>
                </div>
            </div>

                        {{-- Approval Actions --}}
            @if($normalizedStatus === 'pending' && $isManager)
                <div class="flex gap-3">
                    <form action="{{ route('approvals.leaves.verify', $leave->id) }}" method="POST" class="inline">
                        @csrf
                        <x-button
                            type="submit"
                            variant="success"
                            icon="fas fa-check-double"
                            onclick="event.preventDefault(); showConfirmAlert('Verifikasi Pengajuan?', 'Anda yakin ingin memverifikasi pengajuan cuti ini? Pengajuan akan diteruskan ke HR.', () => this.closest('form').submit());">
                            Verifikasi
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
            @elseif($normalizedStatus === 'manager_verified')
                @if($isHR)
                    <div class="flex gap-3">
                        <form action="{{ route('approvals.leaves.approve', $leave->id) }}" method="POST" class="inline">
                            @csrf
                            <x-button
                                type="submit"
                                variant="success"
                                icon="fas fa-check-circle"
                                onclick="event.preventDefault(); showConfirmAlert('Setujui Pengajuan?', 'Anda yakin ingin menyetujui pengajuan cuti ini?', () => this.closest('form').submit());">
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
                @else
                    <div class="text-sm font-medium text-blue-700 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                        <i class="fas fa-info-circle mr-2"></i>
                        Pengajuan ini telah diverifikasi manager dan sekarang menunggu persetujuan akhir dari HR.
                    </div>
                @endif
            @endif
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Worker Information --}}
            <x-card title="Informasi Pegawai">
                <div class="flex items-start space-x-4">
                    @if($leave->worker->photo)
                        <img class="h-20 w-20 rounded-lg object-cover"
                             src="{{ asset('storage/' . $leave->worker->photo) }}"
                             alt="{{ $leave->worker->name }}">
                    @else
                        <div class="h-20 w-20 rounded-lg bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-3xl">
                                {{ substr($leave->worker->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $leave->worker->name }}</h3>
                        <p class="text-gray-600">{{ $leave->worker->department->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500 mt-1">NIP: {{ $leave->worker->nip ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Leave Details --}}
            <x-card title="Detail Pengajuan Cuti">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jenis Cuti</label>
                            <p class="text-base font-semibold text-gray-900 mt-1">{{ $leave->leaveType->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Durasi</label>
                            <p class="text-base font-semibold text-gray-900 mt-1">{{ $leave->total_days }} Hari</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-200">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Mulai</label>
                            <p class="text-base text-gray-900 mt-1">{{ $leave->start_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Selesai</label>
                            <p class="text-base text-gray-900 mt-1">{{ $leave->end_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-sm font-medium text-gray-500">Alasan Cuti</label>
                        <p class="text-base text-gray-700 mt-2 leading-relaxed">{{ $leave->reason }}</p>
                    </div>

                    @php
                        $attachmentPath = $leave->attachment_path ?? $leave->attachment ?? null;
                        $attachmentUrl = $attachmentPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($attachmentPath) : null;
                        $attachmentExtension = $attachmentPath ? strtolower(pathinfo($attachmentPath, PATHINFO_EXTENSION)) : null;
                        $isImageAttachment = in_array($attachmentExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                        $isPdfAttachment = $attachmentExtension === 'pdf';
                    @endphp

                    @if($attachmentUrl)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500 mb-3 block">Berkas Pendukung</label>

                            <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ basename($attachmentPath) }}</p>
                                        <p class="text-xs text-gray-500">{{ strtoupper($attachmentExtension ?? 'FILE') }}</p>
                                    </div>
                                    <a href="{{ $attachmentUrl }}"
                                       target="_blank"
                                       class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        Buka File
                                    </a>
                                </div>

                                @if($isImageAttachment)
                                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <img src="{{ $attachmentUrl }}" alt="Preview berkas pendukung"
                                             class="w-full object-contain bg-white" style="height: clamp(42rem, 92vh, 92rem);">
                                    </div>
                                @elseif($isPdfAttachment)
                                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <iframe src="{{ $attachmentUrl }}#zoom=220" class="w-full" style="height: clamp(42rem, 92vh, 92rem);" title="Preview PDF berkas pendukung"></iframe>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-600">
                                        Preview langsung belum tersedia untuk jenis file ini. Gunakan tombol Buka File untuk melihat lampiran.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Approval History --}}
            @if($leave->approved_at)
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
                                    <p class="text-sm text-gray-500">{{ $leave->approved_at->format('d M Y, H:i') }}</p>
                                    @if($leave->approved_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh:
                                            {{ $leave->approver->worker->name ?? $leave->approver->username ?? $leave->approver->email ?? '-' }}
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
                                    <p class="text-sm text-gray-500">{{ $leave->approved_at->format('d M Y, H:i') }}</p>
                                    @if($leave->approved_by)
                                        <p class="text-xs text-gray-400 mt-1">Oleh:
                                            {{ $leave->approver->worker->name ?? $leave->approver->username ?? $leave->approver->email ?? '-' }}
                                        </p>
                                    @endif
                                    @if($leave->rejection_reason)
                                        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Alasan Penolakan</p>
                                            <p class="mt-1 text-sm leading-relaxed text-red-800">{{ $leave->rejection_reason }}</p>
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
                        <p class="text-sm text-gray-900 mt-1">{{ $leave->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    @if($leave->updated_at && $leave->updated_at != $leave->created_at)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-xs font-medium text-gray-500">Terakhir Diupdate</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $leave->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-xs font-medium text-gray-500">ID Pengajuan</label>
                        <p class="text-sm text-gray-900 mt-1 font-mono">#{{ str_pad($leave->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Quick Actions --}}
            <x-card title="Aksi Cepat">
                <div class="space-y-2">
                    
                </div>
            </x-card>
        </div>
    </div>
 </div>

@if(in_array($normalizedStatus, ['pending', 'manager_verified']))
    <div id="leave-reject-modal" class="hidden fixed inset-0 z-50" onclick="if(event.target === this) closeLeaveRejectModal()">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-xl" onclick="event.stopPropagation()">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Tolak Pengajuan Cuti</h3>
                    <p class="mt-1 text-sm text-gray-500">Isi alasan penolakan agar pegawai memahami keputusan.</p>
                </div>
                <form id="reject-form" action="{{ route('approvals.leaves.reject', $leave->id) }}" method="POST" class="px-5 py-4" onsubmit="return validateLeaveRejectForm()">
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
