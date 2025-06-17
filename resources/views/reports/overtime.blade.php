@extends('layouts.app')

@section('title', 'Laporan Lembur')
@section('page-title', 'Laporan Lembur & Overtime')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-check fa-2x text-success"></i>
                </div>
                <h4 class="mb-1 text-success">{{ number_format($stats['total_approved'] ?? 0) }}</h4>
                <small class="text-muted">Lembur Disetujui</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
                <h4 class="mb-1 text-warning">{{ number_format($stats['total_pending'] ?? 0) }}</h4>
                <small class="text-muted">Menunggu Persetujuan</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-times fa-2x text-danger"></i>
                </div>
                <h4 class="mb-1 text-danger">{{ number_format($stats['total_rejected'] ?? 0) }}</h4>
                <small class="text-muted">Lembur Ditolak</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-business-time fa-2x text-info"></i>
                </div>
                <h4 class="mb-1 text-info">{{ number_format($stats['total_hours'] ?? 0, 1) }}</h4>
                <small class="text-muted">Total Jam Lembur</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.overtime') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label for="department_id" class="form-label">Departemen</label>
                <select class="form-select" id="department_id" name="department_id">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('reports.export', 'overtime') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&department_id={{ $departmentId }}" 
                   class="btn btn-success">
                    <i class="fas fa-download me-2"></i>Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Overtime Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-business-time me-2"></i>Data Lembur & Overtime</h5>
    </div>
    <div class="card-body">
        @if($overtimes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Karyawan</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Total Jam</th>
                            <th>Status</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overtimes as $overtime)
                        <tr>
                            <td>{{ $overtime->user->employee_id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $overtime->user->full_name }}</h6>
                                        <small class="text-muted">{{ $overtime->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $overtime->user->employee->department->name ?? 'N/A' }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}</td>
                            <td>
                                <span class="badge bg-info">{{ number_format($overtime->total_hours, 1) }} jam</span>
                            </td>
                            <td>
                                @switch($overtime->status)
                                    @case('approved')
                                        <span class="badge bg-success">Disetujui</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($overtime->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <small>{{ Str::limit($overtime->reason, 50) }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $overtimes->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-business-time fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data lembur</h5>
                <p class="text-muted">Tidak ada data lembur untuk periode yang dipilih</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}
</style>
@endpush
