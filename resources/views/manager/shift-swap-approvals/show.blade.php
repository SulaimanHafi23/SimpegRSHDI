@extends('layouts.admin')

@section('title', 'Detail Permintaan Tukar Shift')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-white">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold flex items-center">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    Detail Permintaan Tukar Shift
                </h1>
                <p class="mt-2 text-green-100">Informasi lengkap permintaan pertukaran shift</p>
            </div>
            <a href="{{ route('manager.shift-swap-approvals.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-green-700 font-semibold rounded-lg shadow-md hover:bg-green-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->


    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Status Permintaan
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-700 font-medium">Status Saat Ini:</span>
                        <span class="px-4 py-2 rounded-full text-sm font-bold
                            @if($swap->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($swap->status == 'awaiting_approval') bg-blue-100 text-blue-800
                            @elseif($swap->status == 'approved') bg-green-100 text-green-800
                            @elseif($swap->status == 'rejected') bg-red-100 text-red-800
                            @elseif($swap->status == 'executed') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ ucfirst(str_replace('_', ' ', $swap->status)) }}
                        </span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-clock mr-2 text-blue-500"></i>
                        <span>Diajukan pada {{ $swap->requested_at?->format('d M Y H:i') ?? $swap->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Workers Info Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        Informasi Pekerja
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Requester -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-5 border-2 border-green-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-green-500 text-white rounded-full w-10 h-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 font-medium">PEMOHON</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $swap->requester->name }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-building w-5 text-green-600"></i>
                                    <span>{{ $swap->requester->department?->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-envelope w-5 text-green-600"></i>
                                    <span>{{ $swap->requester->user?->email ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Target Worker -->
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-5 border-2 border-purple-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-purple-500 text-white rounded-full w-10 h-10 flex items-center justify-center mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600 font-medium">TARGET</p>
                                    @if($swap->targetWorker)
                                        <p class="text-lg font-bold text-gray-800">{{ $swap->targetWorker->name }}</p>
                                    @else
                                        <p class="text-lg font-bold text-gray-500">Open Request</p>
                                    @endif
                                </div>
                            </div>
                            @if($swap->targetWorker)
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-building w-5 text-purple-600"></i>
                                        <span>{{ $swap->targetWorker->department?->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-envelope w-5 text-purple-600"></i>
                                        <span>{{ $swap->targetWorker->user?->email ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-600">Permintaan terbuka untuk semua pekerja</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shift Details Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        Detail Shift
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Requester Shift -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-5 border-2 border-blue-200">
                            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-clock text-blue-600 mr-2"></i>
                                Shift Pemohon
                            </h3>
                            @php
                                $reqShift = $swap->requesterShift?->shift;
                            @endphp
                            @if($reqShift)
                                <div class="space-y-3">
                                    <div class="bg-white rounded-lg p-3">
                                        <p class="text-xs text-gray-600 mb-1">Nama Shift</p>
                                        <p class="text-lg font-bold text-blue-700">{{ $reqShift->name }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-white rounded-lg p-2">
                                            <p class="text-xs text-gray-600">Mulai</p>
                                            <p class="font-bold text-gray-800">{{ $reqShift->start_time instanceof \Carbon\Carbon ? $reqShift->start_time->format('H:i') : $reqShift->start_time }}</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-2">
                                            <p class="text-xs text-gray-600">Selesai</p>
                                            <p class="font-bold text-gray-800">{{ $reqShift->end_time instanceof \Carbon\Carbon ? $reqShift->end_time->format('H:i') : $reqShift->end_time }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-2">
                                        <p class="text-xs text-gray-600">Tanggal Tukar</p>
                                        <p class="font-bold text-gray-800">
                                            <i class="fas fa-calendar text-blue-600 mr-1"></i>
                                            @if($swap->swap_type === 'single_date' && $swap->swap_date)
                                                {{ $swap->swap_date->format('d M Y') }}
                                            @elseif($swap->swap_type === 'date_range' && $swap->swap_start_date && $swap->swap_end_date)
                                                {{ $swap->swap_start_date->format('d M Y') }} s/d {{ $swap->swap_end_date->format('d M Y') }}
                                            @elseif($swap->swap_type === 'recurring' && $swap->swap_dates)
                                                {{ collect($swap->swap_dates)->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M Y'))->join(', ') }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-600">Tidak ada informasi shift</p>
                            @endif
                        </div>

                        <!-- Target Shift -->
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-5 border-2 border-indigo-200">
                            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-clock text-indigo-600 mr-2"></i>
                                Shift Target
                            </h3>
                            @php
                                $tgtShift = $swap->targetShift?->shift;
                            @endphp
                            @if($tgtShift)
                                <div class="space-y-3">
                                    <div class="bg-white rounded-lg p-3">
                                        <p class="text-xs text-gray-600 mb-1">Nama Shift</p>
                                        <p class="text-lg font-bold text-indigo-700">{{ $tgtShift->name }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-white rounded-lg p-2">
                                            <p class="text-xs text-gray-600">Mulai</p>
                                            <p class="font-bold text-gray-800">{{ $tgtShift->start_time instanceof \Carbon\Carbon ? $tgtShift->start_time->format('H:i') : $tgtShift->start_time }}</p>
                                        </div>
                                        <div class="bg-white rounded-lg p-2">
                                            <p class="text-xs text-gray-600">Selesai</p>
                                            <p class="font-bold text-gray-800">{{ $tgtShift->end_time instanceof \Carbon\Carbon ? $tgtShift->end_time->format('H:i') : $tgtShift->end_time }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-2">
                                        <p class="text-xs text-gray-600">Tanggal Tukar</p>
                                        <p class="font-bold text-gray-800">
                                            <i class="fas fa-calendar text-indigo-600 mr-1"></i>
                                            @if($swap->swap_type === 'single_date' && $swap->swap_date)
                                                {{ $swap->swap_date->format('d M Y') }}
                                            @elseif($swap->swap_type === 'date_range' && $swap->swap_start_date && $swap->swap_end_date)
                                                {{ $swap->swap_start_date->format('d M Y') }} s/d {{ $swap->swap_end_date->format('d M Y') }}
                                            @elseif($swap->swap_type === 'recurring' && $swap->swap_dates)
                                                {{ collect($swap->swap_dates)->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M Y'))->join(', ') }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-600">Tidak ada informasi shift</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason Card -->
            @if($swap->reason)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-comment-dots mr-2"></i>
                        Alasan Permintaan
                    </h2>
                </div>
                <div class="p-6">
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
                        <p class="text-gray-700 leading-relaxed">{{ $swap->reason }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Info Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-info mr-2"></i>
                        Informasi Tambahan
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <span class="text-gray-700 font-medium flex items-center">
                            <i class="fas fa-user-shield text-gray-500 mr-2"></i>
                            Perlu Approval Manager:
                        </span>
                        @if($swap->requires_manager_approval)
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold">
                                <i class="fas fa-check-circle mr-1"></i> Ya (Cross-department)
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-bold">
                                <i class="fas fa-times-circle mr-1"></i> Tidak
                            </span>
                        @endif
                    </div>

                    @if($swap->manager_id)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-700 font-medium flex items-center">
                                <i class="fas fa-user-tie text-gray-500 mr-2"></i>
                                Manager:
                            </span>
                            <span class="text-gray-800 font-bold">{{ $swap->manager?->name ?? 'N/A' }}</span>
                        </div>
                        @if($swap->manager_approved_at)
                            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                                <span class="text-gray-700 font-medium flex items-center">
                                    <i class="fas fa-calendar-check text-gray-500 mr-2"></i>
                                    Disetujui Pada:
                                </span>
                                <span class="text-gray-800 font-bold">{{ $swap->manager_approved_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    @endif

                    @if($swap->executed_at)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-gray-700 font-medium flex items-center">
                                <i class="fas fa-play-circle text-gray-500 mr-2"></i>
                                Dieksekusi Pada:
                            </span>
                            <span class="text-gray-800 font-bold">{{ $swap->executed_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-gray-700 font-medium flex items-center">
                                <i class="fas fa-user-check text-gray-500 mr-2"></i>
                                Dieksekusi Oleh:
                            </span>
                            <span class="text-gray-800 font-bold">{{ $swap->executedBy?->name ?? 'N/A' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            @if($swap->status === 'awaiting_approval')
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-hand-pointer text-blue-600 mr-2"></i>
                        Aksi Persetujuan
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        Kedua pegawai sudah saling menyetujui. Menunggu persetujuan Manager/HR.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" onclick="approveSwap('{{ $swap->id }}')" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-check-circle mr-2"></i>
                            Setujui Permintaan
                        </button>
                        <button type="button" onclick="rejectSwap('{{ $swap->id }}')" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-times-circle mr-2"></i>
                            Tolak Permintaan
                        </button>
                    </div>
                </div>
            @endif

            @if(in_array($swap->status, ['approved', 'accepted']))
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-play text-purple-600 mr-2"></i>
                        Eksekusi Pertukaran
                    </h3>
                    <button type="button" onclick="executeSwap('{{ $swap->id }}')" class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                        <i class="fas fa-play-circle mr-2"></i>
                        Eksekusi Pertukaran Shift
                    </button>
                </div>
            @endif
        </div>

        <!-- Right Column - Audit Log -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden sticky top-6">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        Riwayat Audit
                    </h2>
                </div>
                <div class="p-6 max-h-[600px] overflow-y-auto">
                    @if($swap->auditLogs && $swap->auditLogs->count() > 0)
                        <div class="space-y-4">
                            @foreach($swap->auditLogs->sortByDesc('created_at') as $log)
                                <div class="border-l-4
                                    @if($log->action === 'approved') border-green-500 bg-green-50
                                    @elseif($log->action === 'rejected') border-red-500 bg-red-50
                                    @elseif($log->action === 'executed') border-purple-500 bg-purple-50
                                    @else border-blue-500 bg-blue-50
                                    @endif
                                    rounded-r-lg p-4 shadow-sm hover:shadow-md transition duration-200">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="font-bold text-gray-800 capitalize flex items-center">
                                            @if($log->action === 'approved')
                                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                            @elseif($log->action === 'rejected')
                                                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                            @elseif($log->action === 'executed')
                                                <i class="fas fa-play-circle text-purple-600 mr-2"></i>
                                            @else
                                                <i class="fas fa-circle text-blue-600 mr-2"></i>
                                            @endif
                                            {{ str_replace('_', ' ', $log->action) }}
                                        </span>
                                        <span class="text-xs text-gray-600 whitespace-nowrap ml-2">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $log->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <span class="font-medium">{{ $log->old_status ? ucfirst($log->old_status) : '-' }}</span>
                                        <i class="fas fa-arrow-right mx-2"></i>
                                        <span class="font-medium">{{ ucfirst($log->new_status) }}</span>
                                    </div>
                                    @if($log->notes)
                                        <div class="text-sm text-gray-700 mt-2 bg-white bg-opacity-50 rounded p-2">
                                            <i class="fas fa-comment text-gray-500 mr-1"></i>
                                            {{ $log->notes }}
                                        </div>
                                    @endif
                                    @if($log->user)
                                        <div class="text-xs text-gray-600 mt-2 flex items-center">
                                            <i class="fas fa-user text-gray-500 mr-1"></i>
                                            <span class="font-medium">{{ $log->user->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-history text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500">Belum ada riwayat audit</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <form action="{{ route('manager.shift-swap-approvals.approve', $swap->id) }}" method="POST">
            @csrf
            <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Setujui Permintaan
                </h3>
                <button type="button" onclick="closeModal('approveModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (opsional)
                </label>
                <textarea name="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition font-bold shadow-lg">
                    <i class="fas fa-check mr-2"></i>
                    Setujui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <form action="{{ route('manager.shift-swap-approvals.reject', $swap->id) }}" method="POST">
            @csrf
            <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    Tolak Permintaan
                </h3>
                <button type="button" onclick="closeModal('rejectModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Masukkan alasan penolakan..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Alasan penolakan wajib diisi</p>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition font-bold shadow-lg">
                    <i class="fas fa-times mr-2"></i>
                    Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Execute Modal -->
<div id="executeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <form action="{{ route('manager.shift-swap-approvals.execute', $swap->id) }}" method="POST">
            @csrf
            <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-play-circle text-purple-600 mr-2"></i>
                    Eksekusi Pertukaran Shift
                </h3>
                <button type="button" onclick="closeModal('executeModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
                    <p class="text-gray-700 leading-relaxed">
                        <i class="fas fa-exclamation-triangle text-purple-600 mr-2"></i>
                        Yakin ingin mengeksekusi pertukaran shift ini? Shift kedua pekerja akan ditukar secara permanen dan tidak dapat dibatalkan.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('executeModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg transition font-bold shadow-lg">
                    <i class="fas fa-play mr-2"></i>
                    Eksekusi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function approveSwap(id) {
    document.getElementById('approveModal').classList.remove('hidden');
}

function rejectSwap(id) {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function executeSwap(id) {
    document.getElementById('executeModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('approveModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('executeModal').classList.add('hidden');
    }
});

// Close modal on backdrop click
['approveModal', 'rejectModal', 'executeModal'].forEach(function(modalId) {
    document.getElementById(modalId)?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(modalId);
        }
    });
});
</script>
@endsection
