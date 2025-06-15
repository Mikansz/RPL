@extends('layouts.app')

@section('title', 'Detail Posisi')
@section('page-title', 'Detail Posisi - ' . $position->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Position Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Informasi Posisi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Kode Posisi:</strong></td>
                                <td>{{ $position->code }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Posisi:</strong></td>
                                <td>{{ $position->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Departemen:</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $position->department->name }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Level:</strong></td>
                                <td>
                                    <span class="badge bg-info">Level {{ $position->level }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Gaji Pokok:</strong></td>
                                <td>
                                    <span class="fw-bold text-success">Rp {{ number_format($position->base_salary, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($position->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td>{{ $position->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Diupdate:</strong></td>
                                <td>{{ $position->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($position->description)
                <div class="mt-3">
                    <h6><strong>Deskripsi:</strong></h6>
                    <p class="text-muted">{{ $position->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Employees with this Position -->
        @if($position->employees && $position->employees->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Karyawan dengan Posisi Ini</h5>
                <span class="badge bg-primary">{{ $position->employees->count() }} Orang</span>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($position->employees as $employee)
                    <div class="col-md-6 mb-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $employee->user->full_name }}</h6>
                                        <small class="text-muted">{{ $employee->user->employee_id }}</small><br>
                                        <small class="text-muted">{{ $employee->user->email }}</small>
                                        <div class="mt-1">
                                            @if($employee->employment_status == 'active')
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($employee->employment_status) }}</span>
                                            @endif
                                            <span class="badge bg-outline-primary">{{ ucfirst($employee->employment_type) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Karyawan dengan Posisi Ini</h5>
            </div>
            <div class="card-body text-center">
                <div class="py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Belum ada karyawan dengan posisi ini</h6>
                    <p class="text-muted">Karyawan akan muncul di sini setelah ditugaskan ke posisi ini.</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('positions.edit', $position) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Posisi
                    </a>
                    <a href="{{ route('positions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                    @if($position->is_active)
                        <button type="button" class="btn btn-outline-warning" onclick="toggleStatus(false)">
                            <i class="fas fa-pause me-2"></i>Nonaktifkan
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-success" onclick="toggleStatus(true)">
                            <i class="fas fa-play me-2"></i>Aktifkan
                        </button>
                    @endif
                    @if($position->employees->count() == 0)
                        <button type="button" class="btn btn-outline-danger" onclick="deletePosition()">
                            <i class="fas fa-trash me-2"></i>Hapus Posisi
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-danger" disabled title="Tidak dapat menghapus posisi yang masih memiliki karyawan">
                            <i class="fas fa-trash me-2"></i>Hapus Posisi
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $position->employees->where('employment_status', 'active')->count() }}</h4>
                            <small class="text-muted">Karyawan Aktif</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $position->employees->count() }}</h4>
                        <small class="text-muted">Total Karyawan</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="text-info mb-1">{{ $position->employees->where('employment_type', 'permanent')->count() }}</h6>
                            <small class="text-muted">Tetap</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-warning mb-1">{{ $position->employees->where('employment_type', 'contract')->count() }}</h6>
                        <small class="text-muted">Kontrak</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Departemen</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="avatar bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                    <h6>{{ $position->department->name }}</h6>
                    <p class="text-muted mb-2">{{ $position->department->code }}</p>
                    @if($position->department->description)
                        <small class="text-muted">{{ $position->department->description }}</small>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('departments.show', $position->department) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Lihat Departemen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" action="{{ route('positions.destroy', $position) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function toggleStatus(status) {
    const action = status ? 'mengaktifkan' : 'menonaktifkan';
    
    if (confirm(`Apakah Anda yakin ingin ${action} posisi ini?`)) {
        // Create form to update status
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("positions.update", $position) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';
        
        const codeField = document.createElement('input');
        codeField.type = 'hidden';
        codeField.name = 'code';
        codeField.value = '{{ $position->code }}';
        
        const nameField = document.createElement('input');
        nameField.type = 'hidden';
        nameField.name = 'name';
        nameField.value = '{{ $position->name }}';
        
        const departmentField = document.createElement('input');
        departmentField.type = 'hidden';
        departmentField.name = 'department_id';
        departmentField.value = '{{ $position->department_id }}';
        
        const salaryField = document.createElement('input');
        salaryField.type = 'hidden';
        salaryField.name = 'base_salary';
        salaryField.value = '{{ $position->base_salary }}';
        
        const levelField = document.createElement('input');
        levelField.type = 'hidden';
        levelField.name = 'level';
        levelField.value = '{{ $position->level }}';
        
        const descField = document.createElement('input');
        descField.type = 'hidden';
        descField.name = 'description';
        descField.value = '{{ $position->description }}';
        
        const statusField = document.createElement('input');
        statusField.type = 'hidden';
        statusField.name = 'is_active';
        statusField.value = status ? '1' : '0';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(codeField);
        form.appendChild(nameField);
        form.appendChild(departmentField);
        form.appendChild(salaryField);
        form.appendChild(levelField);
        form.appendChild(descField);
        form.appendChild(statusField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deletePosition() {
    if (confirm('Apakah Anda yakin ingin menghapus posisi ini? Tindakan ini tidak dapat dibatalkan!')) {
        if (confirm('Pastikan tidak ada karyawan yang menggunakan posisi ini. Lanjutkan menghapus?')) {
            document.getElementById('deleteForm').submit();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.avatar {
    width: 40px;
    height: 40px;
}
</style>
@endpush
