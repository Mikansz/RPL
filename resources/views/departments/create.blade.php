@extends('layouts.app')

@section('title', 'Tambah Departemen')
@section('page-title', 'Tambah Departemen Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Form Tambah Departemen</h5>
            </div>
            <div class="card-body">
                <!-- Debug Info -->
                <div class="alert alert-info">
                    <strong>Debug Info:</strong><br>
                    Form Action: {{ route('departments.store') }}<br>
                    CSRF Token: {{ csrf_token() }}<br>
                    Current URL: {{ url()->current() }}
                </div>

                <form method="POST" action="{{ route('departments.store') }}" id="departmentForm">
                    @csrf
                    
                    <div class="row">
                        <!-- Kode Departemen -->
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Kode Departemen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code') }}" 
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
                                   id="name" name="name" value="{{ old('name') }}" 
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
                                  placeholder="Deskripsi singkat tentang departemen ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Status Aktif</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">Departemen yang tidak aktif tidak akan muncul dalam pilihan</small>
                    </div>

                    <!-- Tips -->
                    <div class="alert alert-light border">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips:</h6>
                        <ul class="mb-0 small">
                            <li>Kode departemen akan otomatis dibuat dari nama jika tidak diisi</li>
                            <li>Gunakan kode yang mudah diingat dan singkat</li>
                            <li>Departemen yang tidak aktif tidak akan muncul dalam pilihan saat membuat karyawan baru</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Departemen
                        </button>
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
    console.log('🚀 Department create page loaded');

    // Auto-generate code from name if code is empty
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    const form = document.getElementById('departmentForm');

    console.log('📋 Form elements found:', {
        nameInput: !!nameInput,
        codeInput: !!codeInput,
        form: !!form,
        formAction: form?.action,
        formMethod: form?.method
    });

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

    // Form validation with detailed logging
    form.addEventListener('submit', function(e) {
        console.log('🔄 Form submit event triggered');

        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        console.log('📝 Form data:', data);
        console.log('🎯 Form action:', form.action);
        console.log('🔒 CSRF token:', document.querySelector('meta[name="csrf-token"]')?.content);

        const code = codeInput.value.trim();
        const name = nameInput.value.trim();

        if (!code || !name) {
            e.preventDefault();
            console.log('❌ Validation failed: Missing required fields');
            alert('Kode dan Nama Departemen harus diisi!');
            return false;
        }

        if (code.length > 10) {
            e.preventDefault();
            console.log('❌ Validation failed: Code too long');
            alert('Kode Departemen maksimal 10 karakter!');
            codeInput.focus();
            return false;
        }

        console.log('✅ Validation passed, form will submit');

        // Add loading state to submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
        }

        // Let the form submit naturally
        console.log('🚀 Form submitting to:', form.action);
    });

    // Real-time validation feedback
    codeInput.addEventListener('input', function() {
        if (this.value.length > 10) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Debug: Test form submission manually
    window.testFormSubmit = function() {
        console.log('🧪 Manual form test');
        codeInput.value = 'TEST';
        nameInput.value = 'Test Department';
        form.submit();
    };

    console.log('💡 Use testFormSubmit() in console to test form submission');
});
</script>
@endpush
