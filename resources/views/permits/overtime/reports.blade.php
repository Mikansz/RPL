@extends('layouts.app')

@section('title', 'Laporan Lembur')

@section('content')
<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('permits.overtime.reports') }}">
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
    <div class="col-md-2">
        <div class="card stat-card-primary">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-3"></i>
                <h3 class="mb-1">{{ number_format($summary['total_hours'], 1) }}</h3>
                <p class="mb-0">Total Jam</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card-dark">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x mb-3"></i>
                <h3 class="mb-1">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h3>
                <p class="mb-0">Total Nominal</p>
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
                    <i class="fas fa-chart-bar"></i> Laporan Detail Lembur
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
                @if($overtimes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="overtimeTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Karyawan</th>
                                    <th>Tanggal Lembur</th>
                                    <th>Waktu</th>
                                    <th>Durasi (Jam)</th>
                                    <th>Pekerjaan</th>
                                    <th>Status</th>
                                    <th>Nominal</th>
                                    <th>Disetujui Oleh</th>
                                    <th>Tanggal Disetujui</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overtimes as $index => $overtime)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $overtime->user->employee->full_name ?? $overtime->user->first_name . ' ' . $overtime->user->last_name }}</strong><br>
                                            <small class="text-muted">{{ $overtime->user->employee->employee_id ?? $overtime->user->username }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $overtime->overtime_date->format('d M Y') }}</td>
                                    <td>{{ $overtime->start_time }} - {{ $overtime->end_time }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ number_format($overtime->planned_hours, 1) }}</strong>
                                            @if($overtime->actual_hours && $overtime->actual_hours != $overtime->planned_hours)
                                                <br><small class="text-info">(Aktual: {{ number_format($overtime->actual_hours, 1) }})</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px;">
                                            {{ Str::limit($overtime->work_description, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($overtime->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($overtime->status === 'approved')
                                            <span class="badge bg-success">Disetujui</span>
                                        @elseif($overtime->status === 'rejected')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($overtime->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($overtime->overtime_amount)
                                            <strong>Rp {{ number_format($overtime->overtime_amount, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($overtime->approvedBy)
                                            {{ $overtime->approvedBy->first_name }} {{ $overtime->approvedBy->last_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($overtime->approved_at)
                                            {{ $overtime->approved_at->format('d M Y H:i') }}
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
                                    <th>{{ number_format($summary['total_hours'], 1) }} jam</th>
                                    <th colspan="2"></th>
                                    <th>Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</th>
                                    <th colspan="2"></th>
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
                                        <th>Total Jam</th>
                                        <th>Total Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $employeeSummary = $overtimes->groupBy('user_id')->map(function($group) {
                                            return [
                                                'user' => $group->first()->user,
                                                'total_requests' => $group->count(),
                                                'approved_requests' => $group->where('status', 'approved')->count(),
                                                'total_hours' => $group->where('status', 'approved')->sum('actual_hours') ?: $group->where('status', 'approved')->sum('planned_hours'),
                                                'total_amount' => $group->where('status', 'approved')->sum('overtime_amount'),
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
                                        <td>{{ number_format($summary['total_hours'], 1) }} jam</td>
                                        <td>Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data lembur</h5>
                        <p class="text-muted">Tidak ada data lembur pada periode yang dipilih.</p>
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
