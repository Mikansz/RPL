@extends('layouts.app')

@section('title', 'Persetujuan Lembur')
@section('page-title', 'Persetujuan Lembur')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_pending'] }}</h3>
                <p class="mb-0">Menunggu Persetujuan</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-check fa-2x text-success mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_approved_today'] }}</h3>
                <p class="mb-0">Disetujui Hari Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-half fa-2x text-info mb-3"></i>
                <h3 class="mb-1">{{ number_format($stats['total_hours_pending'], 1) }}</h3>
                <p class="mb-0">Total Jam Pending</p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Overtime Requests -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock"></i> Pengajuan Lembur Menunggu Persetujuan
                </h5>
            </div>
            <div class="card-body">
                @if($overtimes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Tanggal Lembur</th>
                                    <th>Waktu</th>
                                    <th>Durasi</th>
                                    <th>Pekerjaan</th>
                                    <th>Alasan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overtimes as $overtime)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $overtime->user->full_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $overtime->user->employee->employee_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $overtime->created_at->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $overtime->overtime_date->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $overtime->overtime_date->format('l') }}</small>
                                    </td>
                                    <td>
                                        {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                                    </td>
                                    <td>
                                        <strong class="text-info">{{ number_format($overtime->planned_hours, 1) }} jam</strong>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px;">
                                            {{ Str::limit($overtime->work_description, 50) }}
                                            @if(strlen($overtime->work_description) > 50)
                                                <a href="#" data-bs-toggle="tooltip" title="{{ $overtime->work_description }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 150px;">
                                            {{ Str::limit($overtime->reason, 30) }}
                                            @if(strlen($overtime->reason) > 30)
                                                <a href="#" data-bs-toggle="tooltip" title="{{ $overtime->reason }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('permits.overtime.show', $overtime) }}" 
                                               class="btn btn-outline-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('approve', $overtime)
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="approveOvertime({{ $overtime->id }})" title="Setujui">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="rejectOvertime({{ $overtime->id }})" title="Tolak">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $overtimes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>Tidak Ada Pengajuan Lembur Pending</h5>
                        <p class="text-muted">Semua pengajuan lembur sudah diproses.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Pengajuan Lembur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" 
                                  rows="3" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveOvertime(overtimeId) {
    if (confirm('Apakah Anda yakin ingin menyetujui pengajuan lembur ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permits/overtime/${overtimeId}/approve`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectOvertime(overtimeId) {
    const rejectForm = document.getElementById('rejectForm');
    rejectForm.action = `/permits/overtime/${overtimeId}/reject`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
