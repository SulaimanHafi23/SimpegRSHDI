@extends('layouts.employee')

@section('title', 'Jadwal Kerja')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <i class="fas fa-calendar-week text-purple-600 text-2xl"></i>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Jadwal Kerja Saya</h1>
                <p class="text-gray-600 text-sm mt-1">Lihat jadwal shift Anda</p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Month Navigation -->
    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5 mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('employee.shifts.index', ['month' => $date->copy()->subMonth()->month, 'year' => $date->copy()->subMonth()->year]) }}" 
               class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition shadow-md hover:shadow-lg">
                <i class="fas fa-chevron-left"></i>
            </a>
            
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="far fa-calendar-alt text-purple-600"></i>
                {{ $date->format('F Y') }}
            </h2>
            
            <a href="{{ route('employee.shifts.index', ['month' => $date->copy()->addMonth()->month, 'year' => $date->copy()->addMonth()->year]) }}" 
               class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition shadow-md hover:shadow-lg">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Regular Shift Info -->
    @if($workerShift)
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl shadow-md p-5 mb-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                Jadwal Regular Anda:
            </h3>
            
            @if(($workerShift->pattern_type ?? 'fixed') === 'fixed')
                <div class="text-sm">
                    <p class="text-gray-700">Pola: <span class="font-medium text-blue-700">Tetap</span></p>
                    <p class="text-gray-700 mt-1">Shift: <span class="font-medium text-green-700">{{ $workerShift->shift->name ?? '-' }}</span></p>
                    <p class="text-gray-600 mt-1">
                        {{ $workerShift->shift ? \Carbon\Carbon::parse($workerShift->shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($workerShift->shift->end_time)->format('H:i') : '' }}
                    </p>
                </div>
            @elseif($workerShift->pattern_type === 'custom')
                <div class="text-sm">
                    <p class="text-gray-700 mb-2">Pola: <span class="font-medium text-blue-700">Hari Tertentu</span></p>
                    <p class="text-gray-700 mb-2">Shift: <span class="font-medium text-green-700">{{ $workerShift->shift->name ?? '-' }}</span></p>
                    <p class="text-gray-700 mb-2">Hari Kerja:</p>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                            $workingDays = $workerShift->custom_working_days ?? [];
                        @endphp
                        @foreach($dayNames as $dayNum => $label)
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ in_array($dayNum, $workingDays) ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-500' }}">
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>
                    @if($workerShift->shift)
                        <p class="text-gray-600 mt-2">
                            Jam: {{ \Carbon\Carbon::parse($workerShift->shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($workerShift->shift->end_time)->format('H:i') }}
                        </p>
                    @endif
                </div>
            @elseif($workerShift->pattern_type === 'rotating')
                <div class="text-sm">
                    <p class="text-gray-700 mb-2">Pola: <span class="font-medium text-blue-700">Rotasi</span></p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @php
                            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                            $rotatingDays = $workerShift->rotating_days ?? [];
                        @endphp
                        @foreach($days as $dayNum => $label)
                            @php
                                $shiftId = $rotatingDays[$dayNum] ?? null;
                                $shift = $shiftId ? \App\Models\Shift::find($shiftId) : null;
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-gray-700">{{ $label }}:</span>
                                <span class="font-medium {{ $shift ? 'text-green-700' : 'text-gray-400' }}">
                                    {{ $shift ? $shift->name : 'Libur' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-300 rounded-xl shadow-md p-5 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-1"></i>
                <div>
                    <p class="font-semibold text-yellow-800 mb-1">Belum Ada Jadwal Regular</p>
                    <p class="text-yellow-700 text-sm">Anda belum memiliki jadwal shift regular. Hubungi HR untuk informasi lebih lanjut.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Calendar -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Calendar Header -->
        <div class="grid grid-cols-7 bg-gradient-to-r from-gray-50 to-gray-100 text-center font-semibold text-gray-700">
            <div class="py-4 border-r"><i class="far fa-sun text-yellow-500 mr-1"></i>Min</div>
            <div class="py-4 border-r">Sen</div>
            <div class="py-4 border-r">Sel</div>
            <div class="py-4 border-r">Rab</div>
            <div class="py-4 border-r">Kam</div>
            <div class="py-4 border-r">Jum</div>
            <div class="py-4"><i class="far fa-moon text-purple-500 mr-1"></i>Sab</div>
        </div>

        <!-- Calendar Body -->
        @foreach($calendar as $week)
            <div class="grid grid-cols-7 border-t">
                @foreach($week as $day)
                    <div class="min-h-28 p-3 border-r relative {{ !$day['isCurrentMonth'] ? 'bg-gray-50' : 'bg-white hover:bg-gray-50' }} {{ $day['isToday'] ? 'bg-gradient-to-br from-blue-50 to-blue-100' : '' }} transition-colors duration-150">
                        <!-- Date Number -->
                        <div class="flex justify-between items-start mb-2">
                            @if($day['isToday'])
                                <div class="bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-xs shadow-md font-bold">
                                    {{ $day['date']->day }}
                                </div>
                            @else
                                <span class="text-sm font-semibold {{ !$day['isCurrentMonth'] ? 'text-gray-400' : 'text-gray-700' }}">
                                    {{ $day['date']->day }}
                                </span>
                            @endif
                            @if($day['isOverride'])
                                <span class="text-xs bg-gradient-to-r from-orange-500 to-orange-600 text-white px-2 py-0.5 rounded-full shadow-sm" title="Jadwal Override">
                                    <i class="fas fa-exclamation"></i>
                                </span>
                            @endif
                        </div>

                        <!-- Shift Info -->
                        @if($day['shift'])
                            <div class="text-xs bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-2 border border-purple-200">
                                <div class="font-semibold text-purple-800 truncate flex items-center gap-1">
                                    <i class="fas fa-clock text-purple-600"></i>
                                    {{ $day['shift']->name }}
                                </div>
                                <div class="text-purple-600 mt-1 font-medium">
                                    {{ \Carbon\Carbon::parse($day['shift']->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($day['shift']->end_time)->format('H:i') }}
                                </div>
                            </div>
                        @else
                            @if($day['isCurrentMonth'])
                                <div class="text-xs text-gray-400 bg-gray-100 rounded-lg p-2 text-center">
                                    <i class="fas fa-umbrella-beach mr-1"></i>Libur
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white rounded-xl shadow-md p-5">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-purple-600"></i>
            Keterangan:
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 rounded"></div>
                <span class="text-gray-700">Hari Ini</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-white border-2 border-gray-300 rounded"></div>
                <span class="text-gray-700">Hari Biasa</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-gray-50 border-2 border-gray-300 rounded"></div>
                <span class="text-gray-700">Bulan Lain</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-3 py-1 rounded-full shadow-sm text-xs font-medium">
                    <i class="fas fa-exclamation mr-1"></i>Khusus
                </span>
                <span class="text-gray-700">Jadwal Override</span>
            </div>
        </div>
    </div>
</div>
@endsection
