@extends('layouts.app')

@section('title', 'Data Absensi')
@section('page-title', 'Data Absensi')

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.index') }}">
            <div class="row">
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                @if(!auth()->user()->hasRole('karyawan'))
                <div class="col-md-3">
                    <label for="user_id" class="form-label">Karyawan</label>
                    <select class="form-select" name="user_id">
                        <option value="">Semua Karyawan</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }} ({{ $user->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Alpha</option>
                        <option value="sick" {{ request('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                        <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Cuti</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="{{ route('attendance.clock') }}" class="btn btn-success w-100">
                    <i class="fas fa-clock me-2"></i>Clock In/Out
                </a>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-info w-100" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export Excel
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-warning w-100" onclick="generateReport()">
                    <i class="fas fa-chart-bar me-2"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Data Absensi ({{ $attendances->total() }})</h5>
    </div>
    <div class="card-body">
        @if($attendances->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Jam Kerja</th>

                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td>
                                <strong>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') : 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('l') : '' }}</small>
                            </td>
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
                                    @if($attendance->early_leave_minutes > 0)
                                        <br><small class="text-warning">Pulang awal {{ $attendance->early_leave_minutes }} menit</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format(($attendance->total_work_minutes ?? 0) / 60, 1) }} jam</strong>
                                @if($attendance->overtime_minutes > 0)
                                    <br><small class="text-primary">Lembur {{ $attendance->overtime_minutes }} menit</small>
                                @endif
                            </td>

                            <td>
                                @php
                                    $statusColors = [
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'early_leave' => 'info',
                                        'absent' => 'danger',
                                        'sick' => 'secondary',
                                        'leave' => 'primary',
                                        'holiday' => 'dark',
                                        'half_day' => 'light'
                                    ];
                                    $statusLabels = [
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        'early_leave' => 'Pulang Awal',
                                        'absent' => 'Alpha',
                                        'sick' => 'Sakit',
                                        'leave' => 'Cuti',
                                        'holiday' => 'Libur',
                                        'half_day' => 'Setengah Hari'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$attendance->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$attendance->status] ?? ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewDetail({{ $attendance->id }})" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(auth()->user()->hasPermission('attendance.edit'))
                                    <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-warning" title="Edit">
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
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $attendances->firstItem() }} - {{ $attendances->lastItem() }} dari {{ $attendances->total() }} data
                </div>
                <div>
                    {{ $attendances->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data absensi</h5>
                <p class="text-muted">Belum ada data absensi untuk periode yang dipilih.</p>
                <a href="{{ route('attendance.clock') }}" class="btn btn-primary">
                    <i class="fas fa-clock me-2"></i>Mulai Absensi
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $attendances->where('status', 'present')->count() + $attendances->where('status', 'late')->count() }}</h4>
                        <p class="mb-0">Total Hadir</p>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $attendances->where('status', 'late')->count() }}</h4>
                        <p class="mb-0">Terlambat</p>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
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
                        <h4>{{ $attendances->where('status', 'absent')->count() }}</h4>
                        <p class="mb-0">Alpha</p>
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
                        <h4>{{ $attendances->whereIn('status', ['sick', 'leave'])->count() }}</h4>
                        <p class="mb-0">Izin/Sakit</p>
                    </div>
                    <div>
                        <i class="fas fa-calendar-times fa-2x opacity-75"></i>
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
                <h5 class="modal-title">Detail Absensi</h5>
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
function viewDetail(attendanceId) {
    // Implementation would load attendance detail via AJAX
    $('#detailContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    
    // Simulate loading detail
    setTimeout(() => {
        $('#detailContent').html('<p>Detail absensi akan ditampilkan di sini</p>');
    }, 1000);
}

function exportData() {
    alert('Fitur export akan diimplementasikan');
}

function generateReport() {
    alert('Fitur generate report akan diimplementasikan');
}

$(document).ready(function() {
    // Auto-submit form on filter change
    $('select[name="user_id"], select[name="status"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
