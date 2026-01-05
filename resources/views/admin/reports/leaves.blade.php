@extends('layouts.admin')

@section('title', 'Laporan Cuti')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Laporan Cuti</h1>
        <p class="text-gray-600">Ringkasan permohonan cuti berdasarkan rentang tanggal dan status.</p>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('reports.leaves') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <div class="flex items-end justify-end gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filter</button>
                <a href="{{ route('reports.leaves.export', request()->only(['start_date','end_date','status','worker_id'])) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Export Excel</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hari</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($leaves as $leave)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->worker->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->leaveType->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->total_days }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($leave->status) }}</td>
                    <td class="px-6 py-4 text-right text-sm font-medium"><a href="#" class="text-blue-600">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center">Tidak ada data cuti.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($leaves->hasPages())
    <div class="mt-4">{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
