@extends('layouts.workers')

@section('title', 'Lembur Saya')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Lembur Saya</h1>
            <p class="mt-2 text-sm text-gray-600">Kelola pengajuan lembur Anda</p>
        </div>
        <a href="{{ route('workers.overtimes.create') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>Ajukan Lembur
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Jam Lembur Bulan Ini</p>
                    <p class="text-3xl font-bold text-green-600">{{ $totalHours ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">jam kerja</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-clock text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Menunggu Persetujuan</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $pendingOvertimes ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">pengajuan</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-hourglass-half text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtimes Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Jam</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($overtimes ?? [] as $overtime)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-gray-500">{{ Str::limit($overtime->description, 30) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $overtime->start_time }} - {{ $overtime->end_time }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $overtime->total_hours }} jam
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($overtime->status === 'pending')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Menunggu
                            </span>
                        @elseif($overtime->status === 'approved')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Disetujui
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>Ditolak
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('workers.overtimes.show', $overtime->id) }}" 
                           class="text-green-600 hover:text-green-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($overtime->status === 'pending')
                        <a href="{{ route('workers.overtimes.edit', $overtime->id) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('workers.overtimes.destroy', $overtime->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin membatalkan?')">
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
                        <p>Belum ada pengajuan lembur</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    @if(isset($overtimes) && $overtimes->hasPages())
    <div class="mt-4">
        {{ $overtimes->links() }}
    </div>
    @endif
</div>
@endsection
