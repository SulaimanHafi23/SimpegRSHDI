@extends('layouts.employee')

@section('title', 'Detail Absensi')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee.attendance.index') }}" 
           class="mr-4 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Detail Absensi</h1>
            <p class="text-gray-600 mt-1">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l, d F Y') }}</p>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        @if($attendance->status === 'present')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Hadir
            </span>
        @elseif($attendance->status === 'late')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Terlambat
            </span>
        @elseif($attendance->status === 'absent')
            <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Tidak Hadir
            </span>
        @endif
    </div>

    <!-- Attendance Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Absensi</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Check In -->
                <div class="border-l-4 border-green-500 pl-4">
                    <div class="text-sm text-gray-600 mb-1">Check In</div>
                    <div class="text-2xl font-bold text-gray-800">
                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}
                    </div>
                </div>

                <!-- Check Out -->
                <div class="border-l-4 border-red-500 pl-4">
                    <div class="text-sm text-gray-600 mb-1">Check Out</div>
                    <div class="text-2xl font-bold text-gray-800">
                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : 'Belum Check Out' }}
                    </div>
                </div>

                <!-- Duration -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <div class="text-sm text-gray-600 mb-1">Durasi Kerja</div>
                    <div class="text-xl font-bold text-gray-800">
                        @if($attendance->check_in && $attendance->check_out)
                            @php
                                $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                $duration = $checkIn->diff($checkOut);
                            @endphp
                            {{ $duration->format('%H jam %I menit') }}
                        @else
                            -
                        @endif
                    </div>
                </div>

                <!-- Location -->
                <div class="border-l-4 border-purple-500 pl-4">
                    <div class="text-sm text-gray-600 mb-1">Lokasi</div>
                    <div class="text-xl font-bold text-gray-800">
                        {{ $attendance->location->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($attendance->notes)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Catatan</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $attendance->notes }}</p>
        </div>
    @endif

    <!-- Photos -->
    @if($attendance->photos && $attendance->photos->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Foto Absensi</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($attendance->photos as $photo)
                    <div class="border rounded-lg p-3">
                        <div class="text-sm text-gray-600 mb-2">
                            {{ ucfirst($photo->type) }} - {{ \Carbon\Carbon::parse($photo->created_at)->format('H:i:s') }}
                        </div>
                        <img src="{{ Storage::url($photo->photo_path) }}" 
                             alt="Foto {{ $photo->type }}"
                             class="w-full h-auto rounded-lg shadow-sm">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Check Out Button -->
    @if($attendance->check_in && !$attendance->check_out && \Carbon\Carbon::parse($attendance->attendance_date)->isToday())
        <div class="mt-6">
            <form action="{{ route('employee.attendance.check-out', $attendance->id) }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full md:w-auto px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150"
                        onclick="return confirm('Apakah Anda yakin ingin check-out?')">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Check Out Sekarang
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
