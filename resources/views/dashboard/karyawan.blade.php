@extends('layouts.app')

@section('title', 'Dashboard Karyawan')
@section('page-title', 'Dashboard Karyawan')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-secondary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1">Selamat Datang, {{ auth()->user()->first_name }}!</h3>
                        <p class="mb-0">Kelola absensi dan informasi pribadi Anda</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-circle fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Attendance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Absensi Hari Ini - {{ now()->format('d F Y') }}</h5>
            </div>
            <div class="card-body">
                @if(isset($today_attendance) && $today_attendance)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-success bg-opacity-10 rounded p-3 mb-2">
                                    <i class="fas fa-sign-in-alt fa-2x text-success"></i>
                                </div>
                                <h6>Clock In</h6>
                                <h4 class="text-success">{{ $today_attendance->clock_in ? \Carbon\Carbon::parse($today_attendance->clock_in)->format('H:i') : '-' }}</h4>
                                @if($today_attendance->late_minutes > 0)
                                    <small class="text-danger">Terlambat {{ $today_attendance->late_minutes }} menit</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-info bg-opacity-10 rounded p-3 mb-2">
                                    <i class="fas fa-sign-out-alt fa-2x text-info"></i>
                                </div>
                                <h6>Clock Out</h6>
                                <h4 class="text-info">{{ $today_attendance->clock_out ? \Carbon\Carbon::parse($today_attendance->clock_out)->format('H:i') : '-' }}</h4>
                                @if($today_attendance->early_leave_minutes > 0)
                                    <small class="text-warning">Pulang awal {{ $today_attendance->early_leave_minutes }} menit</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-primary bg-opacity-10 rounded p-3 mb-2">
                                    <i class="fas fa-hourglass-half fa-2x text-primary"></i>
                                </div>
                                <h6>Total Jam Kerja</h6>
                                <h4 class="text-primary">{{ number_format(($today_attendance->total_work_minutes ?? 0) / 60, 1) }} jam</h4>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            @if(!$today_attendance->clock_in)
                                <a href="{{ route('attendance.clock') }}" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Clock In
                                </a>
                            @elseif(!$today_attendance->clock_out)
                                <a href="{{ route('attendance.clock') }}" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-sign-out-alt me-2"></i>Clock Out
                                </a>
                            @else
                                <div class="alert alert-success text-center">
                                    <i class="fas fa-check-circle me-2"></i>Absensi hari ini sudah lengkap
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('attendance.clock') }}" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-clock me-2"></i>Kelola Absensi
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                        <h5>Belum ada absensi hari ini</h5>
                        <p class="text-muted mb-4">Silakan lakukan clock in untuk memulai hari kerja Anda</p>
                        <a href="{{ route('attendance.clock') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Clock In Sekarang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-calendar-check fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ count($monthly_attendance ?? []) }}</h3>
                        <p class="text-muted mb-0">Hari Kerja Bulan Ini</p>
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
                        <h3 class="mb-1">{{ $attendance_stats['late_count'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Terlambat Bulan Ini</p>
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
                            <i class="fas fa-calendar-times fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $leave_balance ?? 12 }}</h3>
                        <p class="text-muted mb-0">Sisa Cuti</p>
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
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ isset($recent_payroll) ? 'Ada' : 'Belum' }}</h3>
                        <p class="text-muted mb-0">Slip Gaji Terbaru</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-lg-8">
        <!-- Recent Payroll -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Slip Gaji Terbaru</h5>
            </div>
            <div class="card-body">
                @if(isset($recent_payroll) && $recent_payroll)
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Periode Gaji</h6>
                            <p>{{ $recent_payroll->payrollPeriod->name ?? 'N/A' }}</p>
                            
                            <h6 class="text-muted">Gaji Pokok</h6>
                            <p>Rp {{ number_format($recent_payroll->basic_salary ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Total Tunjangan</h6>
                            <p>Rp {{ number_format($recent_payroll->total_allowances ?? 0, 0, ',', '.') }}</p>
                            
                            <h6 class="text-muted">Gaji Bersih</h6>
                            <h4 class="text-success">Rp {{ number_format($recent_payroll->net_salary ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download Slip Gaji
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                        <h5>Belum ada slip gaji</h5>
                        <p class="text-muted">Slip gaji akan tersedia setelah proses payroll selesai</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Monthly Attendance Chart -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Kehadiran Bulanan</h5>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Recent Leaves -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Riwayat Cuti</h5>
                <a href="{{ route('leaves.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Ajukan
                </a>
            </div>
            <div class="card-body">
                @if(isset($recent_leaves) && count($recent_leaves) > 0)
                    @foreach($recent_leaves as $leave)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-calendar text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $leave->leaveType->name ?? 'N/A' }}</h6>
                            <small class="text-muted">
                                {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') : 'N/A' }} - 
                                {{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') : 'N/A' }}
                            </small>
                        </div>
                        <div>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$leave->status] ?? 'secondary' }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Belum ada riwayat cuti</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('attendance.clock') }}" class="btn btn-primary">
                        <i class="fas fa-clock me-2"></i>Kelola Absensi
                    </a>
                    <a href="{{ route('leaves.create') }}" class="btn btn-warning">
                        <i class="fas fa-calendar-times me-2"></i>Ajukan Cuti
                    </a>
                    <a href="{{ route('profile') }}" class="btn btn-info">
                        <i class="fas fa-user me-2"></i>Edit Profil
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Attendance Chart
const ctx = document.getElementById('attendanceChart').getContext('2d');
@php
    $attendanceData = $monthly_attendance ?? [];
    $attendanceLabels = !empty($attendanceData) ? array_column($attendanceData, 'date') : [];
    $attendanceHours = !empty($attendanceData) ? array_map(function($att) { return ($att['total_work_minutes'] ?? 0) / 60; }, $attendanceData) : [];
@endphp
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($attendanceLabels),
        datasets: [{
            label: 'Jam Kerja',
            data: @json($attendanceHours),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 10,
                ticks: {
                    callback: function(value) {
                        return value + ' jam';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Jam Kerja: ' + context.parsed.y.toFixed(1) + ' jam';
                    }
                }
            }
        }
    }
});
</script>
@endpush
