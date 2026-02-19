@extends('layouts.admin')

@section('title', 'Detail Perjalanan Dinas')

@section('content')
<div class="space-y-6" x-data="{ showApproveModal: false, showRejectModal: false, approvalNotes: '', rejectionReason: '' }">
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
        @if($trip->status === 'pending')
        <div class="flex gap-2">
            <button @click="showApproveModal = true" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-check mr-2"></i>Approve
            </button>
            <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i>Reject
            </button>
        </div>
        @endif
    </div>

    <!-- Status Alert -->
    @if($trip->status === 'approved')
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
                <p class="text-red-700 text-sm mt-1">
                    Ditolak oleh <strong>{{ $trip->approvedBy->name }}</strong>
                    pada {{ $trip->approved_at->format('d M Y H:i') }}
                </p>
                @endif
                @if($trip->rejection_reason)
                <p class="text-red-700 text-sm mt-2">
                    <strong>Alasan:</strong> {{ $trip->rejection_reason }}
                </p>
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
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $trip->start_date->diffInDays($trip->end_date) + 1 }} hari
                            </p>
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
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            @if($trip->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                                    <i class="fas fa-clock mr-2"></i>Pending
                                </span>
                            @elseif($trip->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 font-semibold">
                                    <i class="fas fa-check mr-2"></i>Approved
                                </span>
                            @elseif($trip->status === 'rejected')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 font-semibold">
                                    <i class="fas fa-times mr-2"></i>Rejected
                                </span>
                            @elseif($trip->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-semibold">
                                    <i class="fas fa-ban mr-2"></i>Cancelled
                                </span>
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tujuan Perjalanan</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <p class="text-gray-700 leading-relaxed">{{ $trip->purpose }}</p>
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

                            <!-- Approved/Rejected -->
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
                                                <p class="text-xs text-gray-500">{{ $trip->approvedBy->name }}</p>
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

    <!-- Approve Modal -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 backdrop-blur-sm bg-white/30" @click="showApproveModal = false"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4">Approve Permohonan</h3>
                <form method="POST" action="{{ route('approvals.business-trips.approve', $trip->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea x-model="approvalNotes" name="approval_notes" rows="3"
                                  class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showApproveModal = false"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-check mr-2"></i>Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 backdrop-blur-sm bg-white/30" @click="showRejectModal = false"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4">Reject Permohonan</h3>
                <form method="POST" action="{{ route('approvals.business-trips.reject', $trip->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="rejectionReason" name="rejection_reason" rows="4" required
                                  class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showRejectModal = false"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@endsection
