@extends('layouts.app')

@section('title', 'Detail Pengajuan Cuti')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Leave Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Informasi Cuti</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Jenis Cuti:</strong></td>
                                <td><span class="badge bg-info">{{ $leave->leaveType->name }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Periode:</strong></td>
                                <td>{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Durasi:</strong></td>
                                <td>
                                    {{ $leave->total_days }} hari
                                    @if($leave->is_half_day)
                                        <small class="text-info">({{ ucfirst($leave->half_day_type) }})</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($leave->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($leave->status === 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($leave->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Tanggal Pengajuan:</strong></td>
                                <td>{{ $leave->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @if($leave->approved_by)
                            <tr>
                                <td><strong>Disetujui Oleh:</strong></td>
                                <td>{{ $leave->approvedBy->first_name }} {{ $leave->approvedBy->last_name }}</td>
                            </tr>
                            @endif
                            @if($leave->approved_at)
                            <tr>
                                <td><strong>Tanggal Disetujui:</strong></td>
                                <td>{{ $leave->approved_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Cuti</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label"><strong>Alasan Cuti:</strong></label>
                            <p class="form-control-plaintext">{{ $leave->reason }}</p>
                        </div>

                        @if($leave->notes)
                        <div class="mb-3">
                            <label class="form-label"><strong>Catatan Tambahan:</strong></label>
                            <p class="form-control-plaintext">{{ $leave->notes }}</p>
                        </div>
                        @endif

                        @if($leave->emergency_contact)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Kontak Darurat:</strong></label>
                                    <p class="form-control-plaintext">{{ $leave->emergency_contact }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Telepon Darurat:</strong></label>
                                    <p class="form-control-plaintext">{{ $leave->emergency_phone }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($leave->work_handover)
                        <div class="mb-3">
                            <label class="form-label"><strong>Serah Terima Pekerjaan:</strong></label>
                            <p class="form-control-plaintext">{{ $leave->work_handover }}</p>
                        </div>
                        @endif

                        @if($leave->approval_notes)
                        <div class="mb-3">
                            <label class="form-label"><strong>Catatan Persetujuan:</strong></label>
                            <div class="alert alert-info">
                                {{ $leave->approval_notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachments -->
        @if($leave->attachments && count($leave->attachments) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Lampiran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($leave->attachments as $attachment)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">{{ $attachment['original_name'] }}</h6>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Ukuran: {{ number_format($attachment['size'] / 1024, 2) }} KB<br>
                                        Diunggah: {{ \Carbon\Carbon::parse($attachment['uploaded_at'])->format('d M Y H:i') }}
                                    </small>
                                </p>
                                <a href="{{ asset('uploads/leaves/' . $attachment['filename']) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download me-2"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Employee Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg mx-auto mb-3">
                        @if($leave->user->profile_photo)
                            <img src="{{ asset('storage/' . $leave->user->profile_photo) }}" alt="Profile" class="rounded-circle" width="80" height="80">
                        @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        @endif
                    </div>
                    <h6 class="mb-1">{{ $leave->user->employee->full_name ?? $leave->user->first_name . ' ' . $leave->user->last_name }}</h6>
                    <p class="text-muted mb-0">{{ $leave->user->employee->employee_id ?? $leave->user->username }}</p>
                </div>

                <table class="table table-sm table-borderless">
                    @if($leave->user->employee)
                    <tr>
                        <td><strong>Departemen:</strong></td>
                        <td>{{ $leave->user->employee->department->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Posisi:</strong></td>
                        <td>{{ $leave->user->employee->position->name ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $leave->user->email }}</td>
                    </tr>
                    @if($leave->user->phone)
                    <tr>
                        <td><strong>Telepon:</strong></td>
                        <td>{{ $leave->user->phone }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($leave->status === 'pending')
                        @if(auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) || auth()->user()->hasPermission('leave.approve'))
                        <form method="POST" action="{{ route('permits.leave.approve', $leave) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Yakin ingin menyetujui cuti ini?')">
                                <i class="fas fa-check me-2"></i>Setujui Cuti
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" onclick="rejectLeave({{ $leave->id }})">
                            <i class="fas fa-times me-2"></i>Tolak Cuti
                        </button>
                        @endif

                        @if($leave->canBeEdited() && (auth()->user()->id === $leave->user_id || auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR'])))
                        <a href="{{ route('permits.leave.edit', $leave) }}" class="btn btn-primary w-100">
                            <i class="fas fa-edit me-2"></i>Edit Cuti
                        </a>
                        @endif
                    @endif

                    @if($leave->status === 'approved')
                    <a href="{{ route('permits.leave.slip', $leave) }}" class="btn btn-info w-100" target="_blank">
                        <i class="fas fa-print me-2"></i>Cetak Slip Cuti
                    </a>
                    @endif

                    <a href="{{ route('permits.leave.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Pengajuan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST" action="{{ route('permits.leave.reject', $leave) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Alasan Penolakan</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Masukkan alasan penolakan cuti..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Cuti</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rejectLeave(leaveId) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection
