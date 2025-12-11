@extends('layouts.workers')

@section('title', 'Detail Cuti')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Cuti</h1>
            <p class="mt-2 text-sm text-gray-600">Informasi lengkap pengajuan cuti</p>
        </div>
        <a href="{{ route('workers.leaves.index') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Status Badge -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $leave->leave_type ?? 'Cuti Tahunan' }}</h2>
                <p class="text-sm text-gray-600">Diajukan pada: {{ $leave->created_at->format('d F Y, H:i') ?? now()->format('d F Y, H:i') }}</p>
            </div>
            <div>
                @if(($leave->status ?? 'pending') === 'pending')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock mr-1"></i>Menunggu Persetujuan
                    </span>
                @elseif(($leave->status ?? 'pending') === 'approved')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Disetujui
                    </span>
                @else
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i>Ditolak
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Leave Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-info-circle text-green-600 mr-2"></i>Informasi Cuti
                </h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Tanggal Mulai</p>
                            <p class="text-base font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($leave->start_date ?? now())->format('d F Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Tanggal Selesai</p>
                            <p class="text-base font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($leave->end_date ?? now()->addDays(3))->format('d F Y') }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Hari Cuti</p>
                        <p class="text-base font-semibold text-gray-900">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                                {{ $leave->total_days ?? 3 }} hari
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Alasan</p>
                        <p class="text-base text-gray-900">{{ $leave->reason ?? 'Tidak ada alasan yang diberikan' }}</p>
                    </div>

                    @if(isset($leave->emergency_contact) && $leave->emergency_contact)
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Kontak Darurat</p>
                            <p class="text-base font-semibold text-gray-900">{{ $leave->emergency_contact }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">No. HP Darurat</p>
                            <p class="text-base font-semibold text-gray-900">{{ $leave->emergency_phone ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    @if(isset($leave->attachment) && $leave->attachment)
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600 mb-2">Lampiran</p>
                        <a href="{{ route('workers.leaves.download', $leave->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            <i class="fas fa-download mr-2 text-gray-600"></i>
                            <span class="text-sm font-medium text-gray-700">Download Lampiran</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approval Info -->
            @if(isset($leave->status) && $leave->status !== 'pending')
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-user-check text-blue-600 mr-2"></i>Informasi Persetujuan
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Disetujui/Ditolak Oleh</p>
                        <p class="text-base font-semibold text-gray-900">{{ $leave->approved_by_name ?? 'Admin' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tanggal</p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ $leave->approved_at ? \Carbon\Carbon::parse($leave->approved_at)->format('d F Y, H:i') : '-' }}
                        </p>
                    </div>
                    @if(isset($leave->approval_notes) && $leave->approval_notes)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Catatan</p>
                        <p class="text-base text-gray-900">{{ $leave->approval_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Actions -->
            @if(isset($leave->status) && $leave->status === 'pending')
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Aksi</h3>
                <div class="space-y-3">
                    <a href="{{ route('workers.leaves.edit', $leave->id) }}" 
                       class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-flex items-center justify-center">
                        <i class="fas fa-edit mr-2"></i>Edit Pengajuan
                    </a>
                    <form action="{{ route('workers.leaves.destroy', $leave->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin membatalkan pengajuan cuti ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition inline-flex items-center justify-center">
                            <i class="fas fa-trash mr-2"></i>Batalkan Pengajuan
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Leave Balance Info -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-bold mb-4">Sisa Jatah Cuti</h3>
                <div class="text-center">
                    <p class="text-5xl font-bold mb-2">{{ $leaveBalance ?? 12 }}</p>
                    <p class="text-sm opacity-90">dari 12 hari/tahun</p>
                </div>
                <div class="mt-4 pt-4 border-t border-green-400">
                    <div class="flex justify-between text-sm">
                        <span>Digunakan:</span>
                        <span class="font-semibold">{{ $usedLeaves ?? 0 }} hari</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
