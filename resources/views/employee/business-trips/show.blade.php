@extends('layouts.employee')

@section('title', 'Detail Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="{{ route('employee.business-trips.index') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-briefcase mr-2"></i>Perjalanan Dinas
                </a>
            </li>
            <li>
                <span class="mx-2 text-gray-400">/</span>
            </li>
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
        <form action="{{ route('employee.business-trips.cancel', $trip->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
            @csrf
            @method('DELETE')
            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-ban mr-2"></i>Batalkan
            </button>
        </form>
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
                <p class="text-gray-700 text-sm mt-1">Pengajuan ini telah dibatalkan.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Details Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Informasi Perjalanan Dinas</h3>
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

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Diajukan Pada</label>
                    <p class="text-base text-gray-900">
                        {{ $trip->created_at->format('d M Y H:i') }}
                    </p>
                    <p class="text-sm text-gray-500">{{ $trip->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection