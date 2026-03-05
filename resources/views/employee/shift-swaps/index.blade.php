@extends('layouts.employee')

@section('title','Tukar Shift')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-white">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold flex items-center">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    Tukar Shift
                </h1>
                <p class="mt-2 text-green-100">Kelola permintaan tukar shift dengan rekan kerja Anda</p>
            </div>
            <a href="{{ route('employee.shift-swaps.create') }}" class="inline-flex items-center px-6 py-3 bg-white text-green-700 font-semibold rounded-lg shadow-md hover:bg-green-50 transition duration-200">
                <i class="fas fa-plus mr-2"></i>
                Buat Permintaan
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <i class="fas fa-list text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Menunggu</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Riwayat</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $summary['history'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-history text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5 border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Open Request</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $summary['open_requests'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4" x-data="{ filter: 'all' }">
        <!-- Status Filter (client-side) -->
        <div class="bg-white rounded-lg shadow-md p-4 flex flex-wrap items-center gap-2 mb-2">
            <span class="text-sm font-medium text-gray-700 mr-2">Tampilkan:</span>
            <button @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium">
                Semua
            </button>
            <button @click="filter = 'open'"
                    :class="filter === 'open' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium relative">
                Open Request
                @if(($summary['open_requests'] ?? 0) > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $summary['open_requests'] }}</span>
                @endif
            </button>
            <button @click="filter = 'pending'"
                    :class="filter === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium">
                Menunggu
            </button>
            <button @click="filter = 'approved'"
                    :class="filter === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium">
                Disetujui
            </button>
            <button @click="filter = 'history'"
                    :class="filter === 'history' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium">
                Riwayat
            </button>
        </div>

        <!-- Open Requests Section (from other workers) -->
        <div x-show="filter === 'all' || filter === 'open'" x-cloak class="space-y-4">
            @if(isset($openRequests) && $openRequests->count() > 0)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center mb-3">
                    <i class="fas fa-bullhorn text-blue-600 mr-2"></i>
                    <h3 class="text-lg font-semibold text-blue-800">Open Request dari Rekan Kerja</h3>
                    <span class="ml-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full">{{ $openRequests->count() }}</span>
                </div>
                <p class="text-sm text-blue-700 mb-4">Permintaan tukar shift terbuka yang bisa Anda terima</p>

                <div class="space-y-3">
                    @foreach($openRequests as $openRequest)
                    <div class="bg-white rounded-lg shadow-sm border border-blue-100 p-4 hover:shadow-md transition">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $openRequest->requester->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $openRequest->requester->department->name ?? '-' }}</p>
                                    <div class="mt-2 text-sm text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span class="font-medium">{{ $openRequest->requesterShift?->shift->name ?? 'N/A' }}</span>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        @if($openRequest->swap_type === 'single_date' && $openRequest->swap_date)
                                            {{ $openRequest->swap_date->format('d M Y') }}
                                        @elseif($openRequest->swap_type === 'date_range' && $openRequest->swap_start_date && $openRequest->swap_end_date)
                                            {{ $openRequest->swap_start_date->format('d M Y') }} s/d {{ $openRequest->swap_end_date->format('d M Y') }}
                                        @else
                                            {{ $openRequest->created_at->format('d M Y') }}
                                        @endif
                                    </div>
                                    @if($openRequest->reason)
                                    <p class="mt-1 text-sm text-gray-500 italic">
                                        <i class="fas fa-comment mr-1"></i>
                                        {{ Str::limit($openRequest->reason, 100) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500">{{ $openRequest->created_at->diffForHumans() }}</span>
                                <a href="{{ route('employee.shift-swaps.accept-open', $openRequest->id) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-hand-paper mr-2"></i>
                                    Terima
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @elseif(($summary['open_requests'] ?? 0) == 0)
            <div x-show="filter === 'open'" class="bg-gray-50 rounded-lg p-8 text-center">
                <i class="fas fa-bullhorn text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">Tidak ada open request dari rekan kerja saat ini</p>
            </div>
            @endif
        </div>

        <!-- Swap Requests List -->
        <div class="space-y-4" x-show="filter !== 'open'">
            @forelse($items as $item)
            @php
                $currentWorkerId = auth()->user()->worker->id;
                $isRequester = $item->requester_id === $currentWorkerId;
                $isTarget = $item->target_worker_id === $currentWorkerId;

                $statusConfig = [
                    'pending' => ['color' => 'yellow', 'icon' => 'clock', 'text' => 'Menunggu'],
                    'accepted' => ['color' => 'green', 'icon' => 'check', 'text' => 'Diterima'],
                    'awaiting_approval' => ['color' => 'blue', 'icon' => 'hourglass-half', 'text' => 'Menunggu Manager'],
                    'approved' => ['color' => 'green', 'icon' => 'check-double', 'text' => 'Disetujui Manager'],
                    'rejected' => ['color' => 'red', 'icon' => 'times', 'text' => 'Ditolak'],
                    'cancelled' => ['color' => 'gray', 'icon' => 'ban', 'text' => 'Dibatalkan'],
                    'executed' => ['color' => 'purple', 'icon' => 'check-circle', 'text' => 'Selesai'],
                ];
                $status = $statusConfig[$item->status] ?? ['color' => 'gray', 'icon' => 'question', 'text' => $item->status];
            @endphp

                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200 overflow-hidden"
                      data-status="{{ $item->status }}"
                      x-cloak
                      x-show="filter === 'all'
                          || (filter === 'pending' && ['pending','awaiting_approval'].includes($el.dataset.status))
                          || (filter === 'approved' && ['accepted','approved'].includes($el.dataset.status))
                          || (filter === 'history' && ['rejected','cancelled','executed'].includes($el.dataset.status))">
                <!-- Card Header with Status -->
                <div class="bg-{{ $status['color'] }}-50 border-l-4 border-{{ $status['color'] }}-500 px-4 sm:px-6 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-{{ $status['icon'] }} text-{{ $status['color'] }}-600"></i>
                            <span class="font-semibold text-{{ $status['color'] }}-800">{{ $status['text'] }}</span>
                        </div>
                        <span class="text-sm text-{{ $status['color'] }}-600">{{ $item->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-4 sm:p-6">
                    <div class="grid md:grid-cols-2 gap-4 md:gap-6">
                        <!-- Requester Info -->
                        <div class="space-y-3 min-w-0">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-green-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Peminta</p>
                                    <p class="font-semibold text-gray-900 truncate">{{ $item->requester->name }}</p>
                                    <p class="text-sm text-gray-600 truncate">{{ $item->requester->department->name ?? '-' }}</p>
                                </div>
                            </div>

                            @php
                                $reqShift = $item->requesterShift?->shift;
                            @endphp
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Shift Peminta</p>
                                <div class="flex items-center space-x-2 flex-wrap">
                                    <i class="fas fa-clock text-gray-400"></i>
                                    <span class="font-medium text-gray-900">{{ $reqShift->name ?? 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-history text-gray-400 mr-1"></i>
                                    {{ $reqShift->start_time ?? '' }} - {{ $reqShift->end_time ?? '' }}
                                </p>
                            </div>
                        </div>

                        <!-- Arrow & Target Info -->
                        <div class="space-y-3 min-w-0">
                            <div class="flex items-center justify-center my-2 md:my-0">
                                <i class="fas fa-arrow-down md:fa-arrow-right text-gray-400 text-2xl"></i>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-friends text-blue-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Target</p>
                                    <p class="font-semibold text-gray-900 truncate">{{ $item->targetWorker?->name ?? 'Open Request' }}</p>
                                    <p class="text-sm text-gray-600 truncate">{{ $item->targetWorker?->department->name ?? '-' }}</p>
                                </div>
                            </div>

                            @if($item->targetShift)
                                @php
                                    $tgtShift = $item->targetShift?->shift;
                                @endphp
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500 mb-1">Shift Target</p>
                                    <div class="flex items-center space-x-2 flex-wrap">
                                        <i class="fas fa-clock text-gray-400"></i>
                                        <span class="font-medium text-gray-900">{{ $tgtShift->name ?? 'N/A' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-history text-gray-400 mr-1"></i>
                                        {{ $tgtShift->start_time ?? '' }} - {{ $tgtShift->end_time ?? '' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tanggal Tukar Shift -->
                    <div class="mt-4 p-3 bg-indigo-50 border-l-4 border-indigo-400 rounded">
                        <p class="text-xs text-indigo-700 font-medium mb-1">
                            <i class="fas fa-calendar-alt mr-1"></i>Tanggal Tukar Shift:
                        </p>
                        @if($item->swap_type === 'single_date' && $item->swap_date)
                            <p class="text-sm text-indigo-900 font-semibold">
                                {{ $item->swap_date->format('d M Y') }}
                            </p>
                        @elseif($item->swap_type === 'date_range' && $item->swap_start_date && $item->swap_end_date)
                            <p class="text-sm text-indigo-900 font-semibold">
                                {{ $item->swap_start_date->format('d M Y') }} s/d {{ $item->swap_end_date->format('d M Y') }}
                            </p>
                        @elseif($item->swap_type === 'recurring' && $item->swap_dates)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($item->swap_dates as $swapDate)
                                    <span class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded">
                                        {{ \Carbon\Carbon::parse($swapDate)->format('d M Y') }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-indigo-600">Tanggal belum ditentukan</p>
                        @endif
                    </div>

                    <!-- Reason -->
                    @if($item->reason)
                        <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-400 rounded">
                            <p class="text-xs text-amber-700 font-medium mb-1">Alasan:</p>
                            <p class="text-sm text-amber-900 break-words">{{ $item->reason }}</p>
                        </div>
                    @endif

                    <!-- Manager Approval Info -->
                    @if($item->requires_manager_approval)
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-400 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                                <div>
                                    <p class="text-xs text-blue-700 font-medium">Memerlukan Persetujuan Manager/HR</p>
                                    <p class="text-xs text-blue-600 mt-1">
                                        {{ $item->requester->department->name ?? 'N/A' }} ↔ {{ $item->targetWorker?->department->name ?? 'N/A' }} (Beda Departemen)
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-green-50 border-l-4 border-green-400 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                <p class="text-xs text-green-700 font-medium">Tidak memerlukan persetujuan (Satu Departemen)</p>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($isTarget && $item->status === 'pending')
                            <form action="{{ route('employee.shift-swaps.accept', $item->id) }}" method="POST" class="inline w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                    <i class="fas fa-check mr-2"></i>
                                    Terima
                                </button>
                            </form>
                            <button type="button"
                                onclick="rejectSwap('{{ $item->id }}')"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <i class="fas fa-times mr-2"></i>
                                Tolak
                            </button>
                        @endif

                        @if($isRequester && !in_array($item->status, ['executed', 'cancelled']))
                            <form action="{{ route('employee.shift-swaps.cancel', $item->id) }}" method="POST" class="inline w-full sm:w-auto">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Yakin membatalkan permintaan ini?')"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                    <i class="fas fa-ban mr-2"></i>
                                    Batalkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Permintaan</h3>
                <p class="text-gray-500 mb-6">Anda belum memiliki permintaan tukar shift</p>
                <a href="{{ route('employee.shift-swaps.create') }}" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Buat Permintaan Pertama
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="fixed inset-0 backdrop-blur-sm bg-white/30 z-50 hidden" id="rejectModal" onclick="if(event.target === this) closeRejectModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 rounded-t-lg">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-times-circle mr-2"></i>
                            Tolak Permintaan
                        </h3>
                        <button type="button" onclick="closeRejectModal()" class="text-white hover:text-red-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan (opsional)</label>
                    <textarea name="reason" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" rows="4" placeholder="Tuliskan alasan penolakan Anda..."></textarea>
                    <p class="text-xs text-gray-500 mt-2">Alasan akan membantu peminta memahami keputusan Anda</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                        <i class="fas fa-times mr-2"></i>
                        Tolak
                    </button>
                    <button type="button" onclick="closeRejectModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-200">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rejectSwap(id) {
    const form = document.getElementById('rejectForm');
    form.action = "{{ route('employee.shift-swaps.index') }}/" + id + "/reject";
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
