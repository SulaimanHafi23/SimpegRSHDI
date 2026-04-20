@extends('layouts.admin')

@section('title', 'Detail Absensi')

@section('content')
<div class="space-y-6">
    {{-- Page Header with Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-check mr-3 text-blue-600"></i>
                    Detail Absensi
                </h1>
                <p class="text-sm text-gray-600 mt-1">Informasi lengkap data absensi pegawai</p>
            </div>
        <div class="flex space-x-2 w-full sm:w-auto">
            @if(!$attendance->check_out)
                <a href="{{ route('admin.attendance.check-out-form', $attendance->id) }}"
                   class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition duration-200 shadow-md">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Check Out
                </a>
            @endif
            @can('delete-attendance')
                <button onclick="showDeleteConfirm(() => document.getElementById('delete-form').submit())"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200 shadow-md">
                    <i class="fas fa-trash mr-2"></i>
                    Hapus
                </button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Worker Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Worker Profile --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col items-center">
                    @if($attendance->worker->photo_url && Storage::disk('public')->exists($attendance->worker->photo_url))
                        <img src="{{ asset('storage/' . $attendance->worker->photo_url) }}"
                             alt="{{ $attendance->worker->name }}"
                             class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-blue-500 object-cover mb-4">
                    @else
                        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-blue-500 overflow-hidden bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fas fa-user text-4xl sm:text-5xl text-gray-400"></i>
                        </div>
                    @endif
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-2">{{ $attendance->worker->name }}</h2>
                    <p class="text-sm text-gray-600">{{ $attendance->worker->nip }}</p>

                    @php
                        $statusConfig = [
                            'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir'],
                            'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat'],
                            'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir'],
                            'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit'],
                            'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin'],
                            'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti'],
                        ];
                        $status = $statusConfig[$attendance->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($attendance->status)];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }} mt-2">
                        {{ $status['label'] }}
                    </span>
                </div>
                <div class="mt-6 space-y-3 text-center border-t pt-4">
                    <div class="py-2">
                        <p class="text-sm text-gray-600">Departemen</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->worker->department->name ?? '-' }}</p>
                    </div>
                    <div class="py-2 border-t">
                        <p class="text-sm text-gray-600">Shift</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->shift->name ?? '-' }}</p>
                        @if($attendance->shift)
                            <p class="text-xs text-gray-500">{{ $attendance->shift->start_time }} - {{ $attendance->shift->end_time }}</p>
                        @endif
                    </div>
                    <div class="py-2 border-t">
                        <p class="text-sm text-gray-600">Lokasi</p>
                        <p class="font-semibold text-gray-900">{{ config('attendance.location.name', '-') }}</p>
                    </div>
                </div>
            </div>

            {{-- Status Info --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Absensi</h3>
                <div class="space-y-3">
                    @if($attendance->is_late)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-clock text-yellow-600"></i>
                                <span class="text-sm text-yellow-900">Terlambat</span>
                            </div>
                            <span class="font-semibold text-yellow-900">{{ $attendance->late_minutes }} menit</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                <span class="text-sm text-green-900">Tepat Waktu</span>
                            </div>
                        </div>
                    @endif

                    @if($attendance->is_early_leave)
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-door-open text-orange-600"></i>
                                <span class="text-sm text-orange-900">Pulang Cepat</span>
                            </div>
                            <span class="font-semibold text-orange-900">{{ $attendance->early_leave_minutes }} menit</span>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Right Column - Attendance Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Check In Info --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                        Check In
                    </span>
                    @if($attendance->check_in_by_admin)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-user-shield mr-1"></i>
                            Oleh Admin
                        </span>
                    @endif
                </h3>

                {{-- Admin Info if by admin --}}
                @if($attendance->check_in_by_admin)
                    <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                            <span class="text-sm text-purple-800">
                                Check-in dilakukan oleh
                                <strong>{{ $attendance->check_in_admin_display_name ?? 'Admin tidak ditemukan' }}</strong>
                            </span>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Tanggal</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->attendance_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Waktu</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->check_in?->format('H:i:s') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jarak dari Lokasi</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->distance_check_in ?? 0 }} meter</p>
                    </div>
                </div>

                {{-- Check In Photos --}}
                @if($attendance->checkInPhoto->count() > 0)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 mb-2">Foto Check In</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($attendance->checkInPhoto as $photo)
                                @if($photo->photo_path && Storage::disk('public')->exists($photo->photo_path))
                                    <div class="relative">
                                        <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                             alt="Check In Photo"
                                             class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75"
                                             onclick="window.open('{{ asset('storage/' . $photo->photo_path) }}', '_blank')">
                                        @if($attendance->check_in_by_admin)
                                            <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-purple-600 text-white text-xs rounded">
                                                Admin
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Check Out Info --}}
            @if($attendance->check_out)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                            Check Out
                        </span>
                        @if($attendance->check_out_by_admin)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-user-shield mr-1"></i>
                                Oleh Admin
                            </span>
                        @endif
                    </h3>

                    {{-- Admin Info if by admin --}}
                    @if($attendance->check_out_by_admin)
                        <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                                <span class="text-sm text-purple-800">
                                    Check-out dilakukan oleh
                                    <strong>{{ $attendance->check_out_admin_display_name ?? 'Admin tidak ditemukan' }}</strong>
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Tanggal</p>
                            <p class="font-semibold text-gray-900">{{ $attendance->check_out?->format('d F Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Waktu</p>
                            <p class="font-semibold text-gray-900">{{ $attendance->check_out?->format('H:i:s') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jarak dari Lokasi</p>
                            <p class="font-semibold text-gray-900">{{ $attendance->distance_check_out ?? 0 }} meter</p>
                        </div>
                    </div>

                    {{-- Check Out Photos --}}
                    @if($attendance->checkOutPhoto->count() > 0)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 mb-2">Foto Check Out</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($attendance->checkOutPhoto as $photo)
                                    @if($photo->photo_path && Storage::disk('public')->exists($photo->photo_path))
                                        <div class="relative">
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                                 alt="Check Out Photo"
                                                 class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75"
                                                 onclick="window.open('{{ asset('storage/' . $photo->photo_path) }}', '_blank')">
                                            @if($attendance->check_out_by_admin)
                                                <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-purple-600 text-white text-xs rounded">
                                                    Admin
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center py-8">
                        <i class="fas fa-clock text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">Belum Check Out</p>
                        <p class="text-sm text-gray-500 mt-1">Pegawai belum melakukan check out</p>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            @if($attendance->notes)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h3>
                    <p class="text-gray-700">{{ $attendance->notes }}</p>
                </div>
            @endif

            {{-- Working Hours Summary --}}
            @if($attendance->check_in && $attendance->check_out)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Jam Kerja</h3>
                    @php
                        $workingHours = $attendance->check_in->diffInMinutes($attendance->check_out);
                        $hours = floor($workingHours / 60);
                        $minutes = $workingHours % 60;
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-600">Total Jam Kerja</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $hours }}j {{ $minutes }}m</p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <p class="text-sm text-gray-600">Terlambat</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $attendance->late_minutes ?? 0 }}m</p>
                        </div>
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <p class="text-sm text-gray-600">Pulang Cepat</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $attendance->early_leave_minutes ?? 0 }}m</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Delete Form --}}
@can('delete-attendance')
    <form id="delete-form" action="{{ route('admin.attendance.destroy', $attendance->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endcan
@endsection
