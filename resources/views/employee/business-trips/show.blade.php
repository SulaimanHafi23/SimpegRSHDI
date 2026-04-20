@extends('layouts.employee')

@section('title', 'Detail Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Perjalanan Dinas</h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-0.5 truncate">{{ $trip->destination }}</p>
            </div>
        </div>
        {{-- Status Badge --}}
        @if($trip->status === 'pending')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Menunggu
            </span>
        @elseif($trip->status === 'approved')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-green-100 text-green-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Disetujui
            </span>
        @elseif($trip->status === 'rejected')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-red-100 text-red-800 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Ditolak
            </span>
        @elseif($trip->status === 'cancelled')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-full bg-gray-100 text-gray-700 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                Dibatalkan
            </span>
        @endif
    </div>

    {{-- Status Alert --}}
    @if($trip->status === 'approved' && $trip->approvedBy)
        <div class="flex items-start gap-3 px-4 py-3.5 bg-green-50 rounded-xl border border-green-200 mb-5">
            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-green-800">Permohonan Disetujui</p>
                <p class="text-xs sm:text-sm text-green-700 mt-0.5">
                    Oleh <strong>{{ $trip->approvedBy->name }}</strong>
                    @if($trip->approved_at) pada {{ \Carbon\Carbon::parse($trip->approved_at)->format('d M Y H:i') }} @endif
                </p>
            </div>
        </div>
    @elseif($trip->status === 'rejected')
        <div class="flex items-start gap-3 px-4 py-3.5 bg-red-50 rounded-xl border border-red-200 mb-5">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-red-800">Permohonan Ditolak</p>
                @if($trip->approvedBy)
                    <p class="text-xs sm:text-sm text-red-700 mt-0.5">
                        Oleh <strong>{{ $trip->approvedBy->name }}</strong>
                        @if($trip->approved_at) pada {{ \Carbon\Carbon::parse($trip->approved_at)->format('d M Y H:i') }} @endif
                    </p>
                @endif
                @if($trip->rejection_reason)
                    <div class="mt-2 rounded-lg border border-red-200 bg-red-100/40 px-3 py-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Alasan Penolakan</p>
                        <p class="mt-1 text-xs sm:text-sm leading-relaxed text-red-800">{{ $trip->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    @elseif($trip->status === 'cancelled')
        <div class="flex items-start gap-3 px-4 py-3.5 bg-gray-50 rounded-xl border border-gray-200 mb-5">
            <svg class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800">Permohonan Dibatalkan</p>
                <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Pengajuan ini telah dibatalkan.</p>
            </div>
        </div>
    @endif

    <div class="space-y-4 sm:space-y-5">
        {{-- Card 1: Informasi Perjalanan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Informasi Perjalanan</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tujuan</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-800">{{ $trip->destination }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Durasi</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">{{ $trip->duration_label }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal Mulai</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ $trip->start_date ? $trip->start_date->format('d F Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal Selesai</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ $trip->end_date ? $trip->end_date->format('d F Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Estimasi Biaya</p>
                    <p class="text-sm sm:text-base font-semibold text-blue-700">
                        Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Sesi</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">{{ $trip->half_day_session_label ?? 'Full Day' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Transportasi</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">{{ $trip->transportation ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Akomodasi</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">{{ $trip->accommodation ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Dokumen Pendukung</p>
                    @if($trip->supporting_document_path)
                        <a href="{{ Storage::disk('public')->url($trip->supporting_document_path) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 text-sm font-medium text-blue-700 hover:text-blue-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Lihat Lampiran
                        </a>
                    @else
                        <p class="text-sm sm:text-base font-medium text-gray-500">-</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Diajukan Pada</p>
                    <p class="text-sm sm:text-base font-medium text-gray-800">
                        {{ $trip->created_at ? $trip->created_at->format('d M Y H:i') : '-' }}
                    </p>
                    @if($trip->created_at)
                        <p class="text-xs text-gray-400">{{ $trip->created_at->diffForHumans() }}</p>
                    @endif
                </div>

                @if($trip->supporting_document_path)
                    @php
                        $supportingDocUrl = Storage::disk('public')->url($trip->supporting_document_path);
                        $supportingDocExtension = strtolower(pathinfo($trip->supporting_document_path, PATHINFO_EXTENSION));
                        $isSupportingImage = in_array($supportingDocExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
                        $isSupportingPdf = $supportingDocExtension === 'pdf';
                    @endphp
                    <div class="sm:col-span-2 pt-2 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Preview Dokumen Pendukung</p>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs sm:text-sm font-medium text-gray-700">{{ basename($trip->supporting_document_path) }}</p>
                                <a href="{{ $supportingDocUrl }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6m0 0v6m0-6L10 14"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5v14h14v-5"/></svg>
                                    Buka File
                                </a>
                            </div>

                            @if($isSupportingImage)
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                    <img src="{{ $supportingDocUrl }}" alt="Preview dokumen pendukung" class="w-full object-contain" style="height: clamp(34rem, 85vh, 72rem);">
                                </div>
                            @elseif($isSupportingPdf)
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                    <iframe src="{{ $supportingDocUrl }}#zoom=page-width" class="w-full" style="height: clamp(34rem, 85vh, 72rem);" title="Preview dokumen pendukung PDF"></iframe>
                                </div>
                            @else
                                <div class="rounded-lg border border-dashed border-gray-300 bg-white p-3 text-xs sm:text-sm text-gray-600">
                                    Jenis file ini belum bisa dipreview langsung. Silakan klik tombol Buka File.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Card 2: Tujuan / Keperluan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Tujuan / Keperluan</h2>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 sm:p-4 border border-gray-100">
                <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $trip->purpose }}</p>
            </div>
        </div>

        @if($trip->notes)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Catatan Tambahan</h2>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 sm:p-4 border border-gray-100">
                <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $trip->notes }}</p>
            </div>
        </div>
        @endif

        {{-- Cancel Button --}}
        @if($trip->status === 'pending')
            <div class="flex justify-end pt-1">
                <form action="{{ route('employee.business-trips.cancel', $trip->id) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
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
