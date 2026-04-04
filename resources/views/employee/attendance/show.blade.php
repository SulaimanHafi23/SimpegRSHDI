@extends('layouts.employee')

@section('title', 'Detail Absensi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('employee.attendance.index') }}"
                   class="mr-4 p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-blue-600"></i>
                        Detail Absensi
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l, d F Y') }}</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
                <i class="fas fa-user-clock"></i>
                <span>{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-600"></i>
            Status Overview
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Main Status -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <div class="text-center">
                    <div class="text-sm text-blue-700 mb-2">Status Kehadiran</div>
                    @if($attendance->status === 'present')
                        <div class="text-2xl mb-2">✅</div>
                        <div class="font-bold text-blue-800">Hadir</div>
                    @elseif($attendance->status === 'late')
                        <div class="text-2xl mb-2">⏰</div>
                        <div class="font-bold text-yellow-800">Terlambat</div>
                    @elseif($attendance->status === 'absent')
                        <div class="text-2xl mb-2">❌</div>
                        <div class="font-bold text-red-800">Tidak Hadir</div>
                    @endif
                </div>
            </div>

            <!-- Late Status -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                <div class="text-center">
                    <div class="text-sm text-yellow-700 mb-2">Keterlambatan</div>
                    <div class="text-2xl mb-2">⏱️</div>
                    <div class="font-bold text-yellow-800">
                        @if(abs($attendance->late_minutes) > 0)
                            {{ abs($attendance->late_minutes) }} Menit
                        @else
                            Tepat Waktu
                        @endif
                    </div>
                    @if(abs($attendance->late_minutes) > 0)
                        <div class="text-xs text-yellow-600 mt-1">
                            {{ abs($attendance->late_minutes) <= 15 ? 'Ringan' : (abs($attendance->late_minutes) <= 30 ? 'Sedang' : 'Berat') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Early Leave Status -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                <div class="text-center">
                    <div class="text-sm text-orange-700 mb-2">Pulang Awal</div>
                    <div class="text-2xl mb-2">🏃‍♂️</div>
                    <div class="font-bold text-orange-800">
                        @if($attendance->is_early_leave)
                            {{ $attendance->early_leave_minutes }} Menit
                        @else
                            Sesuai Jadwal
                        @endif
                    </div>
                    @if($attendance->is_early_leave)
                        <div class="text-xs text-orange-600 mt-1">
                            {{ $attendance->early_leave_minutes <= 15 ? 'Ringan' : ($attendance->early_leave_minutes <= 60 ? 'Sedang' : 'Berat') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Check-In & Check-Out Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Check-In Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-sign-in-alt text-green-600"></i>
                Informasi Check-In
            </h2>

            <div class="space-y-4">
                <!-- Check-In Time -->
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-green-700 mb-1 font-medium">Waktu Masuk</div>
                            <div class="text-2xl font-bold text-green-800">
                                {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}
                            </div>
                            @if($attendance->check_in)
                                <div class="text-xs text-green-600 mt-1">
                                    {{ \Carbon\Carbon::parse($attendance->check_in)->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-sign-in-alt text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Late Information -->
                @if(abs($attendance->late_minutes) > 0)
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            <div>
                                <div class="text-sm font-medium text-yellow-700">Keterlambatan Masuk</div>
                                <div class="text-lg font-bold text-yellow-800">{{ abs($attendance->late_minutes) }} menit</div>
                                <div class="text-xs text-yellow-600">
                                    Kategori: {{ abs($attendance->late_minutes) <= 15 ? 'Ringan' : (abs($attendance->late_minutes) <= 30 ? 'Sedang' : 'Berat') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Check-In Photo -->
                @php
                    $checkInPhoto = $attendance->photos->where('photo_type', 'check_in')->first();
                @endphp
                @if($checkInPhoto)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-camera text-green-600"></i>
                            Foto Check-In
                        </div>
                        <div class="relative group">
                            @php
                                $photoUrl = route('employee.attendance.photo', ['id' => $attendance->id, 'type' => 'check_in']);
                            @endphp

                            <img src="{{ $photoUrl }}"
                                 alt="Foto Check-In"
                                 class="w-full h-48 object-cover rounded-lg shadow-sm group-hover:shadow-md transition-shadow cursor-pointer"
                                 onclick="openImageModal('{{ $photoUrl }}', 'Foto Check-In')"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMDAgNzBDOTIuMjY4IDcwIDg2IDc2LjI2OCA4NiA4NFM5Mi4yNjggOTggMTAwIDk4UzExNCA5MS43MzIgMTE0IDg0UzEwNy43MzIgNzAgMTAwIDcwWk0xMDAgOTJDOTUuNTggOTIgOTIgODguNDIgOTIgODRTOTUuNTggNzYgMTAwIDc2UzEwOCA3OS41OCAxMDggODRTMTA0LjQyIDkyIDEwMCA5MloiIGZpbGw9IiM5Q0E0QUYiLz4KPHBhdGggZD0iTTEzMCAxMDBIMTQwVjEzMEgxMzBWMTAwWiIgZmlsbD0iIzlDQTRBRiIvPgo8cGF0aCBkPSJNNzAgMTAwSDgwVjEzMEg3MFYxMDBaIiBmaWxsPSIjOUNBNEFGIi8+Cjx0ZXh0IHg9IjUwJSIgeT0iMTUwIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjOUNBNEFGIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNHB4Ij5Gb3RvIFRpZGFrIFRlcmJhY2E8L3RleHQ+Cjwvc3ZnPgo='; this.classList.add('bg-gray-100'); this.classList.remove('cursor-pointer'); this.onclick=null;"
                                 loading="lazy">
                            {{-- Hover Indicator - Now using pointer-events-none to not block image --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 rounded-lg transition-opacity duration-200 flex items-end justify-center pb-3 pointer-events-none">
                                <span class="text-white text-sm font-medium bg-black/60 px-3 py-1 rounded-full">
                                    <i class="fas fa-expand-alt mr-1"></i>Klik untuk memperbesar
                                </span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-2 text-center">
                            📸 {{ \Carbon\Carbon::parse($checkInPhoto->created_at)->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                        <i class="fas fa-camera-slash text-gray-400 text-2xl mb-2"></i>
                        <div class="text-sm text-gray-500">Tidak ada foto check-in</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Check-Out Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-sign-out-alt text-red-600"></i>
                Informasi Check-Out
            </h2>

            <div class="space-y-4">
                <!-- Check-Out Time -->
                <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-red-700 mb-1 font-medium">Waktu Pulang</div>
                            <div class="text-2xl font-bold text-red-800">
                                {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : 'Belum Check Out' }}
                            </div>
                            @if($attendance->check_out)
                                <div class="text-xs text-red-600 mt-1">
                                    {{ \Carbon\Carbon::parse($attendance->check_out)->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                        <div class="text-red-600">
                            <i class="fas fa-sign-out-alt text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Early Leave Information -->
                @if($attendance->is_early_leave)
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                            <div>
                                <div class="text-sm font-medium text-orange-700">Pulang Lebih Awal</div>
                                <div class="text-lg font-bold text-orange-800">{{ $attendance->early_leave_minutes }} menit</div>
                                <div class="text-xs text-orange-600">
                                    Kategori: {{ $attendance->early_leave_minutes <= 15 ? 'Ringan' : ($attendance->early_leave_minutes <= 60 ? 'Sedang' : 'Berat') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Check-Out Photo -->
                @php
                    $checkOutPhoto = $attendance->photos->where('photo_type', 'check_out')->first();
                @endphp
                @if($checkOutPhoto)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-camera text-red-600"></i>
                            Foto Check-Out
                        </div>
                        <div class="relative group">
                            @php
                                $photoUrl = route('employee.attendance.photo', ['id' => $attendance->id, 'type' => 'check_out']);
                            @endphp

                            <img src="{{ $photoUrl }}"
                                 alt="Foto Check-Out"
                                 class="w-full h-48 object-cover rounded-lg shadow-sm group-hover:shadow-md transition-shadow cursor-pointer"
                                 onclick="openImageModal('{{ $photoUrl }}', 'Foto Check-Out')"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMDAgNzBDOTIuMjY4IDcwIDg2IDc2LjI2OCA4NiA4NFM5Mi4yNjggOTggMTAwIDk4UzExNCA5MS43MzIgMTE0IDg0UzEwNy43MzIgNzAgMTAwIDcwWk0xMDAgOTJDOTUuNTggOTIgOTIgODguNDIgOTIgODRTOTUuNTggNzYgMTAwIDc2UzEwOCA3OS41OCAxMDggODRTMTA0LjQyIDkyIDEwMCA5MloiIGZpbGw9IiM5Q0E0QUYiLz4KPHBhdGggZD0iTTEzMCAxMDBIMTQwVjEzMEgxMzBWMTAwWiIgZmlsbD0iIzlDQTRBRiIvPgo8cGF0aCBkPSJNNzAgMTAwSDgwVjEzMEg3MFYxMDBaIiBmaWxsPSIjOUNBNEFGIi8+Cjx0ZXh0IHg9IjUwJSIgeT0iMTUwIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjOUNBNEFGIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNHB4Ij5Gb3RvIFRpZGFrIFRlcmJhY2E8L3RleHQ+Cjwvc3ZnPgo='; this.classList.add('bg-gray-100'); this.classList.remove('cursor-pointer'); this.onclick=null;"
                                 loading="lazy">
                            {{-- Hover Indicator - Now using pointer-events-none to not block image --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 rounded-lg transition-opacity duration-200 flex items-end justify-center pb-3 pointer-events-none">
                                <span class="text-white text-sm font-medium bg-black/60 px-3 py-1 rounded-full">
                                    <i class="fas fa-expand-alt mr-1"></i>Klik untuk memperbesar
                                </span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-2 text-center">
                            📸 {{ \Carbon\Carbon::parse($checkOutPhoto->created_at)->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                        @if($attendance->check_out)
                            <i class="fas fa-camera-slash text-gray-400 text-2xl mb-2"></i>
                            <div class="text-sm text-gray-500">Tidak ada foto check-out</div>
                        @else
                            <i class="fas fa-clock text-gray-400 text-2xl mb-2"></i>
                            <div class="text-sm text-gray-500">Belum melakukan check-out</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Work Duration & Location Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-bar text-blue-600"></i>
            Informasi Kerja & Lokasi
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Duration -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <div class="text-center">
                    <div class="text-sm text-blue-700 mb-2 font-medium">Durasi Kerja</div>
                    <div class="text-3xl mb-2">⏰</div>
                    <div class="text-xl font-bold text-blue-800">
                        @if($attendance->check_in && $attendance->check_out)
                            @php
                                $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                $duration = $checkIn->diff($checkOut);
                                $totalMinutes = $checkIn->diffInMinutes($checkOut);
                                $totalHours = floor($totalMinutes / 60);
                                $remainingMinutes = $totalMinutes % 60;
                            @endphp
                            {{ $totalHours }}j {{ $remainingMinutes }}m
                            <div class="text-xs text-blue-600 mt-1">{{ $totalMinutes }} menit total</div>
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                <div class="text-center">
                    <div class="text-sm text-purple-700 mb-2 font-medium">Lokasi Absen</div>
                    <div class="text-3xl mb-2">📍</div>
                    <div class="text-lg font-bold text-purple-800">
                        {{ config('attendance.location.name', 'Tidak diketahui') }}
                    </div>
                    <div class="text-xs text-purple-600 mt-1">
                        Radius: {{ config('attendance.location.radius', 0) }}m
                    </div>
                </div>
            </div>

            <!-- Work Score -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200">
                <div class="text-center">
                    <div class="text-sm text-indigo-700 mb-2 font-medium">Skor Kehadiran</div>
                    @php
                        $score = 100;
                        if(abs($attendance->late_minutes) > 0) $score -= min(50, abs($attendance->late_minutes) * 2);
                        if($attendance->is_early_leave) $score -= min(30, $attendance->early_leave_minutes);
                        $score = max(0, min(100, $score));
                    @endphp
                    <div class="text-3xl mb-2">
                        @if($score >= 90) ⭐ @elseif($score >= 70) 👍 @else ⚠️ @endif
                    </div>
                    <div class="text-xl font-bold {{ $score >= 90 ? 'text-green-600' : ($score >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $score }}/100
                    </div>
                    <div class="text-xs {{ $score >= 90 ? 'text-green-600' : ($score >= 70 ? 'text-yellow-600' : 'text-red-600') }} mt-1">
                        @if($score >= 90) Excellent @elseif($score >= 70) Good @else Need Improve @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($attendance->check_in && ($attendance->worker && $attendance->worker->workerShifts->isNotEmpty()))
        @php
            $todayShift = $attendance->worker->workerShifts
                ->where('effective_date', '<=', $attendance->attendance_date)
                ->sortByDesc('effective_date')
                ->first();
        @endphp

        @if($todayShift && $todayShift->shift)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-600"></i>
                    Analisis Waktu Kerja
                </h2>

                @php
                    $shiftStart = \Carbon\Carbon::parse($todayShift->shift->start_time);
                    $shiftEnd = \Carbon\Carbon::parse($todayShift->shift->end_time);
                    $checkInTime = \Carbon\Carbon::parse($attendance->check_in);
                    $checkOutTime = $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out) : null;
                    $tolerance = $todayShift->shift->tolerance_minutes ?? 15;

                    // Set tanggal yang sama
                    $shiftStart->setDateFrom($checkInTime);
                    $shiftEnd->setDateFrom($checkInTime);

                    // Jika shift end lebih kecil dari start (misal shift malam), tambah 1 hari
                    if ($shiftEnd->lessThan($shiftStart)) {
                        $shiftEnd->addDay();
                    }

                    $expectedCheckIn = $shiftStart->copy()->addMinutes($tolerance);
                    $expectedCheckOut = $shiftEnd;
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Jam Masuk -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                        <div class="text-center">
                            <div class="text-sm text-blue-700 mb-1">Jam Masuk</div>
                            <div class="text-xs text-blue-600 mb-2">Seharusnya: {{ $shiftStart->format('H:i') }}</div>
                            <div class="text-lg font-bold text-blue-800">{{ $checkInTime->format('H:i') }}</div>
                            <div class="text-xs mt-1 {{ $checkInTime->lessThanOrEqualTo($expectedCheckIn) ? 'text-green-600' : 'text-red-600' }}">
                                @if($checkInTime->lessThanOrEqualTo($expectedCheckIn))
                                    <i class="fas fa-check-circle mr-1"></i>Tepat Waktu
                                @else
                                    <i class="fas fa-times-circle mr-1"></i>Terlambat {{ abs($attendance->late_minutes) }} menit
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Jam Pulang -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                        <div class="text-center">
                            <div class="text-sm text-green-700 mb-1">Jam Pulang</div>
                            <div class="text-xs text-green-600 mb-2">Seharusnya: {{ $expectedCheckOut->format('H:i') }}</div>
                            <div class="text-lg font-bold text-green-800">
                                {{ $checkOutTime ? $checkOutTime->format('H:i') : 'Belum Check Out' }}
                            </div>
                            @if($checkOutTime)
                                <div class="text-xs mt-1 {{ !$attendance->is_early_leave ? 'text-green-600' : 'text-orange-600' }}">
                                    @if(!$attendance->is_early_leave)
                                        <i class="fas fa-check-circle mr-1"></i>Sesuai Jadwal
                                    @else
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Pulang Awal {{ $attendance->early_leave_minutes }} menit
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Total Jam Kerja -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                        <div class="text-center">
                            <div class="text-sm text-purple-700 mb-1">Jam Kerja</div>
                            @php
                                $expectedHours = $shiftStart->diffInHours($shiftEnd);
                                $actualHours = $checkOutTime ? $checkInTime->diffInHours($checkOutTime) : 0;
                            @endphp
                            <div class="text-xs text-purple-600 mb-2">Target: {{ $expectedHours }} jam</div>
                            <div class="text-lg font-bold text-purple-800">
                                @if($checkOutTime)
                                    {{ $actualHours }} jam
                                @else
                                    - jam
                                @endif
                            </div>
                            @if($checkOutTime)
                                <div class="text-xs mt-1 {{ $actualHours >= $expectedHours ? 'text-green-600' : 'text-red-600' }}">
                                    @if($actualHours >= $expectedHours)
                                        <i class="fas fa-check-circle mr-1"></i>Target Tercapai
                                    @else
                                        <i class="fas fa-times-circle mr-1"></i>Kurang {{ $expectedHours - $actualHours }} jam
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Evaluasi Keseluruhan -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                        <div class="text-center">
                            <div class="text-sm text-gray-700 mb-1">Evaluasi</div>
                            @php
                                $score = 100;
                                if(abs($attendance->late_minutes) > 0) $score -= min(50, abs($attendance->late_minutes) * 2);
                                if($attendance->is_early_leave) $score -= min(30, $attendance->early_leave_minutes);
                                $score = max(0, min(100, $score));
                            @endphp
                            <div class="text-xs text-gray-600 mb-2">Skor Kehadiran</div>
                            <div class="text-lg font-bold {{ $score >= 90 ? 'text-green-600' : ($score >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $score }}/100
                            </div>
                            <div class="text-xs mt-1 {{ $score >= 90 ? 'text-green-600' : ($score >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                @if($score >= 90)
                                    <i class="fas fa-star mr-1"></i>Excellent
                                @elseif($score >= 70)
                                    <i class="fas fa-thumbs-up mr-1"></i>Good
                                @else
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Need Improve
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Summary -->
                <div class="mt-6 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border border-indigo-200">
                    <h4 class="text-sm font-semibold text-indigo-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-clipboard-check"></i>
                        Ringkasan Kinerja
                    </h4>
                    <div class="text-sm text-indigo-700">
                        @if(abs($attendance->late_minutes) == 0 && !$attendance->is_early_leave)
                            <div class="flex items-center gap-2 text-green-700">
                                <i class="fas fa-check-circle"></i>
                                <span>Anda hadir tepat waktu dan mengikuti jadwal kerja dengan baik. Pertahankan disiplin ini!</span>
                            </div>
                        @else
                            <ul class="space-y-1">
                                @if(abs($attendance->late_minutes) > 0)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                                        <span>Terlambat masuk {{ abs($attendance->late_minutes) }} menit. Usahakan datang lebih awal untuk menghindari keterlambatan.</span>
                                    </li>
                                @endif
                                @if($attendance->is_early_leave)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-clock text-orange-600 mt-1"></i>
                                        <span>Pulang {{ $attendance->early_leave_minutes }} menit lebih awal. Pastikan menyelesaikan jam kerja sesuai jadwal.</span>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- GPS Information -->
    @if($attendance->latitude && $attendance->longitude)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-satellite-dish text-blue-600"></i>
                Informasi GPS
            </h2>

            <div class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200">
                        <div class="text-xs text-blue-700 mb-1">Latitude</div>
                        <div class="font-mono text-sm font-bold text-blue-800">{{ number_format($attendance->latitude, 6) }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200">
                        <div class="text-xs text-blue-700 mb-1">Longitude</div>
                        <div class="font-mono text-sm font-bold text-blue-800">{{ number_format($attendance->longitude, 6) }}</div>
                    </div>
                </div>

                @if($attendance->accuracy)
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-3 border border-green-200">
                        <div class="text-xs text-green-700 mb-1">Akurasi GPS</div>
                        <div class="text-sm font-bold text-green-800">±{{ round($attendance->accuracy) }} meter</div>
                    </div>
                @endif

                <div class="text-center">
                    <a href="https://www.google.com/maps?q={{ $attendance->latitude }},{{ $attendance->longitude }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        Lihat di Google Maps
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Check Out Button -->
    @if($attendance->check_in && !$attendance->check_out && \Carbon\Carbon::parse($attendance->attendance_date)->isToday())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="text-center">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2 flex items-center justify-center gap-2">
                        <i class="fas fa-clock text-orange-600"></i>
                        Belum Check Out?
                    </h3>
                    <p class="text-sm text-gray-600">Anda sudah melakukan check-in hari ini. Silakan lakukan check-out untuk menyelesaikan absensi.</p>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('employee.attendance.check-out-form') }}"
                       class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                        Check Out Lengkap
                    </a>

                    <div class="text-sm text-gray-500">atau</div>

                    <form action="{{ route('employee.attendance.check-out', $attendance->id) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-all duration-200"
                                onclick="return confirm('Check-out cepat tanpa foto. Lanjutkan?')">
                            <i class="fas fa-running"></i>
                            Quick Check Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center hidden">
    <div class="max-w-4xl max-h-[90vh] p-4">
        <div class="bg-white rounded-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-800"></h3>
                <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4 text-center">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-[70vh] object-contain rounded-lg">
            </div>
            <div class="p-4 border-t text-center">
                <a id="modalDownload" href="" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-download"></i>
                    Download Foto
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function openImageModal(src, title) {
        document.getElementById('imageModal').classList.remove('hidden');
        document.getElementById('modalImage').src = src;
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalDownload').href = src;
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>

@endsection
