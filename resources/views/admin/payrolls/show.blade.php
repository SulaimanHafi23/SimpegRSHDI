@extends('layouts.admin')

@section('title', 'Detail Periode Penggajian')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <i class="fas fa-receipt text-white text-lg sm:text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $period->name }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ $period->month_name }} &bull; {{ number_format($payrolls->total()) }} slip</p>
            </div>
        </div>

        @php $badge = $period->status_badge; @endphp
        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.payrolls.index') }}"
               class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left text-xs"></i>
                <span class="hidden sm:inline">Kembali</span>
            </a>
            <span class="px-3 py-1 rounded-full text-sm font-medium
                @if($badge['variant'] === 'success') bg-green-100 text-green-700
                @elseif($badge['variant'] === 'warning') bg-yellow-100 text-yellow-700
                @elseif($badge['variant'] === 'info') bg-blue-100 text-blue-700
                @else bg-gray-100 text-gray-700
                @endif">
                {{ $badge['label'] }}
            </span>
            @can('payroll.manage')
                @if($period->status === 'finalized')
                    <form method="POST" action="{{ route('admin.payrolls.mark-paid', $period->id) }}"
                          onsubmit="return confirm('Tandai semua slip sebagai SUDAH DIBAYAR?')">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold">
                            <i class="fas fa-money-bill-wave mr-2"></i>Tandai Lunas
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl">
            <p class="text-green-700 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $totalNet = $payrolls->sum('net_salary');
            $totalEarnings = $payrolls->sum('total_earnings');
            $totalDeductions = $payrolls->sum('total_deductions');
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total Slip</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($payrolls->total()) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total Tunjangan</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total Potongan</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($totalDeductions, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total Gaji Bersih</p>
            <p class="text-lg font-bold text-blue-600">Rp {{ number_format($totalNet, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Cari nama / NIP..."
                   class="flex-1 min-w-[160px] px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm">
                <option value="">Semua Status</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                <option value="paid"  @selected(($filters['status'] ?? '') === 'paid')>Dibayar</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">
                <i class="fas fa-search mr-1"></i>Filter
            </button>
        </form>
    </div>

    {{-- Payroll Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Pegawai</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Gaji Pokok</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Tunjangan</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Potongan</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Gaji Bersih</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payrolls as $payroll)
                        @php $sl = $payroll->status_badge; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $payroll->worker?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $payroll->worker?->nip }} &bull; {{ $payroll->worker?->department?->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-800">
                                Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-green-700">
                                + Rp {{ number_format($payroll->total_earnings, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-red-600">
                                - Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-blue-700">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($sl['variant'] === 'success') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-600
                                    @endif">{{ $sl['label'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.payrolls.slip-pdf', $payroll->id) }}"
                                   target="_blank"
                                   class="px-3 py-1 bg-gray-50 text-gray-700 border rounded text-xs hover:bg-gray-100">
                                    <i class="fas fa-file-pdf mr-1 text-red-500"></i>Slip PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                                <p>Tidak ada slip ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payrolls->hasPages())
            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $payrolls->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
