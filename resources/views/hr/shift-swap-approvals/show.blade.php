@extends('layouts.admin')

@section('title', 'Persetujuan Tukar Shift - HR')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-white">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold flex items-center">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    Persetujuan Tukar Shift (Tahap HR)
                </h1>
                <p class="mt-2 text-blue-100">Tinjau dan setujui permintaan yang sudah diverifikasi manager</p>
            </div>
            <a href="{{ route('hr.shift-swap-approvals.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-blue-700 font-semibold rounded-lg shadow-md hover:bg-blue-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

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
                        <span class="px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            {{ ucfirst(str_replace('_', ' ', $swap->status)) }}
                        </span>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-500"></i>
                            <span>Diajukan pada {{ $swap->requested_at?->format('d M Y H:i') ?? $swap->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-double mr-2 text-green-500"></i>
                            <span>Diverifikasi manager pada {{ $swap->manager_verified_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
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
                                    <p class="text-xs text-gray-600 font-medium">TUKAR DENGAN</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $swap->targetWorker->name ?? 'N/A' }}</p>
                                </div>
                            </div>
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shift Details Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Detail Shift
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">Shift Pemohon</p>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-lg font-bold text-gray-800">{{ $swap->requesterShift?->shift->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    @if($swap->swap_date)
                                        {{ $swap->swap_date->format('d M Y') }}
                                    @else
                                        {{ $swap->swap_start_date?->format('d M Y') ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">Shift Target</p>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-lg font-bold text-gray-800">{{ $swap->targetShift?->shift->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    @if($swap->swap_date)
                                        {{ $swap->swap_date->format('d M Y') }}
                                    @else
                                        {{ $swap->swap_start_date?->format('d M Y') ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manager Verification Info -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-green-200">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-user-tie mr-2"></i>
                        Verifikasi Manager
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Manager Verifikator</p>
                            <p class="text-lg font-bold text-gray-800 mt-1">{{ $swap->manager?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Waktu Verifikasi</p>
                            <p class="text-lg font-bold text-gray-800 mt-1">{{ $swap->manager_verified_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-tasks text-blue-600 mr-2"></i>
                    Aksi
                </h3>

                <div class="space-y-3">
                    <button type="button" onclick="openModal('approveModal')" class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition font-bold shadow-md">
                        <i class="fas fa-check mr-2"></i>
                        Setujui
                    </button>

                    <button type="button" onclick="openModal('rejectModal')" class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition font-bold shadow-md">
                        <i class="fas fa-times mr-2"></i>
                        Tolak
                    </button>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs text-blue-700 leading-relaxed">
                            <i class="fas fa-info-circle mr-2"></i>
                            Permohonan tukar shift ini sudah diverifikasi oleh manager. Anda sebagai HR dapat menyetujui atau menolaknya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <form action="{{ route('hr.shift-swap-approvals.approve', $swap->id) }}" method="POST">
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
        <form action="{{ route('hr.shift-swap-approvals.reject', $swap->id) }}" method="POST">
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

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');

    if (event.target === approveModal) {
        closeModal('approveModal');
    }
    if (event.target === rejectModal) {
        closeModal('rejectModal');
    }
});
</script>
@endsection
