@extends('layouts.admin')

@section('title', 'Persetujuan Tukar Shift')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Persetujuan Tukar Shift</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Permintaan Menunggu Persetujuan</h6>
        </div>
        <div class="card-body">
            @if($items->isEmpty())
                <p class="text-muted">Tidak ada permintaan yang menunggu persetujuan.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pemohon</th>
                                <th>Target</th>
                                <th>Shift Pemohon</th>
                                <th>Shift Target</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->requested_at?->format('d M Y H:i') ?? $item->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ $item->requester->name }}</div>
                                        <small class="text-muted">{{ $item->requester->department?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($item->targetWorker)
                                            <div class="font-weight-bold">{{ $item->targetWorker->name }}</div>
                                            <small class="text-muted">{{ $item->targetWorker->department?->name ?? 'N/A' }}</small>
                                        @else
                                            <span class="text-muted">Open Request</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->requesterShift && $item->requesterShift->shift)
                                            <div>{{ $item->requesterShift->shift->name }}</div>
                                            <small class="text-muted">{{ $item->requesterShift->effective_from?->format('d M Y') ?? 'N/A' }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->targetShift && $item->targetShift->shift)
                                            <div>{{ $item->targetShift->shift->name }}</div>
                                            <small class="text-muted">{{ $item->targetShift->effective_from?->format('d M Y') ?? 'N/A' }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->reason)
                                            <small>{{ Str::limit($item->reason, 50) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($item->status == 'awaiting_approval') bg-warning
                                            @elseif($item->status == 'approved') bg-success
                                            @elseif($item->status == 'rejected') bg-danger
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        @if($item->requires_manager_approval)
                                            <small class="d-block text-muted">Cross-dept</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('manager.shift-swap-approvals.show', $item->id) }}" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @if($item->status === 'awaiting_approval')
                                                <button type="button" 
                                                    class="btn btn-success btn-sm"
                                                    onclick="approveSwap('{{ $item->id }}')">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                                <button type="button" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="rejectSwap('{{ $item->id }}')">
                                                    <i class="fas fa-times"></i> Tolak
                                                </button>
                                            @endif
                                            @if(in_array($item->status, ['approved', 'accepted']))
                                                <button type="button" 
                                                    class="btn btn-primary btn-sm"
                                                    onclick="executeSwap('{{ $item->id }}')">
                                                    <i class="fas fa-play"></i> Eksekusi
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
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Permintaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Setujui</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Permintaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Tolak</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Execute Modal -->
<div class="modal fade" id="executeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="executeForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Eksekusi Pertukaran Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin mengeksekusi pertukaran shift ini? Shift kedua pekerja akan ditukar secara permanen.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Eksekusi</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveSwap(id) {
    const form = document.getElementById('approveForm');
    form.action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/approve";
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectSwap(id) {
    const form = document.getElementById('rejectForm');
    form.action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/reject";
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function executeSwap(id) {
    const form = document.getElementById('executeForm');
    form.action = "{{ route('manager.shift-swap-approvals.index') }}/" + id + "/execute";
    const modal = new bootstrap.Modal(document.getElementById('executeModal'));
    modal.show();
}
</script>
@endsection
