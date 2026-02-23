@extends('layouts.admin')

@section('title', 'Detail Jadwal Shift')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.worker-shifts.index') }}" class="hover:text-green-600">Jadwal Shift</a>
            <span>/</span>
            <span class="text-gray-800">Detail Jadwal</span>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Detail Jadwal Shift</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap jadwal shift pegawai</p>
            </div>
            <div class="flex gap-2">
                <a href="#off-day-management"
                   class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Kelola Libur
                </a>
                <a href="{{ route('admin.worker-shifts.edit', $workerShift->id) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <form action="{{ route('admin.worker-shifts.destroy', $workerShift->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Yakin ingin menghapus jadwal shift ini?')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="font-bold">Terdapat masalah!</strong>
                    <ul class="mt-2 ml-4 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Detail Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Informasi Jadwal Shift</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pegawai -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Pegawai</label>
                    <div class="flex items-center">
                        @if($workerShift->worker->photo_url)
                            <img src="{{ Storage::url($workerShift->worker->photo_url) }}"
                                 alt="{{ $workerShift->worker->name }}"
                                 class="w-12 h-12 rounded-full object-cover mr-3">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                <span class="text-gray-600 font-semibold text-lg">
                                    {{ substr($workerShift->worker->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <p class="text-gray-900 font-medium">{{ $workerShift->worker->name }}</p>
                            <p class="text-sm text-gray-500">{{ $workerShift->worker->nip }}</p>
                        </div>
                    </div>
                </div>

                <!-- Shift -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shift</label>
                    <div class="flex items-center">
                        <div class="bg-green-100 rounded-lg p-3 mr-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-900 font-medium">{{ $workerShift->shift->name }}</p>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($workerShift->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($workerShift->shift->end_time)->format('H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                    <p class="text-gray-900 font-medium">
                        {{ \Carbon\Carbon::parse($workerShift->start_date)->format('d M Y') }}
                    </p>
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Selesai</label>
                    <p class="text-gray-900 font-medium">
                        @if($workerShift->end_date)
                            {{ \Carbon\Carbon::parse($workerShift->end_date)->format('d M Y') }}
                        @else
                            <span class="text-gray-400">Tidak ada batas</span>
                        @endif
                    </p>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    @if($workerShift->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Tidak Aktif
                        </span>
                    @endif
                </div>

                <!-- Durasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Durasi</label>
                    <p class="text-gray-900 font-medium">
                        @if($workerShift->end_date)
                            @php
                                $start = \Carbon\Carbon::parse($workerShift->start_date);
                                $end = \Carbon\Carbon::parse($workerShift->end_date);
                                $days = $end->diffInDays($start) + 1;
                            @endphp
                            {{ $days }} hari
                        @else
                            <span class="text-gray-400">Tidak terbatas</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <span class="font-medium">Dibuat:</span>
                    {{ \Carbon\Carbon::parse($workerShift->created_at)->format('d M Y H:i') }}
                </div>
                <div>
                    <span class="font-medium">Terakhir Diubah:</span>
                    {{ \Carbon\Carbon::parse($workerShift->updated_at)->format('d M Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    <div id="off-day-management" class="mt-6">
        @include('admin.workers.partials.off-days-manager', ['worker' => $workerShift->worker])
    </div>

    @if(isset($rotationShifts) && $rotationShifts->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0114-7M19 5a9 9 0 00-14 7"/>
                </svg>
                <h2 class="text-lg font-semibold text-gray-800">Informasi Rotasi Shift</h2>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ ($isRotating ?? false) ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ ($isRotating ?? false) ? 'Rotasi' : 'Tetap' }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Timeline jadwal shift pegawai ini berdasarkan periode yang tersimpan</p>
        </div>

        <div class="p-6 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                    <p class="text-indigo-700 text-xs font-medium">Total Periode</p>
                    <p class="text-indigo-900 text-xl font-bold">{{ $rotationShifts->count() }}</p>
                </div>
                <div class="bg-purple-50 border border-purple-100 rounded-lg p-3">
                    <p class="text-purple-700 text-xs font-medium">Jenis Shift Berbeda</p>
                    <p class="text-purple-900 text-xl font-bold">{{ $rotationShifts->pluck('shift_id')->unique()->count() }}</p>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-lg p-3">
                    <p class="text-green-700 text-xs font-medium">Status Pola</p>
                    <p class="text-green-900 text-xl font-bold">{{ ($isRotating ?? false) ? 'Rotasi' : 'Tetap' }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode Berlaku</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rotationShifts as $index => $rotation)
                    <tr class="hover:bg-gray-50 {{ $rotation->id === $workerShift->id ? 'bg-indigo-50/50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $rotation->shift->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $rotation->shift->code ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($rotation->shift)
                                {{ \Carbon\Carbon::parse($rotation->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($rotation->shift->end_time)->format('H:i') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($rotation->effective_from)->format('d M Y') }}
                            <span class="text-gray-400 mx-1">—</span>
                            @if($rotation->effective_until)
                                {{ \Carbon\Carbon::parse($rotation->effective_until)->format('d M Y') }}
                            @else
                                <span class="text-gray-400 italic">Tanpa batas</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rotation->id === $workerShift->id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Sedang Dilihat</span>
                            @elseif($rotation->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Shift History -->
    @if(isset($shiftHistories) && $shiftHistories->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Perubahan Shift</h2>
                <span class="bg-gray-200 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full">
                    {{ $shiftHistories->count() }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Daftar shift sebelumnya yang pernah berlaku untuk pegawai ini</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode Berlaku</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Diganti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($shiftHistories as $history)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="bg-orange-100 rounded-lg p-2 mr-3">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $history->shift->name ?? '-' }}</p>
                                    @if($history->shift)
                                        <p class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($history->shift->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($history->shift->end_time)->format('H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($history->effective_from)->format('d M Y') }}
                            <span class="text-gray-400 mx-1">—</span>
                            @if($history->effective_until)
                                {{ \Carbon\Carbon::parse($history->effective_until)->format('d M Y') }}
                            @else
                                <span class="text-gray-400 italic">Tanpa batas</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($history->changed_at)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $reasonLabels = [
                                    'shift_replaced' => ['Diganti', 'bg-blue-100 text-blue-800'],
                                    'shift_updated' => ['Diperbarui', 'bg-yellow-100 text-yellow-800'],
                                    'shift_deleted' => ['Dihapus', 'bg-red-100 text-red-800'],
                                ];
                                $label = $reasonLabels[$history->change_reason] ?? [$history->change_reason, 'bg-gray-100 text-gray-800'];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $label[1] }}">
                                {{ $label[0] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $history->changedByUser->name ?? 'System' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.worker-shifts.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>
</div>
@endsection
