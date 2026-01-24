@extends('layouts.admin')

@section('title', 'Detail Dokumen')

@section('content')
<div class="space-y-6" x-data="{ showVerifyModal: false, showRejectModal: false, rejectionReason: '' }">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li><a href="{{ route('approvals.documents.index') }}" class="text-gray-700 hover:text-blue-600"><i class="fas fa-list mr-2"></i>Daftar Dokumen</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-500">Detail Dokumen</li>
        </ol>
    </nav>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Dokumen</h1>
            <p class="text-gray-600 mt-1">Verifikasi dokumen pegawai</p>
        </div>

        @if($document->status === 'pending')
        <div class="flex gap-2">
            <button @click="showVerifyModal = true" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center"><i class="fas fa-check mr-2"></i>Verify</button>
            <button @click="showRejectModal = true" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition inline-flex items-center"><i class="fas fa-times mr-2"></i>Reject</button>
        </div>
        @endif
    </div>

    @if($document->status === 'verified')
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start"><i class="fas fa-check-circle text-green-500 text-xl mt-1 mr-3"></i><div class="flex-1"><h3 class="text-green-800 font-semibold">Dokumen Terverifikasi</h3><p class="text-green-700 text-sm mt-1">Diverifikasi oleh <strong>{{ $document->verifiedBy->name }}</strong> pada {{ $document->verified_at->format('d M Y H:i') }}</p></div></div>
    </div>
    @elseif($document->status === 'rejected')
    <div class="bg-red-50 border border-red-200 rounded-lg p-4"><div class="flex items-start"><i class="fas fa-times-circle text-red-500 text-xl mt-1 mr-3"></i><div class="flex-1"><h3 class="text-red-800 font-semibold">Dokumen Ditolak</h3><p class="text-red-700 text-sm mt-1">Ditolak oleh <strong>{{ $document->verifiedBy->name }}</strong> pada {{ $document->verified_at->format('d M Y H:i') }}</p>@if($document->rejection_reason)<p class="text-red-700 text-sm mt-2"><strong>Alasan:</strong> {{ $document->rejection_reason }}</p>@endif</div></div></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow"><div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Informasi Pegawai</h3></div><div class="p-6"><div class="flex items-start space-x-4"><div class="flex-shrink-0"><div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center"><span class="text-blue-600 font-bold text-2xl">{{ strtoupper(substr($document->worker->name, 0, 2)) }}</span></div></div><div class="flex-1"><h4 class="text-xl font-bold text-gray-900">{{ $document->worker->name }}</h4><div class="mt-2 space-y-2"><div class="flex items-center text-sm text-gray-600"><i class="fas fa-id-badge w-5 text-gray-400"></i><span class="ml-2"><strong>NIP:</strong> {{ $document->worker->nip }}</span></div><div class="flex items-center text-sm text-gray-600"><i class="fas fa-building w-5 text-gray-400"></i><span class="ml-2"><strong>Departemen:</strong> {{ $document->worker->department->name ?? '-' }}</span></div><div class="flex items-center text-sm text-gray-600"><i class="fas fa-envelope w-5 text-gray-400"></i><span class="ml-2">{{ $document->worker->email }}</span></div></div></div></div></div></div></div>

            <div class="bg-white rounded-lg shadow"><div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Detail Dokumen</h3></div><div class="p-6"><div class="grid grid-cols-1 gap-4"><div><label class="block text-sm font-medium text-gray-500 mb-1">Tipe Dokumen</label><p class="text-base font-semibold text-gray-900">{{ $document->documentType->name }}</p></div><div><label class="block text-sm font-medium text-gray-500 mb-1">Diupload Pada</label><p class="text-base text-gray-900">{{ $document->created_at->format('d M Y H:i') }}</p></div>@if($document->document_path)<div><label class="block text-sm font-medium text-gray-500 mb-2">Dokumen</label><a href="{{ Storage::url($document->document_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition"><i class="fas fa-file-pdf text-red-500 mr-2"></i><span class="text-sm text-gray-700">Lihat Dokumen</span><i class="fas fa-external-link-alt text-gray-400 ml-2 text-xs"></i></a></div>@endif<div><label class="block text-sm font-medium text-gray-500 mb-1">Status</label>@if($document->status === 'pending')<span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold"><i class="fas fa-clock mr-2"></i>Pending</span>@elseif($document->status === 'verified')<span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 font-semibold"><i class="fas fa-check mr-2"></i>Verified</span>@elseif($document->status === 'rejected')<span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 font-semibold"><i class="fas fa-times mr-2"></i>Rejected</span>@endif</div></div></div></div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow"><div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Timeline</h3></div><div class="p-6"><div class="space-y-4"><div class="flex items-start"><div class="flex-shrink-0"><div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center"><i class="fas fa-plus text-blue-600 text-xs"></i></div></div><div class="ml-3 flex-1"><p class="text-sm font-medium text-gray-900">Dokumen diunggah</p><p class="text-xs text-gray-500">{{ $document->created_at->format('d M Y H:i') }}</p></div></div>@if($document->status === 'verified')<div class="flex items-start"><div class="flex-shrink-0"><div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center"><i class="fas fa-check text-green-600 text-xs"></i></div></div><div class="ml-3 flex-1"><p class="text-sm font-medium text-gray-900">Diverifikasi</p><p class="text-xs text-gray-500">oleh {{ $document->verifiedBy->name }}<br>{{ $document->verified_at->format('d M Y H:i') }}</p></div></div>@elseif($document->status === 'rejected')<div class="flex items-start"><div class="flex-shrink-0"><div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center"><i class="fas fa-times text-red-600 text-xs"></i></div></div><div class="ml-3 flex-1"><p class="text-sm font-medium text-gray-900">Ditolak</p><p class="text-xs text-gray-500">oleh {{ $document->verifiedBy->name }}<br>{{ $document->verified_at->format('d M Y H:i') }}</p></div></div>@endif</div></div></div>
        </div>
    </div>

    <!-- Verify Modal -->
    <div x-show="showVerifyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4"><div class="fixed inset-0 backdrop-blur-sm bg-white/30" @click="showVerifyModal = false"></div><div class="relative bg-white rounded-lg max-w-md w-full p-6"><h3 class="text-lg font-semibold text-gray-900 mb-4">Verify Dokumen</h3><form method="POST" action="{{ route('approvals.documents.verify', $document->id) }}">@csrf<div class="mb-4"><p class="text-sm text-gray-700">Anda yakin ingin menandai dokumen ini sebagai <strong>terverifikasi</strong>?</p></div><div class="flex justify-end gap-2"><button type="button" @click="showVerifyModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button><button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-check mr-2"></i>Verify</button></div></form></div></div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4"><div class="fixed inset-0 backdrop-blur-sm bg-white/30" @click="showRejectModal = false"></div><div class="relative bg-white rounded-lg max-w-md w-full p-6"><h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Dokumen</h3><form method="POST" action="{{ route('approvals.documents.reject', $document->id) }}">@csrf<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label><textarea x-model="rejectionReason" name="rejection_reason" rows="4" required class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200" placeholder="Jelaskan alasan penolakan..."></textarea><p class="text-xs text-gray-500 mt-1">Alasan akan dikirim ke pegawai</p></div><div class="flex justify-end gap-2"><button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button><button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"><i class="fas fa-times mr-2"></i>Reject</button></div></form></div></div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
