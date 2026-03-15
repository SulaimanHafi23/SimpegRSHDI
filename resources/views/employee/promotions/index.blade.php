@extends('layouts.employee')

@section('title', 'Kenaikan Pangkat Saya')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Riwayat Kenaikan Pangkat</h1>
        <p class="text-sm text-gray-600 mt-1">Status pengajuan kenaikan pangkat Anda</p>
    </div>

    {{-- Current Rank Info --}}
    @if($worker->rank || $worker->payroll_category)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-xs font-semibold text-blue-700 uppercase mb-2">Informasi Kepangkatan Saat Ini</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Pangkat</span>
                    <p class="font-semibold text-gray-900">{{ $worker->rank ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Tingkat</span>
                    <p class="font-semibold text-gray-900">{{ $worker->rank_level ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Kategori</span>
                    <p class="font-semibold text-gray-900 uppercase">{{ $worker->payroll_category ?? '-' }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($promotions->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
            <i class="fas fa-award text-4xl text-gray-300 mb-3"></i>
            <p class="font-medium">Belum ada pengajuan kenaikan pangkat</p>
            <p class="text-sm mt-1">Data kenaikan pangkat akan ditampilkan di sini</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($promotions as $promo)
                @php $badge = $promo->status_badge; @endphp
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $promo->current_rank ?? '-' }} → {{ $promo->proposed_rank }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ str_replace('_', ' ', ucfirst($promo->promotion_type)) }}
                                &bull; Efektif: {{ optional($promo->effective_date)->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($badge['variant'] === 'success') bg-green-100 text-green-700
                            @elseif($badge['variant'] === 'warning') bg-yellow-100 text-yellow-700
                            @elseif($badge['variant'] === 'danger') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $badge['label'] }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-xs text-gray-600">
                        <div>
                            Gaji: <span class="font-medium">Rp {{ number_format($promo->current_base_salary, 0, ',', '.') }}</span>
                            → <span class="font-medium text-blue-700">Rp {{ number_format($promo->proposed_base_salary, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('employee.promotions.show', $promo->id) }}"
                               class="text-blue-600 hover:underline">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($promotions->hasPages())
            <div>{{ $promotions->links() }}</div>
        @endif
    @endif
</div>
@endsection
