@extends('layouts.app')

@section('title', 'Edit Posisi')
@section('page-title', 'Edit Posisi - ' . $position->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Edit Posisi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('positions.update', $position) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Position Code -->
                    <div class="mb-3">
                        <label for="code" class="form-label">Kode Posisi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code', $position->code) }}" 
                               placeholder="Contoh: MGR001" maxlength="10" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk posisi (maksimal 10 karakter)</small>
                    </div>

                    <!-- Position Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Posisi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $position->name) }}" 
                               placeholder="Contoh: Manager IT" maxlength="100" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Departemen <span class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" 
                                id="department_id" name="department_id" required>
                            <option value="">Pilih Departemen</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                        {{ old('department_id', $position->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }} ({{ $department->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Salary and Level -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="base_salary" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('base_salary') is-invalid @enderror" 
                                       id="base_salary" name="base_salary" value="{{ old('base_salary', $position->base_salary) }}" 
                                       placeholder="0" min="0" required>
                            </div>
                            @error('base_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Gaji pokok untuk posisi ini</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('level') is-invalid @enderror" 
                                   id="level" name="level" value="{{ old('level', $position->level) }}" 
                                   placeholder="1" min="1" max="10" required>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Level hierarki posisi (1-10)</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $position->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Posisi Aktif
                            </label>
                        </div>
                        <small class="text-muted">Centang jika posisi masih aktif dan dapat digunakan</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Deskripsi tugas dan tanggung jawab posisi...">{{ old('description', $position->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current Employees Info -->
                    @if($position->employees && $position->employees->count() > 0)
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Karyawan dengan Posisi Ini</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($position->employees->take(6) as $employee)
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <small class="fw-bold">{{ $employee->user->full_name }}</small><br>
                                            <small class="text-muted">{{ $employee->user->employee_id }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if($position->employees->count() > 6)
                            <small class="text-muted">Dan {{ $position->employees->count() - 6 }} karyawan lainnya...</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <a href="{{ route('positions.show', $position) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>Lihat Detail
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Posisi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format salary input
    const salaryInput = document.getElementById('base_salary');
    
    salaryInput.addEventListener('input', function() {
        // Remove non-numeric characters
        let value = this.value.replace(/[^\d]/g, '');
        this.value = value;
    });

    // Auto-generate code based on name and department
    const nameInput = document.getElementById('name');
    const departmentSelect = document.getElementById('department_id');
    const codeInput = document.getElementById('code');
    
    function generateCode() {
        const name = nameInput.value.trim();
        const departmentOption = departmentSelect.options[departmentSelect.selectedIndex];
        
        if (name && departmentOption && departmentOption.value) {
            const departmentCode = departmentOption.text.match(/\(([^)]+)\)/);
            if (departmentCode) {
                // Take first 3 letters of name and department code
                const nameCode = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
                const deptCode = departmentCode[1].substring(0, 3);
                const suggestedCode = deptCode + nameCode;
                
                // Only suggest if current code is empty or matches old pattern
                if (!codeInput.value || codeInput.value === codeInput.dataset.oldCode) {
                    codeInput.value = suggestedCode.substring(0, 10);
                    codeInput.dataset.oldCode = suggestedCode.substring(0, 10);
                }
            }
        }
    }

    // Store original code
    codeInput.dataset.oldCode = codeInput.value;

    // Generate code on name or department change (only for new positions)
    if (!codeInput.value) {
        nameInput.addEventListener('blur', generateCode);
        departmentSelect.addEventListener('change', generateCode);
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const code = document.getElementById('code').value.trim();
        const name = document.getElementById('name').value.trim();
        const departmentId = document.getElementById('department_id').value;
        const baseSalary = document.getElementById('base_salary').value;
        const level = document.getElementById('level').value;
        
        if (!code) {
            e.preventDefault();
            alert('Kode posisi harus diisi!');
            document.getElementById('code').focus();
            return;
        }
        
        if (!name) {
            e.preventDefault();
            alert('Nama posisi harus diisi!');
            document.getElementById('name').focus();
            return;
        }
        
        if (!departmentId) {
            e.preventDefault();
            alert('Departemen harus dipilih!');
            document.getElementById('department_id').focus();
            return;
        }
        
        if (!baseSalary || baseSalary < 0) {
            e.preventDefault();
            alert('Gaji pokok harus diisi dengan nilai yang valid!');
            document.getElementById('base_salary').focus();
            return;
        }
        
        if (!level || level < 1 || level > 10) {
            e.preventDefault();
            alert('Level harus diisi dengan nilai antara 1-10!');
            document.getElementById('level').focus();
            return;
        }
    });

    // Format number display
    salaryInput.addEventListener('blur', function() {
        if (this.value) {
            const formatted = parseInt(this.value).toLocaleString('id-ID');
            this.setAttribute('title', 'Rp ' + formatted);
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar {
    width: 32px;
    height: 32px;
}
</style>
@endpush
