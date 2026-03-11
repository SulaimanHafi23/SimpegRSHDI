@extends('layouts.admin')

@section('title', 'Manajemen Penggajian')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <i class="fas fa-file-invoice-dollar text-white text-lg sm:text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Manajemen Penggajian</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola periode dan slip gaji pegawai</p>
            </div>
        </div>
        @can('payroll.manage')
        <a href="{{ route('admin.payrolls.generate') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fas fa-calculator mr-2"></i>Generate Gaji
        </a>
        @endcan
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl">
            <p class="text-green-700 text-sm">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
            <p class="text-red-700 text-sm">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Cari nama periode..."
                   class="flex-1 min-w-[160px] px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
            <select name="year" class="px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tahun</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                @endforeach
            </select>
            <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="draft"      @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                <option value="processing" @selected(($filters['status'] ?? '') === 'processing')>Proses</option>
                <option value="finalized"  @selected(($filters['status'] ?? '') === 'finalized')>Final</option>
                <option value="paid"       @selected(($filters['status'] ?? '') === 'paid')>Dibayar</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">
                <i class="fas fa-search mr-1"></i>Filter
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Total Slip</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($periods as $period)
                        @php $badge = $period->status_badge; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $period->name }}</p>
                                <p class="text-xs text-gray-500">{{ $period->month_name }}</p>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600">
                                {{ optional($period->start_date)->format('d/m/Y') }}
                                &ndash;
                                {{ optional($period->end_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-gray-800">
                                {{ number_format($period->payrolls_count) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($badge['variant'] === 'success') bg-green-100 text-green-700
                                    @elseif($badge['variant'] === 'warning') bg-yellow-100 text-yellow-700
                                    @elseif($badge['variant'] === 'info') bg-blue-100 text-blue-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.payrolls.show', $period->id) }}"
                                       class="px-3 py-1 bg-blue-50 text-blue-700 rounded text-xs hover:bg-blue-100">
                                        <i class="fas fa-eye mr-1"></i>Lihat
                                    </a>
                                    @can('payroll.manage')
                                        @if($period->status === 'finalized')
                                            <form method="POST" action="{{ route('admin.payrolls.mark-paid', $period->id) }}"
                                                  onsubmit="return confirm('Tandai periode ini sebagai SUDAH DIBAYAR? Notifikasi akan dikirim ke semua pegawai.')">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-1 bg-green-50 text-green-700 rounded text-xs hover:bg-green-100">
                                                    <i class="fas fa-check mr-1"></i>Bayar
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-file-invoice-dollar text-4xl text-gray-300 mb-3"></i>
                                <p>Belum ada periode penggajian</p>
                                @can('payroll.manage')
                                    <a href="{{ route('admin.payrolls.generate') }}"
                                       class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm">
                                        Generate penggajian pertama
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $periods->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
