@extends('layouts.admin')

@section('title', 'Detail Perjalanan Dinas')

@section('content')
<div class="space-y-6" x-data="{ showApproveModal: false, showRejectModal: false, approvalNotes: '', rejectionReason: '' }">
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li><a href="{{ route('approvals.business-trips.index') }}" class="text-gray-700 hover:text-blue-600"><i class="fas fa-list mr-2"></i>Daftar Perjalanan Dinas</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-500">Detail</li>
        </ol>
    </nav>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Detail Perjalanan Dinas</h1>
            <p class="text-gray-600">{{ $trip->destination }}</p>
        </div>
        @if($trip->status === 'pending')
        <div class="flex gap-2">
            <button @click="showApproveModal = true" class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
            <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Pegawai</p>
                <p class="font-semibold">{{ $trip->worker->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tanggal</p>
                <p class="font-semibold">{{ $trip->start_date->format('d M Y') }} - {{ $trip->end_date->format('d M Y') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Tujuan Perjalanan</p>
                <p class="text-gray-800">{{ $trip->purpose }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estimasi Biaya</p>
                <p class="font-semibold">{{ number_format($trip->estimated_cost, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="font-semibold">{{ ucfirst($trip->status) }}</p>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4"><div class="fixed inset-0 bg-black opacity-50" @click="showApproveModal = false"></div><div class="relative bg-white rounded-lg max-w-md w-full p-6"><h3 class="text-lg font-semibold mb-4">Approve Permohonan</h3><form method="POST" action="{{ route('approvals.business-trips.approve', $trip->id) }}">@csrf<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label><textarea x-model="approvalNotes" name="approval_notes" rows="3" class="w-full rounded-lg border-gray-300"></textarea></div><div class="flex justify-end gap-2"><button type="button" @click="showApproveModal = false" class="px-4 py-2 bg-gray-200 rounded">Batal</button><button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Approve</button></div></form></div></div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4"><div class="fixed inset-0 bg-black opacity-50" @click="showRejectModal = false"></div><div class="relative bg-white rounded-lg max-w-md w-full p-6"><h3 class="text-lg font-semibold mb-4">Reject Permohonan</h3><form method="POST" action="{{ route('approvals.business-trips.reject', $trip->id) }}">@csrf<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label><textarea x-model="rejectionReason" name="rejection_reason" rows="4" required class="w-full rounded-lg border-gray-300"></textarea></div><div class="flex justify-end gap-2"><button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-200 rounded">Batal</button><button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Reject</button></div></form></div></div>
    </div>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@endsection