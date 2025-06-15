@extends('layouts.app')

@section('title', 'Data Cuti')
@section('page-title', 'Data Cuti & Izin')

@section('content')
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="{{ route('leaves.create') }}" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Ajukan Cuti
                </a>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-success w-100" onclick="approveSelected()">
                    <i class="fas fa-check me-2"></i>Approve Selected
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-danger w-100" onclick="rejectSelected()">
                    <i class="fas fa-times me-2"></i>Reject Selected
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-info w-100" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('leaves.index') }}">
            <div class="row">
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="{{ request('date_to', now()->endOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="leave_type" class="form-label">Jenis Cuti</label>
                    <select class="form-select" name="leave_type">
                        <option value="">Semua Jenis</option>
                        <option value="annual" {{ request('leave_type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                        <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                        <option value="maternity" {{ request('leave_type') == 'maternity' ? 'selected' : '' }}>Melahirkan</option>
                        <option value="emergency" {{ request('leave_type') == 'emergency' ? 'selected' : '' }}>Darurat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Leaves Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Daftar Pengajuan Cuti ({{ $leaves->total() }})</h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label" for="selectAll">
                    Pilih Semua
                </label>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($leaves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllHeader">
                            </th>
                            <th>Karyawan</th>
                            <th>Jenis Cuti</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td>
                                <input type="checkbox" class="leave-checkbox" value="{{ $leave->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $leave->user->full_name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $leave->user->employee_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $leave->leaveType->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <strong>{{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') : 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('l') : '' }}</small>
                            </td>
                            <td>
                                <strong>{{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') : 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('l') : '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $leave->days ?? 0 }} hari</span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$leave->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$leave->status] ?? ucfirst($leave->status) }}
                                </span>
                                @if($leave->status === 'approved' && $leave->approvedBy)
                                    <br><small class="text-muted">oleh {{ $leave->approvedBy->full_name }}</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $leave->created_at ? $leave->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                <br><small class="text-muted">{{ $leave->created_at ? $leave->created_at->diffForHumans() : '' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewDetail({{ $leave->id }})" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($leave->status === 'pending' && auth()->user()->hasPermission('leaves.approve'))
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="approveLeave({{ $leave->id }})" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="rejectLeave({{ $leave->id }})" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                    @if($leave->user_id === auth()->id() && $leave->status === 'pending')
                                    <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $leaves->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5>Tidak ada pengajuan cuti</h5>
                <p class="text-muted">Belum ada pengajuan cuti untuk periode yang dipilih.</p>
                <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Ajukan Cuti Pertama
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $leaves->where('status', 'pending')->count() }}</h4>
                        <p class="mb-0">Pending</p>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $leaves->where('status', 'approved')->count() }}</h4>
                        <p class="mb-0">Approved</p>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $leaves->where('status', 'rejected')->count() }}</h4>
                        <p class="mb-0">Rejected</p>
                    </div>
                    <div>
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $leaves->sum('days') }}</h4>
                        <p class="mb-0">Total Hari</p>
                    </div>
                    <div>
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengajuan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewDetail(leaveId) {
    $('#detailContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    
    // Simulate loading detail
    setTimeout(() => {
        $('#detailContent').html('<p>Detail pengajuan cuti akan ditampilkan di sini</p>');
    }, 1000);
}

function approveLeave(leaveId) {
    if (confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti ini?')) {
        // Implementation would go here
        alert('Fitur approve akan diimplementasikan');
    }
}

function rejectLeave(leaveId) {
    if (confirm('Apakah Anda yakin ingin menolak pengajuan cuti ini?')) {
        // Implementation would go here
        alert('Fitur reject akan diimplementasikan');
    }
}

function approveSelected() {
    const selected = $('.leave-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Pilih pengajuan yang ingin disetujui');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin menyetujui ${selected.length} pengajuan cuti?`)) {
        alert('Fitur bulk approve akan diimplementasikan');
    }
}

function rejectSelected() {
    const selected = $('.leave-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Pilih pengajuan yang ingin ditolak');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin menolak ${selected.length} pengajuan cuti?`)) {
        alert('Fitur bulk reject akan diimplementasikan');
    }
}

function exportData() {
    alert('Fitur export akan diimplementasikan');
}

$(document).ready(function() {
    // Select all functionality
    $('#selectAll, #selectAllHeader').change(function() {
        $('.leave-checkbox').prop('checked', this.checked);
    });
    
    $('.leave-checkbox').change(function() {
        const total = $('.leave-checkbox').length;
        const checked = $('.leave-checkbox:checked').length;
        
        $('#selectAll, #selectAllHeader').prop('checked', total === checked);
        $('#selectAll, #selectAllHeader').prop('indeterminate', checked > 0 && checked < total);
    });
    
    // Auto-submit form on filter change
    $('select[name="status"], select[name="leave_type"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
