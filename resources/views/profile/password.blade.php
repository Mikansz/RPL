@extends('layouts.app')

@section('title', 'Ubah Password')
@section('page-title', 'Ubah Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimal 8 karakter</small>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Ulangi password baru</small>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <label class="form-label">Kekuatan Password</label>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="passwordStrengthText" class="text-muted">Masukkan password untuk melihat kekuatan</small>
                    </div>
                    
                    <!-- Password Requirements -->
                    <div class="mb-4">
                        <label class="form-label">Persyaratan Password:</label>
                        <ul class="list-unstyled mb-0">
                            <li id="length" class="text-muted">
                                <i class="fas fa-times text-danger me-2"></i>Minimal 8 karakter
                            </li>
                            <li id="lowercase" class="text-muted">
                                <i class="fas fa-times text-danger me-2"></i>Huruf kecil (a-z)
                            </li>
                            <li id="uppercase" class="text-muted">
                                <i class="fas fa-times text-danger me-2"></i>Huruf besar (A-Z)
                            </li>
                            <li id="number" class="text-muted">
                                <i class="fas fa-times text-danger me-2"></i>Angka (0-9)
                            </li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('profile') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save me-2"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Tips Keamanan</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
                    <li>Jangan gunakan informasi pribadi seperti nama atau tanggal lahir</li>
                    <li>Jangan gunakan password yang sama dengan akun lain</li>
                    <li>Ubah password secara berkala</li>
                    <li>Jangan bagikan password kepada siapapun</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#toggleCurrentPassword').click(function() {
        const input = $('#current_password');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    $('#toggleNewPassword').click(function() {
        const input = $('#password');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    $('#toggleConfirmPassword').click(function() {
        const input = $('#password_confirmation');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Password strength checker
    $('#password').on('input', function() {
        const password = $(this).val();
        checkPasswordStrength(password);
        checkPasswordMatch();
    });
    
    $('#password_confirmation').on('input', function() {
        checkPasswordMatch();
    });
    
    function checkPasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password)
        };
        
        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = $('#' + req);
            const icon = element.find('i');
            
            if (requirements[req]) {
                icon.removeClass('fa-times text-danger').addClass('fa-check text-success');
                element.removeClass('text-muted').addClass('text-success');
                score++;
            } else {
                icon.removeClass('fa-check text-success').addClass('fa-times text-danger');
                element.removeClass('text-success').addClass('text-muted');
            }
        });
        
        // Update strength bar
        const strengthBar = $('#passwordStrength');
        const strengthText = $('#passwordStrengthText');
        
        let strengthClass = '';
        let strengthLabel = '';
        
        switch (score) {
            case 0:
            case 1:
                strengthClass = 'bg-danger';
                strengthLabel = 'Sangat Lemah';
                break;
            case 2:
                strengthClass = 'bg-warning';
                strengthLabel = 'Lemah';
                break;
            case 3:
                strengthClass = 'bg-info';
                strengthLabel = 'Sedang';
                break;
            case 4:
                strengthClass = 'bg-success';
                strengthLabel = 'Kuat';
                break;
        }
        
        strengthBar.removeClass('bg-danger bg-warning bg-info bg-success').addClass(strengthClass);
        strengthBar.css('width', (score * 25) + '%');
        strengthText.text(strengthLabel);
        
        return score >= 3; // Require at least 3 criteria
    }
    
    function checkPasswordMatch() {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        const submitBtn = $('#submitBtn');
        
        const isStrong = checkPasswordStrength(password);
        const isMatching = password === confirmation && password.length > 0;
        
        if (isStrong && isMatching) {
            submitBtn.prop('disabled', false);
        } else {
            submitBtn.prop('disabled', true);
        }
    }
});
</script>
@endpush
