@extends('layouts.admin')

@section('title', 'Manajemen Lembur')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-0">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">Manajemen Lembur</h1>
            <p class="text-gray-600 mt-1">Kelola data lembur pegawai</p>
        </div>
        <a href="{{ route('admin.overtime.create') }}" 
           class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Input Lembur
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Lembur</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statistics['total'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statistics['pending'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statistics['approved'] ?? 0 }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Jam</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statistics['total_hours'] ?? 0 }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.overtime.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                    <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Pegawai</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}" {{ $filters['worker_id'] == $worker->id ? 'selected' : '' }}>
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        <option value="Pending" {{ $filters['status'] == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ $filters['status'] == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ $filters['status'] == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ $filters['start_date'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ $filters['end_date'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan & Tahun</label>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <select name="month" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                            <option value="">Bulan</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $filters['month'] == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('M') }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                            <option value="">Tahun</option>
                            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ $filters['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.overtime.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150">
                    Reset
                </a>
                <button type="submit" 
                        class="px-3 sm:px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($overtimes as $overtime)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($overtime->worker->photo)
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ Storage::url($overtime->worker->photo) }}" 
                                             alt="{{ $overtime->worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="text-green-600 font-semibold text-lg">
                                                {{ substr($overtime->worker->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $overtime->worker->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $overtime->worker->position->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $overtime->overtime_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ date('H:i', strtotime($overtime->start_time)) }}</div>
                            <div class="text-gray-500">s/d {{ date('H:i', strtotime($overtime->end_time)) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm font-bold rounded-full bg-purple-100 text-purple-800">
                                {{ $overtime->total_hours }} Jam
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusConfig = [
                                    'Pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                    'Approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                    'Rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                ];
                                $config = $statusConfig[$overtime->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                {{ $overtime->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                                <a href="{{ route('admin.overtime.show', $overtime->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Lihat">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if($overtime->status == 'Pending')
                                <a href="{{ route('admin.overtime.edit', $overtime->id) }}" 
                                   class="text-green-600 hover:text-green-900" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.overtime.destroy', $overtime->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900" 
                                            title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus data lembur ini?')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2">Tidak ada data lembur</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>

        <!-- Pagination -->
        @if($overtimes->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $overtimes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
