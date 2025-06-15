@extends('layouts.app')

@section('title', 'Departemen')
@section('page-title', 'Manajemen Departemen')

@section('content')
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            @if(auth()->user()->hasPermission('departments.create'))
            <div class="col-md-3">
                <a href="{{ route('departments.create') }}" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Tambah Departemen
                </a>
            </div>
            @endif
            <div class="col-md-3">
                <button type="button" class="btn btn-success w-100" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export Excel
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-warning w-100" onclick="generateReport()">
                    <i class="fas fa-chart-bar me-2"></i>Laporan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('departments.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari nama departemen..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text">Show</span>
                        <select class="form-select" name="per_page" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Departments Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Daftar Departemen ({{ $departments->total() ?? 0 }})</h5>
    </div>
    <div class="card-body">
        @if(isset($departments) && $departments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Departemen</th>
                            <th>Kode</th>
                            <th>Jumlah Karyawan</th>
                            <th>Jumlah Posisi</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $department->name }}</h6>
                                        @if($department->description)
                                            <small class="text-muted">{{ Str::limit($department->description, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $department->code ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $department->employees_count ?? 0 }} orang</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $department->positions_count ?? 0 }} posisi</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $department->is_active ? 'success' : 'danger' }}">
                                    {{ $department->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $department->created_at ? $department->created_at->format('d/m/Y') : 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(auth()->user()->hasPermission('departments.view'))
                                    <a href="{{ route('departments.show', $department) }}" class="btn btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('departments.edit'))
                                    <a href="{{ route('departments.edit', $department) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('departments.delete'))
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="confirmDelete({{ $department->id }}, '{{ $department->name }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $departments->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>Tidak ada departemen ditemukan</h5>
                <p class="text-muted">Belum ada departemen yang terdaftar atau sesuai dengan filter yang dipilih.</p>
                @if(auth()->user()->hasPermission('departments.create'))
                <a href="{{ route('departments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Departemen Pertama
                </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $active_departments ?? 0 }}</h4>
                        <p class="mb-0">Departemen Aktif</p>
                    </div>
                    <div>
                        <i class="fas fa-building fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $total_employees ?? 0 }}</h4>
                        <p class="mb-0">Total Karyawan</p>
                    </div>
                    <div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                        <h4>{{ $total_positions ?? 0 }}</h4>
                        <p class="mb-0">Total Posisi</p>
                    </div>
                    <div>
                        <i class="fas fa-briefcase fa-2x opacity-75"></i>
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
                <p>Apakah Anda yakin ingin menghapus departemen <strong id="departmentName"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi data karyawan terkait.</small></p>
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
function confirmDelete(departmentId, departmentName) {
    document.getElementById('departmentName').textContent = departmentName;
    document.getElementById('deleteForm').action = `/departments/${departmentId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function exportData() {
    alert('Fitur export akan diimplementasikan');
}

function importData() {
    alert('Fitur import akan diimplementasikan');
}

function generateReport() {
    alert('Fitur laporan akan diimplementasikan');
}

$(document).ready(function() {
    // Auto-submit form on status change
    $('select[name="status"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
