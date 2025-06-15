@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan - ' . $employee->user->full_name)

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($employee->user->profile_photo)
                        <img src="{{ asset($employee->user->profile_photo) }}" alt="Profile Photo" 
                             class="rounded-circle" width="120" height="120" style="object-fit: cover;">
                    @else
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-primary"></i>
                        </div>
                    @endif
                </div>
                
                <h4 class="mb-1">{{ $employee->user->full_name }}</h4>
                <p class="text-muted mb-2">{{ $employee->position->name ?? 'N/A' }}</p>
                <p class="text-muted mb-3">{{ $employee->department->name ?? 'N/A' }}</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted">Employee ID</h6>
                        <p class="mb-0">{{ $employee->user->employee_id }}</p>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($employee->employment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Karyawan
                    </a>
                    <a href="{{ route('users.show', $employee->user) }}" class="btn btn-info">
                        <i class="fas fa-user me-2"></i>Lihat User
                    </a>
                    <button type="button" class="btn btn-primary" onclick="viewAttendance()">
                        <i class="fas fa-clock me-2"></i>Riwayat Absensi
                    </button>
                    <button type="button" class="btn btn-success" onclick="viewPayroll()">
                        <i class="fas fa-money-bill-wave me-2"></i>Riwayat Gaji
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Employment Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Informasi Kepegawaian</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Departemen:</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Posisi:</td>
                                <td>{{ $employee->position->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Bergabung:</td>
                                <td>{{ $employee->hire_date ? $employee->hire_date->format('d F Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Masa Kerja:</td>
                                <td>{{ $employee->hire_date ? $employee->hire_date->diffForHumans() : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Status Karyawan:</td>
                                <td>
                                    <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($employee->employment_status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Supervisor:</td>
                                <td>{{ $employee->supervisor->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mulai Kontrak:</td>
                                <td>{{ $employee->contract_start ? $employee->contract_start->format('d F Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Selesai Kontrak:</td>
                                <td>{{ $employee->contract_end ? $employee->contract_end->format('d F Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Salary Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Informasi Gaji</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-primary">Rp {{ number_format($employee->position->base_salary ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Gaji Pokok</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-success">Rp {{ number_format($employee->allowances ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Tunjangan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-info">Rp {{ number_format(($employee->position->base_salary ?? 0) + ($employee->allowances ?? 0), 0, ',', '.') }}</h4>
                            <small class="text-muted">Total Gaji</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td>{{ $employee->user->email }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nomor Telepon:</td>
                                <td>{{ $employee->user->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Kelamin:</td>
                                <td>{{ $employee->user->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Lahir:</td>
                                <td>{{ $employee->user->birth_date ? $employee->user->birth_date->format('d F Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Alamat:</td>
                                <td>{{ $employee->user->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Username:</td>
                                <td>{{ $employee->user->username }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Login:</td>
                                <td>{{ $employee->user->last_login_at ? $employee->user->last_login_at->format('d F Y H:i') : 'Belum pernah' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Terdaftar:</td>
                                <td>{{ $employee->created_at->format('d F Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notes -->
        @if($employee->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Catatan</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $employee->notes }}</p>
            </div>
        </div>
        @endif
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Absensi Bulan Ini</h6>
                        <div class="d-flex justify-content-between">
                            <span>Hadir:</span>
                            <span class="text-success">{{ $attendance_stats['present'] ?? 0 }} hari</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Terlambat:</span>
                            <span class="text-warning">{{ $attendance_stats['late'] ?? 0 }} hari</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Alpha:</span>
                            <span class="text-danger">{{ $attendance_stats['absent'] ?? 0 }} hari</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Cuti & Izin</h6>
                        <div class="d-flex justify-content-between">
                            <span>Sisa Cuti:</span>
                            <span class="text-info">{{ $leave_balance ?? 12 }} hari</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Cuti Terpakai:</span>
                            <span class="text-secondary">{{ $used_leave_days ?? 0 }} hari</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Pending Approval:</span>
                            <span class="text-warning">{{ $pending_leaves ?? 0 }} pengajuan</span>
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
function viewAttendance() {
    // Redirect to attendance page with employee filter
    window.location.href = `{{ route('attendance.index') }}?user_id={{ $employee->user->id }}`;
}

function viewPayroll() {
    // Redirect to payroll page with employee filter
    window.location.href = `{{ route('payroll.index') }}?user_id={{ $employee->user->id }}`;
}
</script>
@endpush
