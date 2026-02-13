@extends('layouts.employee')

@section('title', 'Detail Lembur')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee.overtimes.index') }}"
           class="mr-4 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Detail Permohonan Lembur</h1>
            <p class="text-gray-600 mt-1">{{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if($overtime->status === 'pending')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Menunggu Persetujuan
            </span>
        @elseif($overtime->status === 'approved')
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

    <!-- Overtime Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Lembur</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm text-gray-600">Tanggal</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d F Y') }}
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Durasi</label>
                <p class="text-lg font-medium text-gray-800">
                    @php
                        $start = \Carbon\Carbon::parse($overtime->start_time);
                        $end = \Carbon\Carbon::parse($overtime->end_time);
                        $diff = $start->diff($end);
                    @endphp
                    {{ $diff->h }} jam {{ $diff->i }} menit
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Waktu Mulai</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Waktu Selesai</label>
                <p class="text-lg font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                </p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Shift (sesuai tukar)</label>
                @if($overtime->actual_shift)
                    <p class="text-lg font-medium text-gray-800">
                        {{ $overtime->actual_shift->name }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($overtime->actual_shift->start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($overtime->actual_shift->end_time)->format('H:i') }}
                    </p>
                @else
                    <p class="text-lg font-medium text-gray-400">-</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Reason -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Alasan</h2>
        <p class="text-gray-700 whitespace-pre-line">{{ $overtime->reason }}</p>
    </div>

    <!-- Approval Info -->
    @if($overtime->status !== 'pending')
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Persetujuan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600">Diproses Oleh</label>
                    <p class="text-lg font-medium text-gray-800">{{ $overtime->approvedBy->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Tanggal Diproses</label>
                    <p class="text-lg font-medium text-gray-800">
                        {{ $overtime->approved_at ? \Carbon\Carbon::parse($overtime->approved_at)->format('d F Y H:i') : '-' }}
                    </p>
                </div>
            </div>
            @if($overtime->notes)
                <div class="mt-4">
                    <label class="text-sm text-gray-600">Catatan</label>
                    <p class="text-gray-700 mt-1">{{ $overtime->notes }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Cancel Button -->
    @if($overtime->status === 'pending')
        <div class="flex justify-end">
            <form action="{{ route('employee.overtimes.cancel', $overtime->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                        onclick="return confirm('Yakin ingin membatalkan permohonan lembur ini?')">
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
