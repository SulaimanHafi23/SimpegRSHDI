@extends('layouts.employee')

@section('title', 'Detail Cuti')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee.leaves.index') }}" 
           class="mr-4 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Detail Permohonan Cuti</h1>
            <p class="text-gray-600 mt-1">{{ $leave->leaveType->name ?? '-' }}</p>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if($leave->status === 'pending')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Menunggu Persetujuan
            </span>
        @elseif($leave->status === 'approved')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Disetujui
            </span>
        @else
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Ditolak
            </span>
        @endif
    </div>

    <!-- Leave Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Cuti</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Jenis Cuti</label>
                <p class="text-lg font-medium text-gray-800">{{ $leave->leaveType->name ?? '-' }}</p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Durasi</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays($leave->end_date) + 1 }} hari
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Tanggal Mulai</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($leave->start_date)->format('d F Y') }}
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Tanggal Selesai</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($leave->end_date)->format('d F Y') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Reason -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Alasan</h2>
        <p class="text-gray-700 whitespace-pre-line">{{ $leave->reason }}</p>
    </div>

    <!-- Document -->
    @if($leave->document_path)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Dokumen Pendukung</h2>
            <a href="{{ Storage::url($leave->document_path) }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat Dokumen
            </a>
        </div>
    @endif

    <!-- Approval Info -->
    @if($leave->status !== 'pending')
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Persetujuan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600">Diproses Oleh</label>
                    <p class="text-lg font-medium text-gray-800">{{ $leave->approvedBy->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Tanggal Diproses</label>
                    <p class="text-lg font-medium text-gray-800">
                        {{ $leave->approved_at ? \Carbon\Carbon::parse($leave->approved_at)->format('d F Y H:i') : '-' }}
                    </p>
                </div>
            </div>
            @if($leave->notes)
                <div class="mt-4">
                    <label class="text-sm text-gray-600">Catatan</label>
                    <p class="text-gray-700 mt-1">{{ $leave->notes }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Cancel Button -->
    @if($leave->status === 'pending')
        <div class="flex justify-end">
            <form action="{{ route('employee.leaves.cancel', $leave->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                        onclick="return confirm('Yakin ingin membatalkan permohonan cuti ini?')">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batalkan Permohonan
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
