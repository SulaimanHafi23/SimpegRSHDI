@extends('layouts.admin')

@section('title', 'Detail Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="{{ route('approvals.business-trips.index') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-list mr-2"></i>Daftar Perjalanan Dinas
                </a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-500">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Perjalanan Dinas</h1>
            <p class="text-gray-600">{{ $trip->destination }}</p>
        </div>

        @php
            $user = Auth::user();
            $isAdmin = $user->hasRole(['admin', 'Super Admin', 'super admin', 'superadmin']);
            $isHR = $user->hasRole(['hr', 'HR']) || $isAdmin;
            $isManager = $user->hasRole(['manager', 'Manager']) || $isAdmin;
        @endphp

        @if($trip->status === 'pending' && $isManager)
            <div class="flex gap-2">
                <form action="{{ route('approvals.business-trips.verify', $trip->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            onclick="return confirm('Anda yakin ingin memverifikasi perjalanan dinas ini? Pengajuan akan diteruskan ke HR.')">
                        <i class="fas fa-check-double mr-2"></i>Verifikasi
                    </button>
                </form>
                <button type="button" onclick="confirmBusinessTripReject('manager')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-times mr-2"></i>Tolak
                </button>
            </div>
        @endif

        @if($trip->status === 'manager_verified' && $isHR)
            <!-- HR: Approve & Reject Buttons -->
            <div class="flex gap-2">
                <button type="button" onclick="confirmBusinessTripApprove()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i>Setujui
                </button>
                <button type="button" onclick="confirmBusinessTripReject('hr')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-times mr-2"></i>Tolak
                </button>
            </div>
        @endif
    </div>

    <!-- Status Alert -->
    @if($trip->status === 'pending')
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-clock text-yellow-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-yellow-800 font-semibold">Menunggu Verifikasi Manager</h3>
                <p class="text-yellow-700 text-sm mt-1">
                    Pengajuan ini menunggu verifikasi dari manager departemen. Setelah diverifikasi, akan diteruskan ke HR untuk persetujuan final.
                </p>
            </div>
        </div>
    </div>
    @elseif($trip->status === 'manager_verified')
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-check-circle text-blue-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-blue-800 font-semibold">Telah Diverifikasi Manager</h3>
                <p class="text-blue-700 text-sm mt-1">
                    Pengajuan ini telah diverifikasi oleh manager dan sekarang menunggu persetujuan akhir dari HR.
                </p>
            </div>
        </div>
    </div>
    @elseif($trip->status === 'approved')
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-check-circle text-green-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-green-800 font-semibold">Permohonan Disetujui</h3>
                @if($trip->approvedBy)
                <p class="text-green-700 text-sm mt-1">
                    Disetujui oleh <strong>{{ $trip->approvedBy->name }}</strong>
                    pada {{ $trip->approved_at->format('d M Y H:i') }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @elseif($trip->status === 'rejected')
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-times-circle text-red-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-red-800 font-semibold">Permohonan Ditolak</h3>
                @if($trip->approvedBy)
                @php
                    $rejectedByLabel = $trip->approvedBy->hasRole('manager') && !$trip->approvedBy->hasRole(['admin', 'hr']) ? 'Manager' : 'HR';
                @endphp
                <p class="text-red-700 text-sm mt-1">
                    Ditolak oleh <strong>{{ $trip->approvedBy->name }}</strong> sebagai <strong>{{ $rejectedByLabel }}</strong>
                    pada {{ $trip->approved_at->format('d M Y H:i') }}
                </p>
                @endif
                @if($trip->rejection_reason)
                <div class="mt-3 rounded-md border border-red-200 bg-red-100/60 px-3 py-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Alasan Penolakan</p>
                    <p class="mt-1 text-sm leading-relaxed text-red-800">{{ $trip->rejection_reason }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @elseif($trip->status === 'cancelled')
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-ban text-gray-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-gray-800 font-semibold">Permohonan Dibatalkan</h3>
                <p class="text-gray-700 text-sm mt-1">Pengajuan ini telah dibatalkan oleh karyawan.</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Worker Info Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Karyawan</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
                            {{ strtoupper(substr($trip->worker->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $trip->worker->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $trip->worker->department->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ $trip->worker->department->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trip Details Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Perjalanan Dinas</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tujuan</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $trip->destination }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Durasi</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $trip->duration_label }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                            <p class="text-base font-semibold text-gray-900">
                                <i class="fas fa-calendar text-blue-500 mr-2"></i>
                                {{ $trip->start_date->format('d F Y') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Selesai</label>
                            <p class="text-base font-semibold text-gray-900">
                                <i class="fas fa-calendar text-blue-500 mr-2"></i>
                                {{ $trip->end_date->format('d F Y') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estimasi Biaya</label>
                            <p class="text-lg font-semibold text-gray-900">
                                Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Sesi</label>
                            <p class="text-base font-semibold text-gray-900">{{ $trip->half_day_session_label ?? 'Sepanjang Hari' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            @if($trip->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                                    <i class="fas fa-clock mr-2"></i>Menunggu
                                </span>
                            @elseif($trip->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 font-semibold">
                                    <i class="fas fa-check mr-2"></i>Disetujui
                                </span>
                            @elseif($trip->status === 'rejected')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 font-semibold">
                                    <i class="fas fa-times mr-2"></i>Ditolak
                                </span>
                            @elseif($trip->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-semibold">
                                    <i class="fas fa-ban mr-2"></i>Dibatalkan
                                </span>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Transportasi</label>
                            <p class="text-base font-semibold text-gray-900">{{ $trip->transportation ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Akomodasi</label>
                            <p class="text-base font-semibold text-gray-900">{{ $trip->accommodation ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dokumen Pendukung</label>
                            @if($trip->supporting_document_path)
                                <a href="{{ Storage::disk('public')->url($trip->supporting_document_path) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center text-blue-700 hover:text-blue-800 font-semibold">
                                    <i class="fas fa-paperclip mr-2"></i>Lihat Lampiran
                                </a>
                            @else
                                <p class="text-base font-semibold text-red-600">Tidak ada lampiran</p>
                            @endif
                        </div>

                        @if($trip->supporting_document_path)
                        @php
                            $supportingDocUrl = Storage::disk('public')->url($trip->supporting_document_path);
                            $supportingDocExtension = strtolower(pathinfo($trip->supporting_document_path, PATHINFO_EXTENSION));
                            $isSupportingImage = in_array($supportingDocExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                            $isSupportingPdf = $supportingDocExtension === 'pdf';
                        @endphp
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Preview Dokumen Pendukung</label>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-700">{{ basename($trip->supporting_document_path) }}</p>
                                    <a href="{{ $supportingDocUrl }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                                        <i class="fas fa-external-link-alt mr-2"></i>Buka File
                                    </a>
                                </div>

                                @if($isSupportingImage)
                                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <img src="{{ $supportingDocUrl }}" alt="Preview dokumen pendukung" class="w-full object-contain" style="height: clamp(40rem, 92vh, 88rem);">
                                    </div>
                                @elseif($isSupportingPdf)
                                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <iframe src="{{ $supportingDocUrl }}#zoom=100" class="w-full" style="height: clamp(40rem, 92vh, 88rem);" title="Preview dokumen pendukung PDF"></iframe>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-3 text-sm text-gray-600">
                                        Jenis file ini belum bisa dipreview langsung. Silakan klik tombol Buka File.
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tujuan Perjalanan</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <p class="text-gray-700 leading-relaxed">{{ $trip->purpose }}</p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <p class="text-gray-700 leading-relaxed">{{ $trip->notes ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <!-- Submitted -->
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-plus text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900 font-medium">Pengajuan Dibuat</p>
                                                <p class="text-xs text-gray-500">{{ $trip->worker->name }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <p>{{ $trip->created_at->format('d M Y') }}</p>
                                                <p>{{ $trip->created_at->format('H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Verified by Manager -->
                            @if($trip->manager_verified_at || $trip->status === 'manager_verified' || in_array($trip->status, ['approved', 'rejected']))
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ ($trip->manager_verified_at || in_array($trip->status, ['approved', 'rejected'])) ? 'bg-blue-400' : 'bg-gray-300' }} flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-check-double text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900 font-medium">Telah Diverifikasi</p>
                                                <p class="text-xs text-gray-500">{{ $trip->manager->name ?? 'Manager' }}<br> Sebagai Manager</p>
                                            </div>
                                            @if($trip->manager_verified_at)
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <p>{{ $trip->manager_verified_at->format('d M Y') }}</p>
                                                <p>{{ $trip->manager_verified_at->format('H:i') }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if(in_array($trip->status, ['approved', 'rejected']))
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $trip->status === 'approved' ? 'bg-green-500' : 'bg-red-500' }} flex items-center justify-center ring-8 ring-white">
                                                <i class="fas {{ $trip->status === 'approved' ? 'fa-check' : 'fa-times' }} text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900 font-medium">
                                                    {{ $trip->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                                </p>
                                                @if($trip->approvedBy)
                                                @php
                                                    $tripRejectActorLabel = $trip->approvedBy->hasRole('manager') && !$trip->approvedBy->hasRole(['admin', 'hr']) ? 'Manager' : 'HR';
                                                @endphp
                                                <p class="text-xs text-gray-500">{{ $trip->approvedBy->name }}<br> Sebagai {{ $tripRejectActorLabel }}</p>
                                                @endif
                                            </div>
                                            @if($trip->approved_at)
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <p>{{ $trip->approved_at->format('d M Y') }}</p>
                                                <p>{{ $trip->approved_at->format('H:i') }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif

                            <!-- Cancelled -->
                            @if($trip->status === 'cancelled')
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-ban text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900 font-medium">Dibatalkan</p>
                                                <p class="text-xs text-gray-500">{{ $trip->worker->name }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <p>{{ $trip->updated_at->format('d M Y') }}</p>
                                                <p>{{ $trip->updated_at->format('H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="approve-business-trip-form" method="POST" action="{{ route('approvals.business-trips.approve', $trip->id) }}" class="hidden">
        @csrf
    </form>

    <form id="reject-business-trip-form" method="POST" action="{{ route('approvals.business-trips.reject', $trip->id) }}" class="hidden">
        @csrf
        <input type="hidden" name="rejection_reason" id="reject-business-trip-reason">
    </form>
</div>

@push('scripts')
<script>
    function confirmBusinessTripApprove() {
        const form = document.getElementById('approve-business-trip-form');
        if (!form) {
            return;
        }

        if (window.Swal) {
            window.Swal.fire({
                title: 'Setujui perjalanan dinas?',
                text: 'Permohonan akan diproses sebagai disetujui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, setujui',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return;
        }

        if (window.confirm('Setujui perjalanan dinas ini?')) {
            form.submit();
        }
    }

    function confirmBusinessTripReject(stage = 'hr') {
        const form = document.getElementById('reject-business-trip-form');
        const reasonInput = document.getElementById('reject-business-trip-reason');
        const actorLabel = stage === 'manager' ? 'Manager' : 'HR';

        if (!form || !reasonInput) {
            return;
        }

        if (window.Swal) {
            window.Swal.fire({
                title: `Tolak perjalanan dinas oleh ${actorLabel}?`,
                text: stage === 'manager' ? 'Alasan penolakan dari manager wajib diisi.' : 'Alasan penolakan wajib diisi.',
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'Tulis alasan penolakan...',
                inputAttributes: {
                    'aria-label': 'Alasan penolakan',
                },
                showCancelButton: true,
                confirmButtonText: `Ya, tolak oleh ${actorLabel}`,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'Alasan penolakan wajib diisi.';
                    }
                    return null;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    reasonInput.value = (result.value || '').trim();
                    form.submit();
                }
            });
            return;
        }

        const reason = window.prompt(`Masukkan alasan penolakan oleh ${actorLabel}:`);
        if (reason && reason.trim()) {
            reasonInput.value = reason.trim();
            form.submit();
        }
    }
</script>
@endpush

@endsection
