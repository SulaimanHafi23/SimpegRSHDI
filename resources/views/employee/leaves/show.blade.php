@extends('layouts.employee')

@section('title', 'Detail Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Permohonan Cuti</h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-0.5 truncate">{{ $leave->leaveType->name ?? '-' }}</p>
            </div>
        </div>
        {{-- Status Badge --}}
        @if($leave->status === 'pending')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Menunggu
            </span>
        @elseif($leave->status === 'approved')
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
        {{-- Card 1: Informasi Cuti --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Informasi Cuti</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Jenis Cuti</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">{{ $leave->leaveType->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Durasi</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays($leave->end_date) + 1 }} hari
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal Mulai</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d F Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal Selesai</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d F Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Card 2: Alasan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Alasan</h2>
            </div>
            <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $leave->reason }}</p>
        </div>

        {{-- Card 3: Dokumen Pendukung --}}
        @if($leave->attachment_path)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    </div>
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800">Dokumen Pendukung</h2>
                </div>
                <a href="{{ Storage::url($leave->attachment_path) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-medium rounded-xl transition border border-emerald-200">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Lihat Dokumen
                </a>
            </div>
        @endif

        {{-- Card 4: Informasi Persetujuan --}}
        @if($leave->status !== 'pending')
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
                        <p class="text-sm sm:text-base font-medium text-gray-800">{{ $leave->approver->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Tanggal Diproses</p>
                        <p class="text-sm sm:text-base font-medium text-gray-800">
                            {{ $leave->approved_at ? \Carbon\Carbon::parse($leave->approved_at)->format('d F Y H:i') : '-' }}
                        </p>
                    </div>
                </div>
                @if($leave->rejection_reason)
                    <div class="mt-4 px-3 sm:px-4 py-3 bg-red-50 rounded-xl border border-red-200">
                        <p class="text-xs text-red-500 mb-1">Alasan Penolakan</p>
                        <p class="text-sm text-red-700">{{ $leave->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Cancel Button --}}
        @if($leave->status === 'pending')
            <div class="flex justify-end pt-1">
                <form action="{{ route('employee.leaves.cancel', $leave->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]"
                            onclick="return confirm('Yakin ingin membatalkan permohonan cuti ini?')">
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
