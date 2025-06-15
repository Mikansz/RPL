@extends('layouts.app')

@section('title', 'Edit Departemen')
@section('page-title', 'Edit Departemen - ' . $department->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Edit Departemen</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('departments.update', $department) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Kode Departemen -->
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Kode Departemen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code', $department->code) }}" 
                                   placeholder="Contoh: IT, HR, FIN" maxlength="10" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maksimal 10 karakter, unik untuk setiap departemen</small>
                        </div>

                        <!-- Nama Departemen -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $department->name) }}" 
                                   placeholder="Contoh: Information Technology" maxlength="100" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>



                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi singkat tentang departemen ini...">{{ old('description', $department->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Status Aktif</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">Departemen yang tidak aktif tidak akan muncul dalam pilihan</small>
                    </div>

                    <!-- Informasi Tambahan -->
                    @if($department->employees_count > 0 || $department->positions_count > 0)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi Departemen</h6>
                        <ul class="mb-0">
                            @if($department->employees_count > 0)
                                <li>Departemen ini memiliki <strong>{{ $department->employees_count }} karyawan</strong></li>
                            @endif
                            @if($department->positions_count > 0)
                                <li>Departemen ini memiliki <strong>{{ $department->positions_count }} posisi</strong></li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <a href="{{ route('departments.show', $department) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>Lihat Detail
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Departemen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistik Departemen -->
        @if($department->employees()->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Departemen</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $department->employees()->count() }}</h4>
                            <small class="text-muted">Total Karyawan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-success">{{ $department->employees()->where('employment_status', 'active')->count() }}</h4>
                            <small class="text-muted">Karyawan Aktif</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-info">{{ $department->positions()->count() }}</h4>
                            <small class="text-muted">Total Posisi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate code from name if code is empty
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    
    nameInput.addEventListener('input', function() {
        if (!codeInput.value) {
            let code = this.value
                .toUpperCase()
                .replace(/[^A-Z0-9\s]/g, '')
                .split(' ')
                .map(word => word.charAt(0))
                .join('')
                .substring(0, 10);
            codeInput.value = code;
        }
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const code = codeInput.value.trim();
        const name = nameInput.value.trim();
        
        if (!code || !name) {
            e.preventDefault();
            alert('Kode dan Nama Departemen harus diisi!');
            return false;
        }
        
        if (code.length > 10) {
            e.preventDefault();
            alert('Kode Departemen maksimal 10 karakter!');
            codeInput.focus();
            return false;
        }
    });
});
</script>
@endpush
