@extends('layouts.admin')

@section('title', 'Detail Shift')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.shifts.index') }}" class="hover:text-green-600">Shift</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Detail</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-clock text-green-600 mr-2"></i>
                Detail Shift
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.master.shifts.edit', $shift->id) }}" 
                   class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <form action="{{ route('admin.master.shifts.destroy', $shift->id) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Shift Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Shift</h2>
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="text-sm text-gray-600">Nama Shift</label>
                        <p class="text-xl font-bold text-gray-900">{{ $shift->name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Jam Masuk (Default)</label>
                        <p class="text-lg font-semibold text-green-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Jam Keluar (Default)</label>
                        <p class="text-lg font-semibold text-red-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </p>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="text-sm text-gray-600">Durasi Kerja</label>
                        <p class="text-gray-900 font-medium">
                            <i class="fas fa-hourglass-half text-blue-500 mr-1"></i>
                            {{ number_format($shift->total_hours, 2) }} jam
                        </p>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="text-sm text-gray-600">Deskripsi</label>
                        <p class="text-gray-900">{{ $shift->description ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Status</label>
                        <div>
                            @if($shift->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Jadwal Kerja Per Hari</h2>
                @php
                    $days = [
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        0 => 'Minggu',
                    ];
                    $dayTimesByKey = $shift->dayTimes?->keyBy('day_of_week') ?? collect();
                    $hasPerDaySchedule = $dayTimesByKey->isNotEmpty();
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($days as $dayKey => $dayLabel)
                        @php
                            $dayTime = $dayTimesByKey->get($dayKey);
                            $isDayActive = !$hasPerDaySchedule || (bool) $dayTime;
                            $start = $dayTime?->start_time ? \Carbon\Carbon::parse($dayTime->start_time)->format('H:i') : \Carbon\Carbon::parse($shift->start_time)->format('H:i');
                            $end = $dayTime?->end_time ? \Carbon\Carbon::parse($dayTime->end_time)->format('H:i') : \Carbon\Carbon::parse($shift->end_time)->format('H:i');
                        @endphp
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="text-sm font-semibold text-gray-700">{{ $dayLabel }}</div>
                            @if($isDayActive)
                                <div class="text-xs text-gray-500 mt-1">{{ $start }} - {{ $end }}</div>
                            @else
                                <div class="text-xs text-red-500 mt-1">Libur / Tidak Aktif</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Side Info -->
        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow-md p-4">
                <h3 class="font-semibold text-gray-800 mb-3">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informasi Waktu
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dibuat:</span>
                        <span class="text-gray-900">{{ $shift->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Terakhir Update:</span>
                        <span class="text-gray-900">{{ $shift->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-700 font-medium">Total Jam Kerja</p>
                        <p class="text-2xl font-bold text-green-700">
                            {{ number_format($shift->total_hours, 2) }} Jam
                        </p>
                    </div>
                    <i class="fas fa-clock text-4xl text-green-400"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
