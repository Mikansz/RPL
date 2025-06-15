@extends('layouts.app')

@section('title', 'HRD Dashboard')
@section('page-title', 'HRD Dashboard - Human Resources')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-info text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1">Dashboard HRD</h3>
                        <p class="mb-0">Kelola sumber daya manusia dan operasional HR</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users-cog fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HR Metrics -->
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
                        <h3 class="mb-1">{{ number_format($total_employees ?? 0) }}</h3>
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
                            <i class="fas fa-user-plus fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($new_employees_this_month ?? 0) }}</h3>
                        <p class="text-muted mb-0">Karyawan Baru Bulan Ini</p>
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
                            <i class="fas fa-calendar-times fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($pending_leaves ?? 0) }}</h3>
                        <p class="text-muted mb-0">Pengajuan Cuti Pending</p>
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
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <i class="fas fa-clock fa-2x text-info"></i>
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
                        <a href="{{ route('employees.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>Tambah Karyawan
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('users.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-users me-2"></i>Kelola User
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('attendance.index') }}" class="btn btn-info w-100">
                            <i class="fas fa-clock me-2"></i>Data Absensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('reports.hr') }}" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i>Laporan HR
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
                <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Pengajuan Cuti Pending</h5>
                <span class="badge bg-warning">{{ count($leave_requests ?? []) }}</span>
            </div>
            <div class="card-body">
                @if(isset($leave_requests) && count($leave_requests) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal</th>
                                    <th>Durasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leave_requests as $leave)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $leave->user->full_name ?? 'N/A' }}</h6>
                                                <small class="text-muted">{{ $leave->user->employee->position->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') : 'N/A' }} - 
                                        {{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>{{ $leave->days ?? 0 }} hari</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button class="btn btn-info" title="Detail">
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
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>Tidak ada pengajuan cuti pending</h5>
                        <p class="text-muted">Semua pengajuan cuti telah diproses</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Recent Employees -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Karyawan Terbaru</h5>
            </div>
            <div class="card-body">
                @if(isset($recent_employees) && count($recent_employees) > 0)
                    @foreach($recent_employees as $employee)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-user text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $employee->user->full_name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $employee->position->name ?? 'N/A' }}</small>
                            <br>
                            <small class="text-muted">{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') : 'N/A' }}</small>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Belum ada karyawan baru</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Attendance Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Ringkasan Absensi</h5>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Attendance Summary Chart
const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
new Chart(attendanceCtx, {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
        datasets: [{
            data: [
                {{ $attendance_summary['present'] ?? 85 }},
                {{ $attendance_summary['leave'] ?? 8 }},
                {{ $attendance_summary['sick'] ?? 5 }},
                {{ $attendance_summary['absent'] ?? 2 }}
            ],
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#17a2b8',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
