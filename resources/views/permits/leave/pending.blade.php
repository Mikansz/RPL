@extends('layouts.app')

@section('title', 'Persetujuan Cuti')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card-warning">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-half fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_pending'] }}</h3>
                <p class="mb-0">Menunggu Persetujuan</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card-success">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_approved_today'] }}</h3>
                <p class="mb-0">Disetujui Hari Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card-info">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_days_pending'] }}</h3>
                <p class="mb-0">Total Hari Pending</p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Leave Requests -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-hourglass-half"></i> Pengajuan Cuti Menunggu Persetujuan
                </h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                        <i class="fas fa-check-double me-2"></i>Setujui Semua
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
                                        <th>Tanggal Pengajuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaves as $leave)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="leave_ids[]" value="{{ $leave->id }}" class="form-check-input leave-checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong>{{ $leave->user->employee->full_name ?? $leave->user->first_name . ' ' . $leave->user->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $leave->user->employee->employee_id ?? $leave->user->username }}</small>
                                                    @if($leave->user->employee && $leave->user->employee->department)
                                                        <br><small class="text-info">{{ $leave->user->employee->department->name }}</small>
                                                    @endif
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
                                            <div style="max-width: 250px;">
                                                {{ Str::limit($leave->reason, 80) }}
                                                @if($leave->notes)
                                                    <br><small class="text-muted">Catatan: {{ Str::limit($leave->notes, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $leave->created_at->format('d M Y H:i') }}</small>
                                            <br><small class="text-info">{{ $leave->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('permits.leave.show', $leave) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" action="{{ route('permits.leave.approve', $leave) }}" class="d-inline approve-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Setujui">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Tolak" onclick="rejectLeave({{ $leave->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
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
                        {{ $leaves->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-muted">Tidak ada pengajuan cuti yang menunggu persetujuan</h5>
                        <p class="text-muted">Semua pengajuan cuti sudah diproses.</p>
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
                    <button type="submit" class="btn btn-danger">Tolak Cuti</button>
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

            console.log('Form action:', this.action);
            console.log('Rejection reason:', reason);

            // Let the form submit normally
            return true;
        });
    }
});

function updateStats() {
    // Update pending count in statistics
    const pendingElements = document.querySelectorAll('tr');
    let pendingCount = 0;

    pendingElements.forEach(row => {
        const statusCell = row.cells && row.cells[6];
        if (statusCell && statusCell.textContent.includes('Pending')) {
            pendingCount++;
        }
    });

    // Update stat cards
    const statCards = document.querySelectorAll('.stat-card-warning h3');
    if (statCards.length > 0) {
        statCards[0].textContent = pendingCount;
    }
}
</script>
@endsection
