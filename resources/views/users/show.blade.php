@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User - ' . $user->full_name)

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->profile_photo)
                        <img src="{{ asset($user->profile_photo) }}" alt="Profile Photo" 
                             class="rounded-circle" width="120" height="120" style="object-fit: cover;">
                    @else
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-primary"></i>
                        </div>
                    @endif
                </div>
                
                <h4 class="mb-1">{{ $user->full_name }}</h4>
                <p class="text-muted mb-2">{{ $user->employee->position->name ?? 'N/A' }}</p>
                <p class="text-muted mb-3">{{ $user->employee->department->name ?? 'N/A' }}</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted">Employee ID</h6>
                        <p class="mb-0">{{ $user->employee_id }}</p>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($user->status) }}
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
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit User
                    </a>
                    @if($user->status === 'active')
                        <button type="button" class="btn btn-danger" onclick="toggleUserStatus('{{ $user->id }}', 'inactive')">
                            <i class="fas fa-user-slash me-2"></i>Nonaktifkan
                        </button>
                    @else
                        <button type="button" class="btn btn-success" onclick="toggleUserStatus('{{ $user->id }}', 'active')">
                            <i class="fas fa-user-check me-2"></i>Aktifkan
                        </button>
                    @endif
                    <button type="button" class="btn btn-info" onclick="resetPassword('{{ $user->id }}')">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Username:</td>
                                <td>{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nomor Telepon:</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Kelamin:</td>
                                <td>{{ $user->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Tanggal Lahir:</td>
                                <td>{{ $user->birth_date ? $user->birth_date->format('d F Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Alamat:</td>
                                <td>{{ $user->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Terdaftar:</td>
                                <td>{{ $user->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Login:</td>
                                <td>{{ $user->last_login_at ? $user->last_login_at->format('d F Y H:i') : 'Belum pernah' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employee Information -->
        @if($user->employee)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Informasi Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Departemen:</td>
                                <td>{{ $user->employee->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Posisi:</td>
                                <td>{{ $user->employee->position->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Bergabung:</td>
                                <td>{{ $user->employee->hire_date ? $user->employee->hire_date->format('d F Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Gaji Pokok:</td>
                                <td>Rp {{ number_format($user->employee->position->base_salary ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Karyawan:</td>
                                <td>
                                    <span class="badge bg-{{ $user->employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($user->employee->employment_status ?? 'N/A') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Supervisor:</td>
                                <td>{{ $user->employee->supervisor->full_name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Role Information -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>Role & Permissions</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignRoleModal">
                    <i class="fas fa-plus me-1"></i>Assign Role
                </button>
            </div>
            <div class="card-body">
                @if($user->roles->count() > 0)
                    <div class="row">
                        @foreach($user->roles as $role)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-2">
                                            <span class="badge bg-primary">{{ $role->display_name }}</span>
                                        </h6>
                                        <p class="text-muted mb-2">{{ $role->description }}</p>
                                        <small class="text-muted">
                                            Diberikan: {{ $role->pivot->assigned_at ? \Carbon\Carbon::parse($role->pivot->assigned_at)->format('d F Y') : 'N/A' }}
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="removeRole('{{ $user->id }}', '{{ $role->id }}', '{{ $role->display_name }}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Belum ada role yang diberikan</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Log Aktivitas Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-3">
                    <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Log aktivitas akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div class="modal fade" id="assignRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('users.assign-role', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Pilih Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Pilih Role</option>
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Assign Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleUserStatus(userId, status) {
    const action = status === 'active' ? 'mengaktifkan' : 'menonaktifkan';
    
    if (confirm(`Apakah Anda yakin ingin ${action} user ini?`)) {
        // Implementation would go here
        alert('Fitur ini akan diimplementasikan');
    }
}

function resetPassword(userId) {
    if (confirm('Apakah Anda yakin ingin mereset password user ini?')) {
        // Implementation would go here
        alert('Fitur ini akan diimplementasikan');
    }
}

function removeRole(userId, roleId, roleName) {
    if (confirm(`Apakah Anda yakin ingin menghapus role "${roleName}" dari user ini?`)) {
        // Implementation would go here
        alert('Fitur ini akan diimplementasikan');
    }
}
</script>
@endpush
