@extends('layouts.app')

@section('title', 'Profil')
@section('page-title', 'Profil Pengguna')

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
                    <a href="{{ route('profile.password') }}" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </a>
                    <a href="{{ route('attendance.clock') }}" class="btn btn-primary">
                        <i class="fas fa-clock me-2"></i>Absensi
                    </a>
                    @if(auth()->user()->hasRole('karyawan'))
                    <a href="{{ route('leaves.create') }}" class="btn btn-info">
                        <i class="fas fa-calendar-times me-2"></i>Ajukan Cuti
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Edit Profile Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Nama Depan</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Nama Belakang</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                            <small class="text-muted">Email tidak dapat diubah</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Foto Profil</label>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                               id="profile_photo" name="profile_photo" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                        @error('profile_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Personal Information -->
        <div class="card mt-4">
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
                                <td class="text-muted">Employee ID:</td>
                                <td>{{ $user->employee_id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Kelamin:</td>
                                <td>{{ $user->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Lahir:</td>
                                <td>{{ $user->birth_date ? $user->birth_date->format('d F Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            @if($user->employee)
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
                            <tr>
                                <td class="text-muted">Status Karyawan:</td>
                                <td>
                                    <span class="badge bg-{{ $user->employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($user->employee->employment_status ?? 'N/A') }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Role Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>Role & Permissions</h5>
            </div>
            <div class="card-body">
                @if($user->roles->count() > 0)
                    <div class="row">
                        @foreach($user->roles as $role)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="mb-2">
                                    <span class="badge bg-primary">{{ $role->display_name }}</span>
                                </h6>
                                <p class="text-muted mb-2">{{ $role->description }}</p>
                                <small class="text-muted">
                                    Diberikan: {{ $role->pivot->assigned_at ? \Carbon\Carbon::parse($role->pivot->assigned_at)->format('d F Y') : 'N/A' }}
                                </small>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Preview profile photo
    $('#profile_photo').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You could add preview functionality here
                console.log('File selected:', file.name);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
