@extends('layouts.app')

@section('title', 'Detail Lembur')
@section('page-title', 'Detail Permohonan Lembur')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Overtime Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Informasi Lembur</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Tanggal Lembur:</strong></td>
                                <td>{{ $overtime->overtime_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu:</strong></td>
                                <td>{{ $overtime->start_time }} - {{ $overtime->end_time }}</td>
                            </tr>
                            <tr>
                                <td><strong>Durasi:</strong></td>
                                <td>{{ $overtime->planned_hours ?? 0 }} jam</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Status:</strong></td>
                                <td>
                                    @if($overtime->status == 'pending')
                                        <span class="badge bg-warning">Menunggu Persetujuan</span>
                                    @elseif($overtime->status == 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($overtime->status == 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($overtime->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Diajukan:</strong></td>
                                <td>{{ $overtime->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @if($overtime->approved_at)
                            <tr>
                                <td><strong>Disetujui:</strong></td>
                                <td>{{ $overtime->approved_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endif
                            @if($overtime->approvedBy)
                            <tr>
                                <td><strong>Disetujui oleh:</strong></td>
                                <td>{{ $overtime->approvedBy->full_name }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($overtime->reason)
                <div class="mt-3">
                    <h6><strong>Alasan Lembur:</strong></h6>
                    <p class="text-muted">{{ $overtime->reason }}</p>
                </div>
                @endif

                @if($overtime->work_description)
                <div class="mt-3">
                    <h6><strong>Deskripsi Pekerjaan:</strong></h6>
                    <p class="text-muted">{{ $overtime->work_description }}</p>
                </div>
                @endif

                @if($overtime->project_task)
                <div class="mt-3">
                    <h6><strong>Proyek/Tugas:</strong></h6>
                    <p class="text-muted">{{ $overtime->project_task }}</p>
                </div>
                @endif

                @if($overtime->notes)
                <div class="mt-3">
                    <h6><strong>Catatan:</strong></h6>
                    <p class="text-muted">{{ $overtime->notes }}</p>
                </div>
                @endif

                @if($overtime->rejection_reason)
                <div class="mt-3">
                    <div class="alert alert-danger">
                        <h6><strong>Alasan Penolakan:</strong></h6>
                        <p class="mb-0">{{ $overtime->rejection_reason }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Compensation Details -->
        @if($overtime->status == 'approved' && $overtime->overtime_amount)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Detail Kompensasi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="50%"><strong>Jam Aktual:</strong></td>
                                <td>{{ $overtime->actual_hours ?? $overtime->planned_hours }} jam</td>
                            </tr>
                            <tr>
                                <td><strong>Rate per Jam:</strong></td>
                                <td>Rp {{ number_format($overtime->hourly_rate ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Multiplier:</strong></td>
                                <td>{{ $overtime->overtime_multiplier ?? 1 }}x</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end">
                            <h4 class="text-success">
                                <strong>Total: Rp {{ number_format($overtime->overtime_amount, 0, ',', '.') }}</strong>
                            </h4>
                            <small class="text-muted">Kompensasi lembur</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('permits.overtime.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                    
                    @if($overtime->status == 'pending' && $overtime->canBeEdited())
                        <a href="{{ route('permits.overtime.edit', $overtime) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Permohonan
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteOvertime()">
                            <i class="fas fa-trash me-2"></i>Hapus Permohonan
                        </button>
                    @endif

                    @can('approve', $overtime)
                        @if($overtime->status == 'pending')
                            <hr>
                            <button type="button" class="btn btn-success" onclick="approveOvertime()">
                                <i class="fas fa-check me-2"></i>Setujui
                            </button>
                            <button type="button" class="btn btn-danger" onclick="rejectOvertime()">
                                <i class="fas fa-times me-2"></i>Tolak
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="avatar bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    <h6>{{ $overtime->user->full_name }}</h6>
                    <p class="text-muted mb-2">{{ $overtime->user->employee_id }}</p>
                    <p class="text-muted mb-2">{{ $overtime->user->email }}</p>
                    @if($overtime->user->employee)
                        <small class="text-muted">
                            {{ $overtime->user->employee->department->name ?? 'No Department' }} - 
                            {{ $overtime->user->employee->position->name ?? 'No Position' }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Permohonan Diajukan</h6>
                            <small class="text-muted">{{ $overtime->created_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    
                    @if($overtime->approved_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Permohonan Disetujui</h6>
                            <small class="text-muted">{{ $overtime->approved_at->format('d M Y H:i') }}</small>
                            @if($overtime->approvedBy)
                                <br><small class="text-muted">oleh {{ $overtime->approvedBy->full_name }}</small>
                            @endif
                        </div>
                    </div>
                    @elseif($overtime->status == 'rejected')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Permohonan Ditolak</h6>
                            <small class="text-muted">{{ $overtime->updated_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" action="{{ route('permits.overtime.destroy', $overtime) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deleteOvertime() {
    if (confirm('Apakah Anda yakin ingin menghapus permohonan lembur ini?')) {
        document.getElementById('deleteForm').submit();
    }
}

function approveOvertime() {
    if (confirm('Apakah Anda yakin ingin menyetujui permohonan lembur ini?')) {
        // Create approval form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("permits.overtime.approve", $overtime) }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectOvertime() {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason && reason.trim()) {
        // Create rejection form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("permits.overtime.reject", $overtime) }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const reasonField = document.createElement('input');
        reasonField.type = 'hidden';
        reasonField.name = 'rejection_reason';
        reasonField.value = reason.trim();

        form.appendChild(csrfToken);
        form.appendChild(reasonField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
.avatar {
    width: 60px;
    height: 60px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 20px;
    width: 2px;
    height: calc(100% + 10px);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    margin-left: 10px;
}
</style>
@endpush
