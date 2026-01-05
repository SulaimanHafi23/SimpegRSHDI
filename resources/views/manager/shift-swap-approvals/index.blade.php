@extends('layouts.admin')

@section('title', 'Approval Tukar Shift')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Approval Tukar Shift</h1>
            <p class="text-gray-600 mt-1">Kelola permintaan pertukaran shift karyawan</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Permintaan Menunggu Persetujuan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->requested_at?->format('d M Y') ?? $item->created_at->format('d M Y') }}
                            <div class="text-xs text-gray-500">{{ $item->requested_at?->format('H:i') ?? $item->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                                    {{ strtoupper(substr($item->requester->name, 0, 2)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->requester->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->requester->department?->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->targetWorker)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-semibold">
                                        {{ strtoupper(substr($item->targetWorker->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->targetWorker->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->targetWorker->department?->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-users mr-1"></i>Open Request
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->requesterShift && $item->requesterShift->shift)
                                <div class="text-sm font-medium text-gray-900">{{ $item->requesterShift->shift->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->requesterShift->effective_from?->format('d M Y') ?? 'N/A' }}</div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->targetShift && $item->targetShift->shift)
                                <div class="text-sm font-medium text-gray-900">{{ $item->targetShift->shift->name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->targetShift->effective_from?->format('d M Y') ?? 'N/A' }}</div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->reason)
                                <div class="text-sm text-gray-900">{{ Str::limit($item->reason, 50) }}</div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->status == 'awaiting_approval')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Menunggu
                                </span>
                            @elseif($item->status == 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Disetujui
                                </span>
                            @elseif($item->status == 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Ditolak
                                </span>
                            @elseif($item->status == 'accepted')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-handshake mr-1"></i>Diterima
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            @endif
                            @if($item->requires_manager_approval)
                                <div class="text-xs text-orange-600 mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> Cross-dept
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('manager.shift-swap-approvals.show', $item->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                   title="Lihat detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($item->status === 'awaiting_approval')
                                    <button type="button" 
                                        onclick="approveSwap('{{ $item->id }}')"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                        title="Setujui">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" 
                                        onclick="rejectSwap('{{ $item->id }}')"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                        title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                @if(in_array($item->status, ['approved', 'accepted']))
                                    <button type="button" 
                                        onclick="executeSwap('{{ $item->id }}')"
                                        class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                                        title="Eksekusi">
                                        <i class="fas fa-play"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak ada permintaan</h3>
                            <p class="text-gray-600">Tidak ada permintaan tukar shift yang menunggu persetujuan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Setujui Permintaan</h3>
                <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="approveForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan (opsional)
                    </label>
                    <textarea id="approve_notes" 
                              name="notes" 
                              rows="3" 
                              class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Tambahkan catatan jika perlu..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="closeApproveModal()"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tolak Permintaan</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="reject_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reject_reason" 
                              name="reason" 
                              rows="4" 
                              required
                              class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                              placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Execute Modal -->
<div id="executeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Eksekusi Pertukaran Shift</h3>
                <button onclick="closeExecuteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="executeForm" method="POST">
                @csrf
                <div class="mb-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Yakin ingin mengeksekusi pertukaran shift ini? Shift kedua pekerja akan ditukar secara permanen.
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="closeExecuteModal()"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Eksekusi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveSwap(id) {
    document.getElementById('approveModal').classList.remove('hidden');
    document.getElementById('approveForm').action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/approve";
    document.getElementById('approve_notes').value = '';
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

function rejectSwap(id) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectForm').action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/reject";
    document.getElementById('reject_reason').value = '';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function executeSwap(id) {
    document.getElementById('executeModal').classList.remove('hidden');
    document.getElementById('executeForm').action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/execute";
}

function closeExecuteModal() {
    document.getElementById('executeModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.getElementById('executeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeExecuteModal();
    }
});
</script>
@endsection
