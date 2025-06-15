@extends('layouts.app')

@section('title', 'Laporan Cuti')

@section('content')
<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('permits.leave.reports') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Tampilkan Laporan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card stat-card-info">
            <div class="card-body text-center">
                <i class="fas fa-list fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $summary['total_requests'] }}</h3>
                <p class="mb-0">Total Pengajuan</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card-success">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $summary['total_approved'] }}</h3>
                <p class="mb-0">Disetujui</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card-warning">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-half fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $summary['total_pending'] }}</h3>
                <p class="mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card-danger">
            <div class="card-body text-center">
                <i class="fas fa-times-circle fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $summary['total_rejected'] }}</h3>
                <p class="mb-0">Ditolak</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card-primary">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $summary['total_days'] }}</h3>
                <p class="mb-0">Total Hari Cuti</p>
            </div>
        </div>
    </div>
</div>

<!-- Summary by Leave Type -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Ringkasan per Jenis Cuti</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Jenis Cuti</th>
                                <th>Total Pengajuan</th>
                                <th>Disetujui</th>
                                <th>Total Hari</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveTypeSummary as $summary)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ $summary['leave_type']->name }}</span>
                                </td>
                                <td>{{ $summary['total_requests'] }}</td>
                                <td>{{ $summary['approved_requests'] }}</td>
                                <td>{{ $summary['total_days'] }} hari</td>
                                <td>
                                    @if($summary['total_requests'] > 0)
                                        {{ number_format(($summary['approved_requests'] / $summary['total_requests']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Report Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Laporan Detail Cuti
                    <small class="text-muted">({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})</small>
                </h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($leaves->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="leaveTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Karyawan</th>
                                    <th>Jenis Cuti</th>
                                    <th>Periode</th>
                                    <th>Durasi</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Disetujui Oleh</th>
                                    <th>Tanggal Disetujui</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $index => $leave)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $leave->user->employee->full_name ?? $leave->user->first_name . ' ' . $leave->user->last_name }}</strong><br>
                                            <small class="text-muted">{{ $leave->user->employee->employee_id ?? $leave->user->username }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $leave->leaveType->name }}</span>
                                    </td>
                                    <td>
                                        {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $leave->total_days }} hari</strong>
                                            @if($leave->is_half_day)
                                                <br><small class="text-info">({{ ucfirst($leave->half_day_type) }})</small>
                                            @endif
                                        </div>
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
                                        @if($leave->approvedBy)
                                            {{ $leave->approvedBy->first_name }} {{ $leave->approvedBy->last_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->approved_at)
                                            {{ $leave->approved_at->format('d M Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th colspan="4">Total</th>
                                    <th>{{ $summary['total_days'] }} hari</th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Summary by Employee -->
                    <div class="mt-4">
                        <h6><i class="fas fa-users me-2"></i>Ringkasan per Karyawan</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Karyawan</th>
                                        <th>Total Pengajuan</th>
                                        <th>Disetujui</th>
                                        <th>Total Hari</th>
                                        <th>Jenis Cuti Terbanyak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $employeeSummary = $leaves->groupBy('user_id')->map(function($group) {
                                            $mostUsedType = $group->where('status', 'approved')
                                                                 ->groupBy('leave_type_id')
                                                                 ->map(function($typeGroup) {
                                                                     return [
                                                                         'type' => $typeGroup->first()->leaveType,
                                                                         'count' => $typeGroup->count(),
                                                                         'days' => $typeGroup->sum('total_days')
                                                                     ];
                                                                 })
                                                                 ->sortByDesc('days')
                                                                 ->first();
                                            
                                            return [
                                                'user' => $group->first()->user,
                                                'total_requests' => $group->count(),
                                                'approved_requests' => $group->where('status', 'approved')->count(),
                                                'total_days' => $group->where('status', 'approved')->sum('total_days'),
                                                'most_used_type' => $mostUsedType,
                                            ];
                                        });
                                    @endphp
                                    @foreach($employeeSummary as $summary)
                                    <tr>
                                        <td>
                                            <strong>{{ $summary['user']->employee->full_name ?? $summary['user']->first_name . ' ' . $summary['user']->last_name }}</strong><br>
                                            <small class="text-muted">{{ $summary['user']->employee->employee_id ?? $summary['user']->username }}</small>
                                        </td>
                                        <td>{{ $summary['total_requests'] }}</td>
                                        <td>{{ $summary['approved_requests'] }}</td>
                                        <td>{{ $summary['total_days'] }} hari</td>
                                        <td>
                                            @if($summary['most_used_type'])
                                                <span class="badge bg-info">{{ $summary['most_used_type']['type']->name }}</span>
                                                <small class="text-muted">({{ $summary['most_used_type']['days'] }} hari)</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data cuti</h5>
                        <p class="text-muted">Tidak ada data cuti pada periode yang dipilih.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    // Implementation for Excel export
    alert('Fitur export Excel akan segera tersedia');
}

function exportToPDF() {
    // Implementation for PDF export
    alert('Fitur export PDF akan segera tersedia');
}
</script>
@endsection
