@extends('layouts.workers')

@section('title', 'Cuti Saya')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Cuti Saya</h1>
            <p class="mt-2 text-sm text-gray-600">Kelola pengajuan cuti dan lihat riwayat cuti Anda</p>
        </div>
        <a href="{{ route('workers.leaves.create') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>Ajukan Cuti
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Sisa Jatah Cuti</p>
                    <p class="text-3xl font-bold text-green-600">{{ $leaveBalance ?? 12 }}</p>
                    <p class="text-xs text-gray-500 mt-1">dari 12 hari/tahun</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Cuti Digunakan</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $usedLeaves ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">hari tahun ini</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-umbrella-beach text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Menunggu Persetujuan</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $pendingLeaves ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">pengajuan</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('workers.leaves.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan jenis cuti..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="w-48">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('workers.leaves.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a>
        </form>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jenis Cuti
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Durasi
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($leaves ?? [] as $leave)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt text-gray-400 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $leave->leave_type }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($leave->reason, 30) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-gray-500">Diajukan: {{ $leave->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $leave->total_days }} hari
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($leave->status === 'pending')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Menunggu
                            </span>
                        @elseif($leave->status === 'approved')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Disetujui
                            </span>
                        @elseif($leave->status === 'rejected')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>Ditolak
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('workers.leaves.show', $leave->id) }}" 
                           class="text-green-600 hover:text-green-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($leave->status === 'pending')
                        <a href="{{ route('workers.leaves.edit', $leave->id) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('workers.leaves.destroy', $leave->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin membatalkan pengajuan cuti ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>Belum ada pengajuan cuti</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($leaves) && $leaves->hasPages())
    <div class="mt-4">
        {{ $leaves->links() }}
    </div>
    @endif
</div>
@endsection
