@extends('layouts.admin')

@section('title', 'Approval Lembur')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Approval Lembur</h1>
            <p class="text-gray-600 mt-1">Kelola pengajuan lembur pegawai</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('approvals.overtimes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau NIP..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('approvals.overtimes.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pengajuan</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalOvertimes }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $pendingCount }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $approvedCount }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $rejectedCount }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diajukan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($overtimes as $overtime)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold">
                                            {{ strtoupper(substr($overtime->worker->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $overtime->worker->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $overtime->worker->nip }}</div>
                                    @if($overtime->worker->department)
                                    <div class="text-xs text-gray-400">{{ $overtime->worker->department->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($overtime->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $overtime->total_hours }}</span>
                            <span class="text-xs text-gray-500">jam</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($overtime->status === 'pending')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            @elseif($overtime->status === 'approved')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Approved
                                </span>
                            @elseif($overtime->status === 'rejected')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Rejected
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $overtime->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('approvals.overtimes.show', $overtime->id) }}" 
                               class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-eye mr-2"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Tidak ada pengajuan lembur</p>
                                <p class="text-gray-400 text-sm">Belum ada data yang sesuai dengan filter</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($overtimes->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            {{ $overtimes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
