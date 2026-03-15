@extends('layouts.admin')

@section('title', 'Detail Pengajuan Kenaikan Pangkat')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3 sm:gap-4">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <i class="fas fa-file-signature text-white text-lg sm:text-xl"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Detail Pengajuan Kenaikan Pangkat</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $request->worker?->name }} &bull; {{ optional($request->created_at)->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.promotions.index') }}"
           class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shrink-0">
            <i class="fas fa-arrow-left text-xs"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl"><p class="text-green-700 text-sm">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl"><p class="text-red-700 text-sm">{{ session('error') }}</p></div>
    @endif

    @php $badge = $request->status_badge; @endphp

    {{-- Main Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        {{-- Status Header --}}
        <div class="p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Status Pengajuan</p>
                <span class="mt-1 inline-block px-3 py-1 rounded-full text-sm font-semibold
                    @if($badge['variant'] === 'success') bg-green-100 text-green-700
                    @elseif($badge['variant'] === 'warning') bg-yellow-100 text-yellow-700
                    @elseif($badge['variant'] === 'danger') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-600
                    @endif">
                    {{ $badge['label'] }}
                </span>
            </div>
            <div class="text-right text-sm text-gray-500">
                <p>Dibuat: {{ optional($request->created_at)->format('d/m/Y H:i') }}</p>
                @if($request->reviewed_at)
                    <p>Direview: {{ optional($request->reviewed_at)->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>

        {{-- Comparison Table --}}
        <div class="p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Perbandingan Data</p>
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 text-left text-gray-500 font-medium">Komponen</th>
                            <th class="px-4 py-2 text-center text-gray-500 font-medium">Saat Ini</th>
                            <th class="px-4 py-2 text-center text-gray-500 font-medium">Diajukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 text-gray-600">Pangkat / Golongan</td>
                            <td class="px-4 py-3 text-center">{{ $request->current_rank ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-blue-700">{{ $request->proposed_rank }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-gray-600">Tingkat</td>
                            <td class="px-4 py-3 text-center">{{ $request->current_rank_level ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-blue-700">{{ $request->proposed_rank_level ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-gray-600">Gaji Pokok</td>
                            <td class="px-4 py-3 text-center">Rp {{ number_format($request->current_base_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-blue-700">
                                Rp {{ number_format($request->proposed_base_salary, 0, ',', '.') }}
                                @if($request->salary_diff > 0)
                                    <span class="text-green-600 text-xs ml-1">+Rp {{ number_format($request->salary_diff, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-gray-600">Tanggal Efektif</td>
                            <td class="px-4 py-3 text-center">-</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ optional($request->effective_date)->format('d/m/Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($request->reason)
            <div class="p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Alasan</p>
                <p class="text-sm text-gray-700">{{ $request->reason }}</p>
            </div>
        @endif

        @if($request->rejection_reason)
            <div class="p-5 bg-red-50 rounded-b-2xl">
                <p class="text-xs font-semibold text-red-500 uppercase mb-1">Alasan Penolakan</p>
                <p class="text-sm text-red-700">{{ $request->rejection_reason }}</p>
            </div>
        @endif
    </div>

    {{-- Approval Actions --}}
    @can('promotion.manage')
        @if($request->status === 'pending')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                <p class="font-semibold text-gray-800">Tindakan Persetujuan</p>

                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.promotions.approve', $request->id) }}"
                      onsubmit="return confirm('Setujui pengajuan ini? Data pegawai akan otomatis diperbarui.')">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text" name="notes" placeholder="Catatan (opsional)"
                               class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-green-500">
                        <button type="submit"
                                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold">
                            <i class="fas fa-check mr-2"></i>Setujui
                        </button>
                    </div>
                </form>

                {{-- Reject --}}
                <form method="POST" action="{{ route('admin.promotions.reject', $request->id) }}"
                      onsubmit="return confirm('Tolak pengajuan ini?')">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text" name="rejection_reason" placeholder="Alasan penolakan (wajib)"
                               required
                               class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-red-500">
                        <button type="submit"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endcan

    {{-- Promotion History --}}
    @if($histories->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b">
                <p class="font-semibold text-gray-800">Riwayat Kenaikan Pangkat Pegawai</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($histories as $hist)
                    <div class="px-5 py-3 text-sm">
                        <div class="flex justify-between">
                            <div>
                                <span class="font-medium">{{ $hist->old_rank ?? '-' }} → {{ $hist->new_rank }}</span>
                                @if($hist->old_rank_level || $hist->new_rank_level)
                                    <span class="text-gray-500 ml-2">({{ $hist->old_rank_level ?? '-' }} → {{ $hist->new_rank_level ?? '-' }})</span>
                                @endif
                            </div>
                            <span class="text-gray-500 text-xs">{{ optional($hist->effective_date)->format('d/m/Y') }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Gaji: Rp {{ number_format($hist->old_base_salary, 0, ',', '.') }} → Rp {{ number_format($hist->new_base_salary, 0, ',', '.') }}
                            @if($hist->approvedBy)
                                &bull; Disetujui oleh: {{ $hist->approvedBy->name }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
