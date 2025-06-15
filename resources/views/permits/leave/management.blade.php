@extends('layouts.app')

@section('title', 'Manajemen Cuti - HRD')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card-warning">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-half fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_pending'] }}</h3>
                <p class="mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-success">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_approved'] }}</h3>
                <p class="mb-0">Disetujui</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-info">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_this_month'] }}</h3>
                <p class="mb-0">Bulan Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-primary">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_days_this_month'] }}</h3>
                <p class="mb-0">Hari Bulan Ini</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('permits.leave.management') }}">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="leave_type_id" class="form-label">Jenis Cuti</label>
                            <select name="leave_type_id" id="leave_type_id" class="form-select">
                                <option value="">Semua Jenis</option>
                                @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Leave Requests Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Semua Pengajuan Cuti
                </h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                        <i class="fas fa-check-double me-2"></i>Setujui Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($leaves->count() > 0)
                    <form id="bulkForm" method="POST" action="{{ route('permits.leave.bulk-approve') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Karyawan</th>
                                        <th>Jenis Cuti</th>
                                        <th>Periode</th>
                                        <th>Durasi</th>
                                        <th>Alasan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaves as $leave)
                                    <tr>
                                        <td>
                                            @if($leave->status === 'pending')
                                            <input type="checkbox" name="leave_ids[]" value="{{ $leave->id }}" class="form-check-input leave-checkbox">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong>{{ $leave->user->employee->full_name ?? $leave->user->first_name . ' ' . $leave->user->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $leave->user->employee->employee_id ?? $leave->user->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $leave->leaveType->name }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $leave->start_date->format('d M Y') }}</strong><br>
                                            <small class="text-muted">s/d {{ $leave->end_date->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $leave->total_days }} hari</span>
                                            @if($leave->is_half_day)
                                                <br><small class="text-info">({{ ucfirst($leave->half_day_type) }})</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width: 200px;">
                                                {{ Str::limit($leave->reason, 50) }}
                                            </div>
                                        </td>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('permits.leave.show', $leave) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('permits.leave.edit', $leave) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($leave->status === 'approved')
                                                <a href="{{ route('permits.leave.slip', $leave) }}" class="btn btn-sm btn-outline-warning" title="Cetak Slip" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @endif
                                                @if($leave->status === 'pending')
                                                <form method="POST" action="{{ route('permits.leave.approve', $leave) }}" class="d-inline approve-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Setujui">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Tolak" onclick="rejectLeave({{ $leave->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <form method="POST" action="{{ route('permits.leave.destroy', $leave) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengajuan cuti ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $leaves->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada pengajuan cuti</h5>
                        <p class="text-muted">Belum ada pengajuan cuti yang sesuai dengan filter yang dipilih.</p>
                    </div>
                @endif
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
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Alasan Penolakan</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Masukkan alasan penolakan cuti..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmitBtn">Tolak Cuti</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.leave-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.leave-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu pengajuan cuti untuk disetujui.');
        return;
    }
    
    if (confirm(`Yakin ingin menyetujui ${checkedBoxes.length} pengajuan cuti?`)) {
        document.getElementById('bulkForm').submit();
    }
}

function rejectLeave(leaveId) {
    console.log('Reject leave called for ID:', leaveId);
    const form = document.getElementById('rejectForm');
    form.action = `{{ url('permits/leave') }}/${leaveId}/reject`;
    console.log('Form action set to:', form.action);

    // Clear previous reason
    document.getElementById('rejection_reason').value = '';

    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

// Handle individual approve forms - Simple approach with confirmation
document.addEventListener('DOMContentLoaded', function() {
    const approveForms = document.querySelectorAll('.approve-form');

    approveForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Yakin ingin menyetujui cuti ini?')) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Let the form submit normally - no AJAX
            return true;
        });
    });

    // Handle reject form submission
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            console.log('Reject form submitted');
            const reason = document.getElementById('rejection_reason').value.trim();

            if (!reason) {
                e.preventDefault();
                alert('Alasan penolakan harus diisi!');
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('rejectSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            console.log('Form action:', this.action);
            console.log('Rejection reason:', reason);

            // Let the form submit normally
            return true;
        });
    }
});
</script>
@endsection
