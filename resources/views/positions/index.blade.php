@extends('layouts.app')

@section('title', 'Posisi')
@section('page-title', 'Manajemen Posisi')

@section('content')
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="{{ route('positions.create') }}" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Tambah Posisi
                </a>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-success w-100" onclick="exportData()">
                    <i class="fas fa-download me-2"></i>Export Excel
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-info w-100" onclick="importData()">
                    <i class="fas fa-upload me-2"></i>Import Data
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
        <form method="GET" action="{{ route('positions.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari nama posisi..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="department_id">
                        <option value="">Semua Departemen</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="level">
                        <option value="">Semua Level</option>
                        <option value="entry" {{ request('level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                        <option value="junior" {{ request('level') == 'junior' ? 'selected' : '' }}>Junior</option>
                        <option value="senior" {{ request('level') == 'senior' ? 'selected' : '' }}>Senior</option>
                        <option value="manager" {{ request('level') == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="director" {{ request('level') == 'director' ? 'selected' : '' }}>Director</option>
                    </select>
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
                        <a href="{{ route('positions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Positions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Daftar Posisi ({{ $positions->total() ?? 0 }})</h5>
    </div>
    <div class="card-body">
        @if(isset($positions) && $positions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Posisi</th>
                            <th>Departemen</th>
                            <th>Level</th>
                            <th>Gaji Pokok</th>
                            <th>Jumlah Karyawan</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $position)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-briefcase text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $position->name }}</h6>
                                        @if($position->description)
                                            <small class="text-muted">{{ Str::limit($position->description, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $position->department->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @php
                                    $levelColors = [
                                        'entry' => 'secondary',
                                        'junior' => 'primary',
                                        'senior' => 'success',
                                        'manager' => 'warning',
                                        'director' => 'danger'
                                    ];
                                    $levelLabels = [
                                        'entry' => 'Entry Level',
                                        'junior' => 'Junior',
                                        'senior' => 'Senior',
                                        'manager' => 'Manager',
                                        'director' => 'Director'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $levelColors[$position->level] ?? 'secondary' }}">
                                    {{ $levelLabels[$position->level] ?? ucfirst($position->level) }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">Rp {{ number_format($position->base_salary ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $position->employees_count ?? 0 }} orang</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $position->is_active ? 'success' : 'danger' }}">
                                    {{ $position->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $position->created_at ? $position->created_at->format('d/m/Y') : 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('positions.show', $position) }}" class="btn btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('positions.edit', $position) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete({{ $position->id }}, '{{ $position->name }}')" title="Hapus">
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
                {{ $positions->links('custom.pagination') }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <h5>Tidak ada posisi ditemukan</h5>
                <p class="text-muted">Belum ada posisi yang terdaftar atau sesuai dengan filter yang dipilih.</p>
                <a href="{{ route('positions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Posisi Pertama
                </a>
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
                        <h4>{{ $positions->where('is_active', true)->count() ?? 0 }}</h4>
                        <p class="mb-0">Posisi Aktif</p>
                    </div>
                    <div>
                        <i class="fas fa-briefcase fa-2x opacity-75"></i>
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
                        <h4>Rp {{ number_format($average_salary ?? 0, 0, ',', '.') }}</h4>
                        <p class="mb-0">Rata-rata Gaji</p>
                    </div>
                    <div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
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
                        <h4>{{ $positions->where('employees_count', 0)->count() ?? 0 }}</h4>
                        <p class="mb-0">Posisi Kosong</p>
                    </div>
                    <div>
                        <i class="fas fa-user-times fa-2x opacity-75"></i>
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
                <p>Apakah Anda yakin ingin menghapus posisi <strong id="positionName"></strong>?</p>
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
function confirmDelete(positionId, positionName) {
    document.getElementById('positionName').textContent = positionName;
    document.getElementById('deleteForm').action = `/positions/${positionId}`;
    
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
    // Auto-submit form on filter change
    $('select[name="department_id"], select[name="level"], select[name="status"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
