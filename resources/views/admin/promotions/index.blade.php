@extends('layouts.admin')

@section('title', 'Kenaikan Pangkat / Promosi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
                <i class="fas fa-award text-white text-lg sm:text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Kenaikan Pangkat / Promosi</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola pengajuan kenaikan pangkat dan riwayat promosi pegawai</p>
            </div>
        </div>
        @can('promotion.manage')
        <a href="{{ route('admin.promotions.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>Buat Pengajuan
        </a>
        @endcan
    </div>

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
                   placeholder="Cari nama / NIP pegawai..."
                   class="flex-1 min-w-[180px] px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-sm">
                <option value="">Semua Status</option>
                <option value="pending"  @selected(($filters['status'] ?? '') === 'pending')>Menunggu</option>
                <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Disetujui</option>
                <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Ditolak</option>
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
                        <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Pegawai</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Jenis</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Pangkat Diajukan</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Gaji Diajukan</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Tgl Berlaku</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($requests as $req)
                        @php $badge = $req->status_badge; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $req->worker?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $req->worker?->nip }} &bull; {{ $req->worker?->department?->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700 capitalize">{{ str_replace('_', ' ', $req->promotion_type) }}</td>
                            <td class="px-6 py-4 text-center">
                                <p class="font-medium">{{ $req->proposed_rank }}</p>
                                @if($req->proposed_rank_level)
                                    <p class="text-xs text-gray-500">{{ $req->proposed_rank_level }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">Rp {{ number_format($req->proposed_base_salary, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center text-gray-600">{{ optional($req->effective_date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($badge['variant'] === 'success') bg-green-100 text-green-700
                                    @elseif($badge['variant'] === 'warning') bg-yellow-100 text-yellow-700
                                    @elseif($badge['variant'] === 'danger') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-600
                                    @endif">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.promotions.show', $req->id) }}"
                                   class="px-3 py-1 bg-blue-50 text-blue-700 rounded text-xs hover:bg-blue-100">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-award text-4xl text-gray-300 mb-2"></i>
                                <p>Belum ada pengajuan kenaikan pangkat</p>
                                @can('promotion.manage')
                                    <a href="{{ route('admin.promotions.create') }}"
                                       class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm">
                                        Buat pengajuan pertama
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $requests->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
