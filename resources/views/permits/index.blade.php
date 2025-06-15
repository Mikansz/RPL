@extends('layouts.app')

@section('title', 'Izin & Cuti')
@section('page-title', 'Izin & Cuti')

@section('content')
<!-- Summary Cards -->
<div class="row mb-4">

    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <i class="fas fa-clock fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $pending_overtime ?? 0 }}</h3>
                        <p class="text-muted mb-0">Lembur Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-calendar-times fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $pending_leaves ?? 0 }}</h3>
                        <p class="text-muted mb-0">Cuti Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-hourglass-half fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($monthly_overtime_hours ?? 0, 1) }}</h3>
                        <p class="text-muted mb-0">Jam Lembur Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="{{ route('permits.leave.create') }}" class="btn btn-success w-100">
                    <i class="fas fa-calendar-plus me-2"></i>Ajukan Cuti
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('permits.overtime.create') }}" class="btn btn-info w-100">
                    <i class="fas fa-clock me-2"></i>Ajukan Lembur
                </a>
            </div>

            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-primary w-100" onclick="viewHistory()">
                    <i class="fas fa-history me-2"></i>Riwayat
                </button>
            </div>

            @if(auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR', 'Manager']) || auth()->user()->hasPermission('overtime.approve'))
            <div class="col-md-3 mb-2">
                <a href="{{ route('permits.overtime.pending') }}" class="btn btn-warning w-100">
                    <i class="fas fa-clock me-2"></i>Persetujuan Lembur
                    @php
                        $pendingCount = \App\Models\OvertimeRequest::where('status', 'pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-light text-dark ms-2">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">

    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Lembur Terbaru</h5>
                <a href="{{ route('permits.overtime.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                @if(isset($recent_overtime) && count($recent_overtime) > 0)
                    @foreach($recent_overtime as $overtime)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-clock text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $overtime->overtime_date ? $overtime->overtime_date->format('d/m/Y') : 'N/A' }}</h6>
                            <small class="text-muted">{{ $overtime->planned_hours ?? 0 }} jam direncanakan</small>
                            <br>
                            <span class="badge bg-{{ $overtime->status === 'approved' ? 'success' : ($overtime->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($overtime->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Belum ada lembur</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Cuti Terbaru</h5>
                <a href="{{ route('permits.leave.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                @if(isset($recent_leaves) && count($recent_leaves) > 0)
                    @foreach($recent_leaves as $leave)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-calendar-times text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $leave->leaveType->name ?? 'N/A' }}</h6>
                            <small class="text-muted">
                                {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m') : 'N/A' }} - 
                                {{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d/m') : 'N/A' }}
                            </small>
                            <br>
                            <span class="badge bg-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Belum ada cuti</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan Bulanan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Statistik Cuti</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-primary">{{ $yearly_leave_days ?? 0 }}</h4>
                                    <small class="text-muted">Hari Cuti Tahun Ini</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-success">{{ 12 - ($yearly_leave_days ?? 0) }}</h4>
                                    <small class="text-muted">Sisa Cuti</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Statistik Lembur</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-info">{{ number_format($monthly_overtime_hours ?? 0, 1) }}</h4>
                                    <small class="text-muted">Jam Lembur Bulan Ini</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-warning">Rp {{ number_format(($monthly_overtime_hours ?? 0) * 25000, 0, ',', '.') }}</h4>
                                    <small class="text-muted">Estimasi Upah Lembur</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewHistory() {
    // Implementation would show a modal or redirect to history page
    alert('Fitur riwayat akan diimplementasikan');
}

$(document).ready(function() {
    // Auto-refresh data every 5 minutes
    setInterval(function() {
        // Implementation would refresh the data
        console.log('Auto-refresh data');
    }, 300000); // 5 minutes
});
</script>
@endpush
