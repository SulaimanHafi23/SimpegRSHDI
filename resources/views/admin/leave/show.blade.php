@extends('layouts.admin')

@section('title', 'Detail Pengajuan Cuti')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-600 mb-4">
                <a href="{{ route('admin.leave.index') }}" class="hover:text-green-600">Manajemen Cuti</a>
                <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-800">Detail Pengajuan</span>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">Detail Pengajuan Cuti</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap pengajuan cuti pegawai</p>
                </div>
                @if($leaveRequest->status == 'Pending')
                <div class="flex space-x-2">
                    <a href="{{ route('admin.leave.edit', $leaveRequest->id) }}" 
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
                            'Cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'M6 18L18 6M6 6l12 12'],
                        ];
                        $config = $statusConfig[$leaveRequest->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => ''];
                    @endphp
                    <div class="{{ $config['bg'] }} rounded-full p-3">
                        <svg class="w-8 h-8 {{ $config['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status Pengajuan</p>
                        <p class="text-xl sm:text-2xl font-bold {{ $config['text'] }}">{{ $leaveRequest->status }}</p>
                    </div>
                </div>
                
                @if($leaveRequest->status == 'Pending')
                <div class="flex space-x-2">
                    <form action="{{ route('admin.leave.approve', $leaveRequest->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150"
                                onclick="return confirm('Setujui pengajuan cuti ini?')">
                            Setujui
                        </button>
                    </form>
                    <form action="{{ route('admin.leave.reject', $leaveRequest->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150"
                                onclick="return confirm('Tolak pengajuan cuti ini?')">
                            Tolak
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
                        @if($leaveRequest->worker->photo)
                            <img class="h-20 w-20 rounded-lg object-cover" 
                                 src="{{ Storage::url($leaveRequest->worker->photo) }}" 
                                 alt="{{ $leaveRequest->worker->name }}">
                        @else
                            <div class="h-20 w-20 rounded-lg bg-green-100 flex items-center justify-center">
                                <span class="text-green-600 font-bold text-3xl">
                                    {{ substr($leaveRequest->worker->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800">{{ $leaveRequest->worker->name }}</h3>
                            <p class="text-gray-600">{{ $leaveRequest->worker->position->name ?? '-' }}</p>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">NIP:</span> {{ $leaveRequest->worker->employee_number }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Email:</span> {{ $leaveRequest->worker->email }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Phone:</span> {{ $leaveRequest->worker->phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Cuti</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Jenis Cuti</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                {{ $leaveRequest->leave_type }}
                            </span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Tanggal Mulai</span>
                            <span class="text-gray-800 font-semibold">{{ $leaveRequest->start_date->format('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Tanggal Selesai</span>
                            <span class="text-gray-800 font-semibold">{{ $leaveRequest->end_date->format('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Total Hari</span>
                            <span class="text-gray-800 font-bold text-lg">{{ $leaveRequest->total_days }} hari</span>
                        </div>
                        <div class="py-3">
                            <span class="text-gray-600 font-medium block mb-2">Alasan</span>
                            <p class="text-gray-800 bg-gray-50 p-4 rounded-md">{{ $leaveRequest->reason }}</p>
                        </div>
                        
                        @if($leaveRequest->attachment_url)
                        <div class="py-3">
                            <span class="text-gray-600 font-medium block mb-2">Lampiran</span>
                            <a href="{{ route('admin.leave.download-attachment', $leaveRequest->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Lampiran
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                @if($leaveRequest->notes)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Catatan</h2>
                    <p class="text-gray-800 bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">{{ $leaveRequest->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-4 sm:space-y-6">
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
                                <p class="text-sm font-medium text-gray-900">Pengajuan Dibuat</p>
                                <p class="text-xs text-gray-500">{{ $leaveRequest->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        @if($leaveRequest->approved_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    @if($leaveRequest->status == 'Approved')
                                        Disetujui
                                    @elseif($leaveRequest->status == 'Rejected')
                                        Ditolak
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $leaveRequest->approved_at->format('d M Y, H:i') }}</p>
                                @if($leaveRequest->approver)
                                <p class="text-xs text-gray-600 mt-1">oleh {{ $leaveRequest->approver->name }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-lg shadow-md p-6 text-white">
                    <h2 class="text-base sm:text-lg font-bold mb-4">Durasi Cuti</h2>
                    <div class="text-center">
                        <p class="text-5xl font-bold">{{ $leaveRequest->total_days }}</p>
                        <p class="text-green-100 mt-2">Hari Kerja</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
