@extends('layouts.employee')

@section('title', 'Detail Lembur')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Permohonan Lembur</h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-0.5">{{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d F Y') }}</p>
            </div>
        </div>
        {{-- Status Badge --}}
        @if($overtime->status === 'pending')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Menunggu
            </span>
        @elseif($overtime->status === 'approved')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-green-100 text-green-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Disetujui
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-red-100 text-red-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Ditolak
            </span>
        @endif
    </div>

    <div class="space-y-4 sm:space-y-5">
        {{-- Card 1: Informasi Lembur --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Informasi Lembur</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d F Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Durasi</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">{{ $overtime->total_hours }} jam</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Waktu Mulai</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Waktu Selesai</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                    </p>
                </div>
                @if($overtime->actual_shift)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500 mb-1">Shift</p>
                        <p class="text-sm sm:text-base font-medium text-gray-800">
                            {{ $overtime->actual_shift->name }}
                            <span class="text-gray-500 font-normal">
                                ({{ \Carbon\Carbon::parse($overtime->actual_shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($overtime->actual_shift->end_time)->format('H:i') }})
                            </span>
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 2: Alasan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Alasan</h2>
            </div>
            <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $overtime->reason }}</p>
        </div>

        {{-- Card 3: Informasi Persetujuan --}}
        @if($overtime->status !== 'pending')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800">Informasi Persetujuan</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Diproses Oleh</p>
                        <p class="text-sm sm:text-base font-medium text-gray-800">{{ $overtime->approvedBy->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Tanggal Diproses</p>
                        <p class="text-sm sm:text-base font-medium text-gray-800">
                            {{ $overtime->approved_at ? \Carbon\Carbon::parse($overtime->approved_at)->format('d F Y H:i') : '-' }}
                        </p>
                    </div>
                </div>
                @if($overtime->notes)
                    <div class="mt-4 px-3 sm:px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Catatan</p>
                        <p class="text-sm text-gray-700">{{ $overtime->notes }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Cancel Button --}}
        @if($overtime->status === 'pending')
            <div class="flex justify-end pt-1">
                <form action="{{ route('employee.overtimes.cancel', $overtime->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]"
                            onclick="return confirm('Yakin ingin membatalkan permohonan lembur ini?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batalkan Permohonan
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
