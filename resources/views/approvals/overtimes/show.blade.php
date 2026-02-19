@extends('layouts.admin')

@section('title', 'Detail Pengajuan Lembur')

@section('content')
<div class="space-y-6" x-data="{
    showApproveModal: false,
    showRejectModal: false,
    approvalNotes: '',
    rejectionReason: ''
}">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="{{ route('approvals.overtimes.index') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-list mr-2"></i>Daftar Lembur
                </a>
            </li>
            <li>
                <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-500">Detail Pengajuan</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Lembur</h1>
            <p class="text-gray-600 mt-1">Review dan kelola pengajuan lembur pegawai</p>
        </div>

        @if($overtime->status === 'pending')
        <div class="flex gap-2">
            <button @click="showApproveModal = true"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center">
                <i class="fas fa-check mr-2"></i>Approve
            </button>
            <button @click="showRejectModal = true"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition inline-flex items-center">
                <i class="fas fa-times mr-2"></i>Reject
            </button>
        </div>
        @endif
    </div>

    @if($overtime->status === 'approved')
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-check-circle text-green-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-green-800 font-semibold">Lembur Telah Disetujui</h3>
                <p class="text-green-700 text-sm mt-1">
                    Disetujui oleh <strong>{{ $overtime->approvedBy->name }}</strong> pada {{ $overtime->approved_at->format('d M Y H:i') }}
                </p>
                @if($overtime->approval_notes)
                <p class="text-green-700 text-sm mt-2"><strong>Catatan:</strong> {{ $overtime->approval_notes }}</p>
                @endif
            </div>
        </div>
    </div>
    @elseif($overtime->status === 'rejected')
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-times-circle text-red-500 text-xl mt-1 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-red-800 font-semibold">Lembur Ditolak</h3>
                <p class="text-red-700 text-sm mt-1">Ditolak oleh <strong>{{ $overtime->approvedBy->name }}</strong> pada {{ $overtime->approved_at->format('d M Y H:i') }}</p>
                @if($overtime->rejection_reason)
                <p class="text-red-700 text-sm mt-2"><strong>Alasan:</strong> {{ $overtime->rejection_reason }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Worker Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pegawai</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-2xl">{{ strtoupper(substr($overtime->worker->name, 0, 2)) }}</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-bold text-gray-900">{{ $overtime->worker->name }}</h4>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center text-sm text-gray-600"><i class="fas fa-id-badge w-5 text-gray-400"></i><span class="ml-2"><strong>NIP:</strong> {{ $overtime->worker->nip }}</span></div>
                                <div class="flex items-center text-sm text-gray-600"><i class="fas fa-building w-5 text-gray-400"></i><span class="ml-2"><strong>Departemen:</strong> {{ $overtime->worker->department->name ?? '-' }}</span></div>
                                <div class="flex items-center text-sm text-gray-600"><i class="fas fa-briefcase w-5 text-gray-400"></i><span class="ml-2"><strong>Posisi:</strong> {{ $overtime->worker->department->name ?? '-' }}</span></div>
                                <div class="flex items-center text-sm text-gray-600"><i class="fas fa-envelope w-5 text-gray-400"></i><span class="ml-2">{{ $overtime->worker->email }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Details -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Lembur</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal</label>
                            <p class="text-base font-semibold text-gray-900"><i class="fas fa-calendar text-blue-500 mr-2"></i>{{ \Carbon\Carbon::parse($overtime->date)->format('d F Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jam</label>
                            <p class="text-base font-semibold text-gray-900">{{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Total Jam</label>
                            <p class="text-base font-semibold text-gray-900">{{ $overtime->total_hours }} jam</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Alasan</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200"><p class="text-gray-700 leading-relaxed">{{ $overtime->reason }}</p></div>
                        </div>

                        @if($overtime->document_path)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Dokumen Pendukung</label>
                            <a href="{{ Storage::url($overtime->document_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                <span class="text-sm text-gray-700">Lihat Dokumen</span>
                                <i class="fas fa-external-link-alt text-gray-400 ml-2 text-xs"></i>
                            </a>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Diajukan Pada</label>
                            <p class="text-base text-gray-900">{{ $overtime->created_at->format('d M Y H:i') }}</p>
                            <p class="text-sm text-gray-500">{{ $overtime->created_at->diffForHumans() }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            @if($overtime->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold"><i class="fas fa-clock mr-2"></i>Pending</span>
                            @elseif($overtime->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 font-semibold"><i class="fas fa-check mr-2"></i>Approved</span>
                            @elseif($overtime->status === 'rejected')
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 font-semibold"><i class="fas fa-times mr-2"></i>Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Timeline</h3></div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start"><div class="flex-shrink-0"><div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center"><i class="fas fa-plus text-blue-600 text-xs"></i></div></div><div class="ml-3 flex-1"><p class="text-sm font-medium text-gray-900">Pengajuan dibuat</p><p class="text-xs text-gray-500">{{ $overtime->created_at->format('d M Y H:i') }}</p></div></div>

                        @if($overtime->status === 'approved')
                        <div class="flex items-start"><div class="flex-shrink-0"><div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center"><i class="fas fa-check text-green-600 text-xs"></i></div></div><div class="ml-3 flex-1"><p class="text-sm font-medium text-gray-900">Disetujui</p><p class="text-xs text-gray-500">oleh {{ $overtime->approvedBy->name }}<br>{{ $overtime->approved_at->format('d M Y H:i') }}</p></div></div>
                        @elseif($overtime->status === 'rejected')
                        <div class="flex items-start"><div class="flex-shrink-0"><div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center"><i class="fas fa-times text-red-600 text-xs"></i></div></div><div class="ml-3 flex-1"><p class="text-sm font-medium text-gray-900">Ditolak</p><p class="text-xs text-gray-500">oleh {{ $overtime->approvedBy->name }}<br>{{ $overtime->approved_at->format('d M Y H:i') }}</p></div></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 backdrop-blur-sm bg-white/30" @click="showApproveModal = false"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Pengajuan Lembur</h3>
                <form method="POST" action="{{ route('approvals.overtimes.approve', $overtime->id) }}">
                    @csrf
                    <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Catatan Approval (Opsional)</label><textarea x-model="approvalNotes" name="approval_notes" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Tambahkan catatan jika diperlukan..."></textarea></div>
                    <div class="flex justify-end gap-2"><button type="button" @click="showApproveModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button><button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><i class="fas fa-check mr-2"></i>Approve</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 backdrop-blur-sm bg-white/30" @click="showRejectModal = false"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Pengajuan Lembur</h3>
                <form method="POST" action="{{ route('approvals.overtimes.reject', $overtime->id) }}">
                    @csrf
                    <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label><textarea x-model="rejectionReason" name="rejection_reason" rows="4" required class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200" placeholder="Jelaskan alasan penolakan..."></textarea><p class="text-xs text-gray-500 mt-1">Alasan akan dikirim ke pegawai</p></div>
                    <div class="flex justify-end gap-2"><button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button><button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"><i class="fas fa-times mr-2"></i>Reject</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
