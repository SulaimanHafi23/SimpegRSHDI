@extends('layouts.admin')

@section('title', 'Detail Lembur')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-600 mb-4">
                <a href="{{ route('admin.overtime.index') }}" class="hover:text-green-600">Manajemen Lembur</a>
                <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-800">Detail Lembur</span>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">Detail Lembur</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap data lembur pegawai</p>
                </div>
                @if($overtime->status == 'Pending')
                <div class="flex space-x-2">
                    <a href="{{ route('admin.overtime.edit', $overtime->id) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150">
                        Edit
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @php
                        $statusConfig = [
                            'Pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'Approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'Rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];
                        $config = $statusConfig[$overtime->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => ''];
                    @endphp
                    <div class="{{ $config['bg'] }} rounded-full p-3">
                        <svg class="w-8 h-8 {{ $config['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-xl sm:text-2xl font-bold {{ $config['text'] }}">{{ $overtime->status }}</p>
                    </div>
                </div>
                
                @if($overtime->status == 'Pending')
                <div class="flex space-x-2">
                    <form action="{{ route('admin.overtime.approve', $overtime->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150"
                                onclick="return confirm('Setujui data lembur ini?')">
                            Approve
                        </button>
                    </form>
                    <form action="{{ route('admin.overtime.reject', $overtime->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150"
                                onclick="return confirm('Tolak data lembur ini?')">
                            Reject
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="md:col-span-2 space-y-6">
                <!-- Pegawai Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Pegawai</h2>
                    <div class="flex items-start space-x-4">
                        @if($overtime->worker->photo)
                            <img class="h-20 w-20 rounded-lg object-cover" 
                                 src="{{ Storage::url($overtime->worker->photo) }}" 
                                 alt="{{ $overtime->worker->name }}">
                        @else
                            <div class="h-20 w-20 rounded-lg bg-green-100 flex items-center justify-center">
                                <span class="text-green-600 font-bold text-3xl">
                                    {{ substr($overtime->worker->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800">{{ $overtime->worker->name }}</h3>
                            <p class="text-gray-600">{{ $overtime->worker->position->name ?? '-' }}</p>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">NIP:</span> {{ $overtime->worker->employee_number }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Email:</span> {{ $overtime->worker->email }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Phone:</span> {{ $overtime->worker->phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overtime Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Lembur</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Tanggal Lembur</span>
                            <span class="text-gray-800 font-semibold">{{ $overtime->overtime_date->format('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Waktu Mulai</span>
                            <span class="text-gray-800 font-semibold">{{ date('H:i', strtotime($overtime->start_time)) }} WIB</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Waktu Selesai</span>
                            <span class="text-gray-800 font-semibold">{{ date('H:i', strtotime($overtime->end_time)) }} WIB</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Total Jam</span>
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-bold">
                                {{ $overtime->total_hours }} Jam
                            </span>
                        </div>
                        <div class="py-3">
                            <span class="text-gray-600 font-medium block mb-2">Keterangan/Alasan</span>
                            <p class="text-gray-800 bg-gray-50 p-4 rounded-md">{{ $overtime->reason }}</p>
                        </div>
                    </div>
                </div>

                @if($overtime->approver)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Approval Information</h2>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Disetujui oleh</p>
                                <p class="text-gray-800 font-semibold">{{ $overtime->approver->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Approval</p>
                                <p class="text-gray-800 font-semibold">{{ $overtime->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Total Hours Card -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg shadow-md p-6 text-white">
                    <h2 class="text-base sm:text-lg font-bold mb-4">Total Jam Lembur</h2>
                    <div class="text-center">
                        <div class="flex items-baseline justify-center">
                            <p class="text-5xl font-bold">{{ $overtime->total_hours }}</p>
                            <p class="text-2xl ml-2">Jam</p>
                        </div>
                        <p class="text-purple-100 mt-2">{{ $overtime->overtime_date->format('d F Y') }}</p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Timeline</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">Data Dibuat</p>
                                <p class="text-xs text-gray-500">{{ $overtime->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        @if($overtime->updated_at != $overtime->created_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">Terakhir Diperbarui</p>
                                <p class="text-xs text-gray-500">{{ $overtime->updated_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($overtime->status == 'Approved' || $overtime->status == 'Rejected')
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full {{ $overtime->status == 'Approved' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $overtime->status == 'Approved' ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $overtime->status }}</p>
                                <p class="text-xs text-gray-500">{{ $overtime->updated_at->format('d M Y, H:i') }}</p>
                                @if($overtime->approver)
                                <p class="text-xs text-gray-600 mt-1">oleh {{ $overtime->approver->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 mb-3">Informasi Cepat</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hari</span>
                            <span class="font-semibold text-gray-800">{{ $overtime->overtime_date->format('l') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Durasi</span>
                            <span class="font-semibold text-gray-800">{{ $overtime->total_hours }} jam</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bulan</span>
                            <span class="font-semibold text-gray-800">{{ $overtime->overtime_date->format('F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
