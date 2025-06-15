@extends('layouts.app')

@section('title', 'Personalia Dashboard')
@section('page-title', 'Personalia Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1">Dashboard Personalia</h3>
                        <p class="mb-0">Monitoring dan input data karyawan harian</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Metrics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($employees_count ?? 0) }}</h3>
                        <p class="text-muted mb-0">Total Karyawan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($attendance_today ?? 0) }}</h3>
                        <p class="text-muted mb-0">Hadir Hari Ini</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded p-3">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($absent_today ?? 0) }}</h3>
                        <p class="text-muted mb-0">Tidak Hadir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($late_today ?? 0) }}</h3>
                        <p class="text-muted mb-0">Terlambat</p>
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
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('attendance.index') }}" class="btn btn-primary w-100">
                            <i class="fas fa-clock me-2"></i>Input Absensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('employees.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-users me-2"></i>Data Karyawan
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('leaves.index') }}" class="btn btn-info w-100">
                            <i class="fas fa-calendar-times me-2"></i>Data Cuti
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('reports.attendance') }}" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i>Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Absensi Hari Ini</h5>
                <small class="text-muted">{{ now()->format('d F Y') }}</small>
            </div>
            <div class="card-body">
                @if(isset($recent_attendance) && count($recent_attendance) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_attendance as $attendance)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $attendance->user->full_name ?? 'N/A' }}</h6>
                                                <small class="text-muted">{{ $attendance->user->employee_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($attendance->clock_in)
                                            <span class="text-success">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</span>
                                            @if($attendance->late_minutes > 0)
                                                <br><small class="text-danger">Terlambat {{ $attendance->late_minutes }} menit</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->clock_out)
                                            <span class="text-info">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'present' => 'success',
                                                'late' => 'warning',
                                                'absent' => 'danger',
                                                'sick' => 'info',
                                                'leave' => 'secondary'
                                            ];
                                            $statusLabels = [
                                                'present' => 'Hadir',
                                                'late' => 'Terlambat',
                                                'absent' => 'Alpha',
                                                'sick' => 'Sakit',
                                                'leave' => 'Cuti'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$attendance->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$attendance->status] ?? ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h5>Belum ada data absensi hari ini</h5>
                        <p class="text-muted">Data absensi akan muncul setelah karyawan melakukan clock in</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Upcoming Leaves -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Cuti Mendatang</h5>
            </div>
            <div class="card-body">
                @if(isset($upcoming_leaves) && count($upcoming_leaves) > 0)
                    @foreach($upcoming_leaves as $leave)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-calendar text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $leave->user->full_name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $leave->leaveType->name ?? 'N/A' }}</small>
                            <br>
                            <small class="text-muted">
                                {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m') : 'N/A' }} - 
                                {{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d/m') : 'N/A' }}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-success">{{ $leave->days ?? 0 }} hari</span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Tidak ada cuti mendatang</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Attendance Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Kehadiran</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-success">{{ number_format((($attendance_today ?? 0) / max($employees_count ?? 1, 1)) * 100, 1) }}%</h4>
                            <small class="text-muted">Tingkat Kehadiran</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-warning">{{ number_format((($late_today ?? 0) / max($attendance_today ?? 1, 1)) * 100, 1) }}%</h4>
                        <small class="text-muted">Tingkat Keterlambatan</small>
                    </div>
                </div>
                
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: {{ (($attendance_today ?? 0) / max($employees_count ?? 1, 1)) * 100 }}%">
                    </div>
                </div>
                <small class="text-muted">{{ $attendance_today ?? 0 }} dari {{ $employees_count ?? 0 }} karyawan hadir</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if needed
    if ($('.table').length > 0) {
        $('.table').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "responsive": true
        });
    }
});
</script>
@endpush
