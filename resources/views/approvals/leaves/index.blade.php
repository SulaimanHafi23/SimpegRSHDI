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

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
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
                    @forelse($leaves as $leave)
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
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada data pengajuan cuti.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
