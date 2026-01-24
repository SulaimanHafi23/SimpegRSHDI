@extends('layouts.admin')

@section('title', 'Persetujuan Tukar Shift')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-exchange-alt text-green-600 mr-2"></i>
            Persetujuan Tukar Shift
        </h1>
        <p class="text-gray-600 mt-1">Kelola permintaan pertukaran shift dari pekerja</p>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock text-yellow-600 mr-2"></i>
                Permintaan Menunggu Persetujuan
            </h2>
        </div>
        
        <div class="p-6">
            @if($items->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">Tidak ada permintaan yang menunggu persetujuan</p>
                </div>
            @else
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
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-calendar text-blue-500 mr-1"></i>
                                            {{ $item->requested_at?->format('d M Y') ?? $item->created_at->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->requested_at?->format('H:i') ?? $item->created_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <i class="fas fa-user text-green-500 mr-1"></i>
                                            {{ $item->requester->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-building text-gray-400 mr-1"></i>
                                            {{ $item->requester->department?->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->targetWorker)
                                            <div class="text-sm font-medium text-gray-900">
                                                <i class="fas fa-user text-purple-500 mr-1"></i>
                                                {{ $item->targetWorker->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                                {{ $item->targetWorker->department?->name ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-users mr-1"></i>
                                                Open Request
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->requesterShift && $item->requesterShift->shift)
                                            <div class="text-sm font-medium text-gray-900">
                                                <i class="fas fa-clock text-orange-500 mr-1"></i>
                                                {{ $item->requesterShift->shift->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $item->requesterShift->effective_from?->format('d M Y') ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->targetShift && $item->targetShift->shift)
                                            <div class="text-sm font-medium text-gray-900">
                                                <i class="fas fa-clock text-blue-500 mr-1"></i>
                                                {{ $item->targetShift->shift->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $item->targetShift->effective_from?->format('d M Y') ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item->reason)
                                            <div class="text-sm text-gray-600 max-w-xs">
                                                {{ Str::limit($item->reason, 50) }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($item->status == 'awaiting_approval') bg-yellow-100 text-yellow-800
                                            @elseif($item->status == 'approved') bg-green-100 text-green-800
                                            @elseif($item->status == 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($item->status == 'awaiting_approval')
                                                <i class="fas fa-clock mr-1"></i>
                                            @elseif($item->status == 'approved')
                                                <i class="fas fa-check mr-1"></i>
                                            @elseif($item->status == 'rejected')
                                                <i class="fas fa-times mr-1"></i>
                                            @endif
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        @if($item->requires_manager_approval)
                                            <div class="text-xs text-purple-600 mt-1">
                                                <i class="fas fa-exchange-alt"></i> Cross-dept
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center space-x-2">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('manager.shift-swap-approvals.show', $item->id) }}" 
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($item->status === 'awaiting_approval')
                                                <button type="button" 
                                                    class="text-green-600 hover:text-green-900"
                                                    onclick="approveSwap('{{ $item->id }}')"
                                                    title="Setujui">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" 
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="rejectSwap('{{ $item->id }}')"
                                                    title="Tolak">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            @if(in_array($item->status, ['approved', 'accepted']))
                                                <button type="button" 
                                                    class="text-purple-600 hover:text-purple-900"
                                                    onclick="executeSwap('{{ $item->id }}')"
                                                    title="Eksekusi">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="hidden fixed inset-0 backdrop-blur-sm bg-white/30 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeModal('approveModal')">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white" onclick="event.stopPropagation()">
        <form id="approveForm" method="POST">
            @csrf
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Setujui Permintaan
                </h3>
                <button type="button" onclick="closeModal('approveModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea name="notes" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                          rows="3"
                          placeholder="Tambahkan catatan..."></textarea>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" 
                        onclick="closeModal('approveModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    <i class="fas fa-check mr-2"></i>Setujui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 backdrop-blur-sm bg-white/30 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeModal('rejectModal')">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white" onclick="event.stopPropagation()">
        <form id="rejectForm" method="POST">
            @csrf
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    Tolak Permintaan
                </h3>
                <button type="button" onclick="closeModal('rejectModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" 
                          rows="3" 
                          required
                          placeholder="Jelaskan alasan penolakan..."></textarea>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" 
                        onclick="closeModal('rejectModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Execute Modal -->
<div id="executeModal" class="hidden fixed inset-0 backdrop-blur-sm bg-white/30 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeModal('executeModal')">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white" onclick="event.stopPropagation()">
        <form id="executeForm" method="POST">
            @csrf
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-play-circle text-purple-600 mr-2"></i>
                    Eksekusi Pertukaran Shift
                </h3>
                <button type="button" onclick="closeModal('executeModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <div class="flex items-start space-x-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                    <p class="text-sm text-yellow-800">
                        Yakin ingin mengeksekusi pertukaran shift ini? Shift kedua pekerja akan ditukar secara permanen.
                    </p>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" 
                        onclick="closeModal('executeModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                    <i class="fas fa-play mr-2"></i>Eksekusi
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

function approveSwap(id) {
    const form = document.getElementById('approveForm');
    form.action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/approve";
    openModal('approveModal');
}

function rejectSwap(id) {
    const form = document.getElementById('rejectForm');
    form.action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/reject";
    openModal('rejectModal');
}

function executeSwap(id) {
    const form = document.getElementById('executeForm');
    form.action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/execute";
    openModal('executeModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('bg-opacity-50')) {
        const modals = ['approveModal', 'rejectModal', 'executeModal'];
        modals.forEach(modalId => {
            document.getElementById(modalId).classList.add('hidden');
        });
    }
}
</script>
@endsection
