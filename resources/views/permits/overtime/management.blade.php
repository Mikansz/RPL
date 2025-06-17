@extends('layouts.app')

@section('title', 'Manajemen Lembur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Manajemen Lembur</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permits.index') }}">Permits</a></li>
                        <li class="breadcrumb-item active">Manajemen Lembur</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Pengajuan</p>
                            <h4 class="mb-2">{{ $stats['total'] }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="fas fa-clock font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Menunggu Persetujuan</p>
                            <h4 class="mb-2 text-warning">{{ $stats['pending'] }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="fas fa-hourglass-half font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Disetujui</p>
                            <h4 class="mb-2 text-success">{{ $stats['approved'] }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="fas fa-check-circle font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Ditolak</p>
                            <h4 class="mb-2 text-danger">{{ $stats['rejected'] }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-danger rounded-3">
                                <i class="fas fa-times-circle font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Jam Lembur (Disetujui)</p>
                            <h4 class="mb-2 text-info">{{ number_format($stats['total_hours'] ?? 0, 1) }} Jam</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="fas fa-stopwatch font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Nilai Lembur</p>
                            <h4 class="mb-2 text-success">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="fas fa-money-bill-wave font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('permits.overtime.pending') }}" class="btn btn-warning w-100">
                                <i class="fas fa-hourglass-half me-2"></i>Pending Approval
                                @if($stats['pending'] > 0)
                                    <span class="badge bg-light text-dark ms-2">{{ $stats['pending'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('permits.overtime.reports') }}" class="btn btn-info w-100">
                                <i class="fas fa-chart-bar me-2"></i>Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-secondary w-100" onclick="exportData()">
                                <i class="fas fa-download me-2"></i>Export Data
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-primary w-100" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Requests Table -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">Semua Pengajuan Lembur</h5>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus()">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari karyawan..." onkeyup="searchTable()">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="overtimeTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Karyawan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Durasi</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $index => $overtime)
                        <tr>
                            <td>{{ $overtimes->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-3">
                                        <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                            {{ substr($overtime->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $overtime->user->name }}</h6>
                                        <small class="text-muted">{{ $overtime->user->employee->employee_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $overtime->overtime_date->format('d/m/Y') }}</td>
                            <td>{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</td>
                            <td>{{ $overtime->planned_hours }} jam</td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $overtime->work_description }}">
                                    {{ $overtime->work_description }}
                                </span>
                            </td>
                            <td>
                                @if($overtime->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($overtime->status === 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @elseif($overtime->status === 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($overtime->approvedBy)
                                    <small>{{ $overtime->approvedBy->name }}</small><br>
                                    <small class="text-muted">{{ $overtime->approved_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Aksi
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('permits.overtime.show', $overtime) }}">
                                            <i class="fas fa-eye me-2"></i>Detail
                                        </a></li>
                                        @if($overtime->status === 'pending')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-success" href="#" onclick="approveOvertime({{ $overtime->id }})">
                                            <i class="fas fa-check me-2"></i>Setujui
                                        </a></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="rejectOvertime({{ $overtime->id }})">
                                            <i class="fas fa-times me-2"></i>Tolak
                                        </a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada pengajuan lembur</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($overtimes->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $overtimes->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function filterByStatus() {
    const filter = document.getElementById('statusFilter').value;
    const table = document.getElementById('overtimeTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const statusCell = rows[i].getElementsByTagName('td')[6];
        if (statusCell) {
            const statusText = statusCell.textContent.toLowerCase();
            if (filter === '' || statusText.includes(filter)) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}

function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('overtimeTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const nameCell = rows[i].getElementsByTagName('td')[1];
        if (nameCell) {
            const nameText = nameCell.textContent.toLowerCase();
            if (nameText.includes(filter)) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}

function approveOvertime(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui pengajuan lembur ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permits/overtime/${id}/approve`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectOvertime(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason && reason.trim() !== '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/permits/overtime/${id}/reject`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason;
        
        form.appendChild(csrfToken);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function exportData() {
    alert('Fitur export akan segera tersedia');
}

function refreshData() {
    window.location.reload();
}
</script>
@endsection
