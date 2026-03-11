@extends('layouts.employee')

@section('title', 'Detail Kenaikan Pangkat')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('employee.promotions.index') }}"
           class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Detail Pengajuan</h1>
            <p class="text-sm text-gray-500">{{ optional($promotion->created_at)->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @php $badge = $promotion->status_badge; @endphp

    <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
        {{-- Status --}}
        <div class="p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
                <span class="mt-1 inline-block px-3 py-1 rounded-full text-sm font-semibold
                    @if($badge['variant'] === 'success') bg-green-100 text-green-700
                    @elseif($badge['variant'] === 'warning') bg-yellow-100 text-yellow-700
                    @elseif($badge['variant'] === 'danger') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-600
                    @endif">
                    {{ $badge['label'] }}
                </span>
            </div>
            @if($promotion->reviewed_at)
                <p class="text-xs text-gray-500">Direview: {{ optional($promotion->reviewed_at)->format('d/m/Y H:i') }}</p>
            @endif
        </div>

        {{-- Perbandingan --}}
        <div class="p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Perubahan yang Diajukan</p>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded">
                    <div class="flex-1 text-center">
                        <p class="text-xs text-gray-400">SEBELUMNYA</p>
                        <p class="font-semibold text-gray-700">{{ $promotion->current_rank ?? '-' }}</p>
                        @if($promotion->current_rank_level)
                            <p class="text-xs text-gray-500">{{ $promotion->current_rank_level }}</p>
                        @endif
                        <p class="text-xs text-gray-600 mt-1">Rp {{ number_format($promotion->current_base_salary, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-gray-400"><i class="fas fa-arrow-right text-xl"></i></div>
                    <div class="flex-1 text-center">
                        <p class="text-xs text-blue-400">DIAJUKAN</p>
                        <p class="font-bold text-blue-700">{{ $promotion->proposed_rank }}</p>
                        @if($promotion->proposed_rank_level)
                            <p class="text-xs text-blue-500">{{ $promotion->proposed_rank_level }}</p>
                        @endif
                        <p class="text-xs text-blue-600 mt-1 font-semibold">Rp {{ number_format($promotion->proposed_base_salary, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs text-gray-600">
                    <div><span class="text-gray-400">Jenis:</span> {{ str_replace('_', ' ', ucfirst($promotion->promotion_type)) }}</div>
                    <div><span class="text-gray-400">Tgl Efektif:</span> {{ optional($promotion->effective_date)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        @if($promotion->reason)
            <div class="p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Keterangan</p>
                <p class="text-sm text-gray-700">{{ $promotion->reason }}</p>
            </div>
        @endif

        @if($promotion->status === 'rejected' && $promotion->rejection_reason)
            <div class="p-5 bg-red-50">
                <p class="text-xs font-semibold text-red-500 uppercase mb-1">Alasan Penolakan</p>
                <p class="text-sm text-red-700">{{ $promotion->rejection_reason }}</p>
            </div>
        @endif

        @if($promotion->status === 'approved' && $promotion->reviewer)
            <div class="p-5 bg-green-50">
                <p class="text-xs font-semibold text-green-600 uppercase mb-1">Disetujui oleh</p>
                <p class="text-sm text-green-800 font-medium">{{ $promotion->reviewer->name }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
