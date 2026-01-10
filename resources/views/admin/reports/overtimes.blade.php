@extends('layouts.admin')

@section('title', 'Laporan Lembur')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Laporan Lembur</h1>
            <p class="text-sm sm:text-base text-gray-600">Ringkasan permohonan lembur berdasarkan rentang tanggal dan status.</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <form method="GET" action="{{ route('reports.overtimes') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-sm text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $filters['date_to'] ?? now()->endOfMonth()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm"><i class="fas fa-filter mr-1"></i><span class="hidden sm:inline">Filter</span></button>
                <a href="{{ route('reports.overtimes.export', request()->only(['start_date','end_date','status','worker_id'])) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm"><i class="fas fa-file-excel mr-1"></i><span class="hidden sm:inline">Excel</span></a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Tanggal</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Jam</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Total Jam</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($overtimes as $ot)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4">
                        <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $ot->worker->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500 md:hidden">{{ $ot->overtime_date->format('d M') }}</div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden md:table-cell">{{ $ot->overtime_date->format('d M Y') }}</td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden lg:table-cell">{{ optional($ot->start_time)->format('H:i') ?? '-' }} - {{ optional($ot->end_time)->format('H:i') ?? '-' }}</td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden lg:table-cell">{{ $ot->total_hours }}</td>
                    <td class="px-3 sm:px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($ot->status === 'approved') bg-green-100 text-green-800
                            @elseif($ot->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($ot->status) }}
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-right text-xs sm:text-sm font-medium"><a href="#" class="text-blue-600"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center">Tidak ada data lembur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($overtimes->hasPages())
    <div class="mt-4">{{ $overtimes->links() }}</div>
    @endif
</div>
@endsection
