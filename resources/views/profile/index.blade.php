@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset(auth()->user()->profile_photo) }}" alt="Profile Photo" 
                             class="rounded-circle" width="120" height="120" style="object-fit: cover;">
                    @else
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-primary"></i>
                        </div>
                    @endif
                </div>
                
                <h4 class="mb-1">{{ auth()->user()->full_name }}</h4>
                <p class="text-muted mb-2">{{ auth()->user()->employee->position->name ?? 'N/A' }}</p>
                <p class="text-muted mb-3">{{ auth()->user()->employee->department->name ?? 'N/A' }}</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted">Employee ID</h6>
                        <p class="mb-0">{{ auth()->user()->employee_id }}</p>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge bg-{{ auth()->user()->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst(auth()->user()->status) }}
                        </span>
                    </div>
                </div>
                
                <hr>
                
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#photoModal">
                    <i class="fas fa-camera me-2"></i>Ubah Foto Profil
                </button>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Saya</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-success">{{ $attendance_stats['present'] ?? 0 }}</h4>
                        <small class="text-muted">Hari Hadir</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-warning">{{ $attendance_stats['late'] ?? 0 }}</h4>
                        <small class="text-muted">Terlambat</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-info">{{ $leave_stats['remaining'] ?? 12 }}</h4>
                        <small class="text-muted">Sisa Cuti</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-primary">{{ $overtime_stats['hours'] ?? 0 }}</h4>
                        <small class="text-muted">Jam Lembur</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Nama Depan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Nama Belakang</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name', auth()->user()->last_name) }}">
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   id="birth_date" name="birth_date" 
                                   value="{{ old('birth_date', auth()->user()->birth_date ? auth()->user()->birth_date->format('Y-m-d') : '') }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Jenis Kelamin</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="male" {{ old('gender', auth()->user()->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ old('gender', auth()->user()->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3">{{ old('address', auth()->user()->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Password harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, dan angka.
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Employment Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Informasi Kepegawaian</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Departemen:</td>
                                <td>{{ auth()->user()->employee->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Posisi:</td>
                                <td>{{ auth()->user()->employee->position->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Bergabung:</td>
                                <td>{{ auth()->user()->employee->hire_date ? auth()->user()->employee->hire_date->format('d F Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Masa Kerja:</td>
                                <td>{{ auth()->user()->employee->hire_date ? auth()->user()->employee->hire_date->diffForHumans() : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Status Karyawan:</td>
                                <td>
                                    <span class="badge bg-{{ auth()->user()->employee->employment_status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst(auth()->user()->employee->employment_status ?? 'N/A') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Supervisor:</td>
                                <td>{{ auth()->user()->employee->supervisor->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Role:</td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst(auth()->user()->role) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Login:</td>
                                <td>{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d F Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Pilih Foto</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" 
                               accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                    </div>
                    
                    <div class="text-center">
                        <img id="photoPreview" src="#" alt="Preview" 
                             style="max-width: 200px; max-height: 200px; display: none;" 
                             class="rounded-circle">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Photo preview
    $('#profile_photo').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Show success message if profile was updated
    @if(session('success'))
        alert('{{ session('success') }}');
    @endif
});
</script>
@endpush
