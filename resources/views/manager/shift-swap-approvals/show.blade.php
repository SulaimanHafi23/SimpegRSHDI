@extends('layouts.admin')

@section('title', 'Detail Permintaan Tukar Shift')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1>Detail Permintaan Tukar Shift</h1>
        <a href="{{ route('manager.shift-swap-approvals.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

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

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Permintaan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Status:</th>
                            <td>
                                <span class="badge 
                                    @if($swap->status == 'pending') bg-warning
                                    @elseif($swap->status == 'awaiting_approval') bg-info
                                    @elseif($swap->status == 'approved') bg-success
                                    @elseif($swap->status == 'rejected') bg-danger
                                    @elseif($swap->status == 'executed') bg-purple
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $swap->status)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Diajukan:</th>
                            <td>{{ $swap->requested_at?->format('d M Y H:i') ?? $swap->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Pemohon:</th>
                            <td>
                                <strong>{{ $swap->requester->name }}</strong><br>
                                <small class="text-muted">{{ $swap->requester->department?->name ?? 'N/A' }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Target Pekerja:</th>
                            <td>
                                @if($swap->targetWorker)
                                    <strong>{{ $swap->targetWorker->name }}</strong><br>
                                    <small class="text-muted">{{ $swap->targetWorker->department?->name ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted">Open Request</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Shift Pemohon:</th>
                            <td>
                                @if($swap->requesterShift && $swap->requesterShift->shift)
                                    <strong>{{ $swap->requesterShift->shift->name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $swap->requesterShift->shift->start_time->format('H:i') }} - 
                                        {{ $swap->requesterShift->shift->end_time->format('H:i') }}<br>
                                        Tanggal: {{ $swap->requesterShift->effective_from?->format('d M Y') ?? 'N/A' }}
                                    </small>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Shift Target:</th>
                            <td>
                                @if($swap->targetShift && $swap->targetShift->shift)
                                    <strong>{{ $swap->targetShift->shift->name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $swap->targetShift->shift->start_time->format('H:i') }} - 
                                        {{ $swap->targetShift->shift->end_time->format('H:i') }}<br>
                                        Tanggal: {{ $swap->targetShift->effective_from?->format('d M Y') ?? 'N/A' }}
                                    </small>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Alasan:</th>
                            <td>{{ $swap->reason ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Perlu Approval Manager:</th>
                            <td>
                                @if($swap->requires_manager_approval)
                                    <span class="badge bg-warning">Ya</span>
                                    <small class="text-muted">(Cross-department)</small>
                                @else
                                    <span class="badge bg-secondary">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        @if($swap->manager_id)
                            <tr>
                                <th>Manager:</th>
                                <td>{{ $swap->manager?->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Disetujui Pada:</th>
                                <td>{{ $swap->manager_approved_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @endif
                        @if($swap->executed_at)
                            <tr>
                                <th>Dieksekusi Pada:</th>
                                <td>{{ $swap->executed_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Dieksekusi Oleh:</th>
                                <td>{{ $swap->executedBy?->name ?? 'N/A' }}</td>
                            </tr>
                        @endif
                    </table>

                    @if($swap->status === 'awaiting_approval')
                        <hr>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-success" onclick="approveSwap('{{ $swap->id }}')">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                            <button type="button" class="btn btn-danger" onclick="rejectSwap('{{ $swap->id }}')">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        </div>
                    @endif

                    @if(in_array($swap->status, ['approved', 'accepted']))
                        <hr>
                        <button type="button" class="btn btn-primary" onclick="executeSwap('{{ $swap->id }}')">
                            <i class="fas fa-play"></i> Eksekusi Pertukaran
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Audit</h6>
                </div>
                <div class="card-body">
                    @if($swap->auditLogs && $swap->auditLogs->count() > 0)
                        <div class="timeline">
                            @foreach($swap->auditLogs->sortByDesc('created_at') as $log)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <strong class="text-capitalize">{{ str_replace('_', ' ', $log->action) }}</strong>
                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                    </div>
                                    <small class="text-muted d-block">
                                        {{ $log->old_status ? ucfirst($log->old_status) : '-' }} 
                                        → {{ ucfirst($log->new_status) }}
                                    </small>
                                    @if($log->notes)
                                        <small class="text-muted d-block mt-1">{{ $log->notes }}</small>
                                    @endif
                                    @if($log->user)
                                        <small class="text-muted d-block">Oleh: {{ $log->user->name }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Belum ada riwayat audit.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('manager.shift-swap-approvals.approve', $swap->id) }}" method="POST">
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
            <form action="{{ route('manager.shift-swap-approvals.reject', $swap->id) }}" method="POST">
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
            <form action="{{ route('manager.shift-swap-approvals.execute', $swap->id) }}" method="POST">
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
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectSwap(id) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function executeSwap(id) {
    const modal = new bootstrap.Modal(document.getElementById('executeModal'));
    modal.show();
}
</script>
@endsection
