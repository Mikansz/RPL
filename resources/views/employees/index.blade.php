@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari nama atau employee ID..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="department">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resign</option>
                        <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Pensiun</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('employees.create') }}" class="btn btn-success w-100">
                        <i class="fas fa-plus me-1"></i>Tambah Karyawan
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Employees Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Daftar Karyawan ({{ $employees->total() }})</h5>
    </div>
    <div class="card-body">
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Posisi</th>
                            <th>Tanggal Bergabung</th>
                            <th>Status</th>
                            <th>Gaji Pokok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $employee->user->employee_id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $employee->user->full_name }}</h6>
                                        <small class="text-muted">{{ $employee->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->department->name ?? 'N/A' }}</td>
                            <td>{{ $employee->position->name ?? 'N/A' }}</td>
                            <td>
                                @if($employee->hire_date)
                                    {{ $employee->hire_date->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ $employee->hire_date->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'resigned' => 'warning',
                                        'terminated' => 'danger',
                                        'retired' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Aktif',
                                        'resigned' => 'Resign',
                                        'terminated' => 'Terminated',
                                        'retired' => 'Pensiun'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$employee->employment_status] ?? 'secondary' }}">
                                    {{ $statusLabels[$employee->employment_status] ?? ucfirst($employee->employment_status) }}
                                </span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($employee->position->base_salary ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete({{ $employee->id }}, '{{ $employee->user->full_name }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                <h5>Tidak ada karyawan ditemukan</h5>
                <p class="text-muted">Belum ada karyawan yang terdaftar atau sesuai dengan filter yang dipilih.</p>
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Karyawan Pertama
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $employees->where('employment_status', 'active')->count() }}</h4>
                        <p class="mb-0">Karyawan Aktif</p>
                    </div>
                    <div>
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                        <h4>{{ $employees->where('employment_status', 'resigned')->count() }}</h4>
                        <p class="mb-0">Resign</p>
                    </div>
                    <div>
                        <i class="fas fa-user-clock fa-2x opacity-75"></i>
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
                        <h4>{{ $employees->where('employment_status', 'terminated')->count() }}</h4>
                        <p class="mb-0">Terminated</p>
                    </div>
                    <div>
                        <i class="fas fa-user-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $employees->where('employment_status', 'retired')->count() }}</h4>
                        <p class="mb-0">Pensiun</p>
                    </div>
                    <div>
                        <i class="fas fa-user-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Total Statistics -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $employees->count() }}</h4>
                        <p class="mb-0">Total Karyawan</p>
                    </div>
                    <div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data karyawan <strong id="employeeName"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(employeeId, employeeName) {
    document.getElementById('employeeName').textContent = employeeName;
    document.getElementById('deleteForm').action = `/employees/${employeeId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

$(document).ready(function() {
    // Auto-submit form on filter change
    $('select[name="department"], select[name="status"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
