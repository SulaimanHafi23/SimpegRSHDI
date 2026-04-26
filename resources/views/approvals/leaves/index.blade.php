@extends('layouts.admin')

@section('title', 'Persetujuan Cuti')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Persetujuan Cuti</h1>
            <p class="mt-1 text-sm text-gray-600">Daftar pengajuan cuti untuk proses persetujuan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalLeaves ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-yellow-700">Pending</p>
            <p class="mt-1 text-2xl font-bold text-yellow-800">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-green-700">Approved</p>
            <p class="mt-1 text-2xl font-bold text-green-800">{{ $approvedCount ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-red-700">Rejected</p>
            <p class="mt-1 text-2xl font-bold text-red-800">{{ $rejectedCount ?? 0 }}</p>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <form method="GET" action="{{ route('approvals.leaves.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label for="leave_type_id" class="mb-1 block text-sm font-medium text-gray-700">Jenis Cuti</label>
                <select id="leave_type_id" name="leave_type_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Jenis</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ (string)($filters['leave_type_id'] ?? '') === (string)$type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('approvals.leaves.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        @if($leaves->isEmpty())
            <div class="px-4 py-10 text-center text-sm text-gray-500">
                <i class="fas fa-folder-open text-gray-300 text-4xl mb-3 block"></i>
                Tidak ada data pengajuan cuti.
            </div>
        @else
            {{-- Mobile View --}}
            <div class="md:hidden space-y-4 p-4 bg-gray-50/50">
                @foreach($leaves as $index => $leave)
                    @php
                        $statusBadges = [
                            'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                            'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                        ];
                        $badge = $statusBadges[$leave->status] ?? ['variant' => 'secondary', 'label' => ucfirst($leave->status)];
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-3">
                            <div>
                                <span class="text-xs text-gray-500 font-medium">#{{ $leaves->firstItem() + $index }}</span>
                                <div class="text-xs text-gray-400 mt-0.5 font-medium">
                                    <i class="far fa-clock mr-1"></i>{{ $leave->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-sm border border-gray-200">
                                    {{ substr($leave->worker->name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400 block mb-0.5">Pegawai</span>
                                    <span class="text-sm font-bold text-gray-900 leading-tight block">{{ $leave->worker->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">{{ $leave->worker->nip ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-3 grid grid-cols-2 gap-4 border border-gray-100">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                        <i class="fas fa-tags text-indigo-400 mr-1"></i>Jenis Cuti
                                    </span>
                                    <span class="text-sm font-bold text-indigo-700 block">{{ $leave->leaveType->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500 font-medium">{{ $leave->total_days }} hari</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                        <i class="fas fa-calendar-alt text-emerald-400 mr-1"></i>Periode
                                    </span>
                                    <span class="text-xs font-bold text-emerald-700 block">{{ optional($leave->start_date)->format('d M') }} - {{ optional($leave->end_date)->format('d M Y') }}</span>
                                    <span class="text-[10px] text-gray-400 font-medium">Durasi pengajuan</span>
                                </div>
                            </div>

                            @if($leave->reason)
                                <div class="bg-blue-50/50 rounded-lg p-3 border border-blue-100/50">
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-blue-500 block mb-1">
                                        <i class="fas fa-comment-alt mr-1"></i>Alasan
                                    </span>
                                    <p class="text-xs text-gray-700 italic leading-relaxed">"{{ Str::limit($leave->reason, 100) }}"</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ route('approvals.leaves.show', $leave->id) }}"
                               class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition active:scale-95">
                                <i class="fas fa-search mr-1.5"></i> Periksa Pengajuan
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Pegawai</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Jenis Cuti</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Durasi</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($leaves as $leave)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $leave->worker->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $leave->worker->nip ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $leave->leaveType->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ optional($leave->start_date)->format('d M Y') }} - {{ optional($leave->end_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $leave->total_days }} hari</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusClass = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-gray-100 text-gray-700',
                                        ][$leave->status] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('approvals.leaves.show', $leave->id) }}" class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($leaves->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $leaves->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
