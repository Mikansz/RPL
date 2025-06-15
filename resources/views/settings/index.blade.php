@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')
<div class="row">
    <div class="col-lg-3">
        <!-- Settings Navigation -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Menu Pengaturan</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                    <i class="fas fa-globe me-2"></i>Umum
                </a>
                <a href="#attendance" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="fas fa-clock me-2"></i>Absensi
                </a>
                <a href="#payroll" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="fas fa-money-bill-wave me-2"></i>Payroll
                </a>
                <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="fas fa-bell me-2"></i>Notifikasi
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="fas fa-shield-alt me-2"></i>Keamanan
                </a>
                <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                    <i class="fas fa-database me-2"></i>Backup
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="tab-content">
            <!-- General Settings -->
            <div class="tab-pane fade show active" id="general">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Umum</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            <input type="hidden" name="section" value="general">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Nama Perusahaan</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           value="{{ $settings['company_name'] ?? 'STEA Company' }}">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="company_email" class="form-label">Email Perusahaan</label>
                                    <input type="email" class="form-control" id="company_email" name="company_email" 
                                           value="{{ $settings['company_email'] ?? 'admin@stea.com' }}">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_address" class="form-label">Alamat Perusahaan</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="3">{{ $settings['company_address'] ?? '' }}</textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                                        <option value="Asia/Makassar" {{ ($settings['timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                                        <option value="Asia/Jayapura" {{ ($settings['timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="currency" class="form-label">Mata Uang</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value="IDR" {{ ($settings['currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR (Rupiah)</option>
                                        <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Settings -->
            <div class="tab-pane fade" id="attendance">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Absensi</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            <input type="hidden" name="section" value="attendance">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="work_start_time" class="form-label">Jam Masuk</label>
                                    <input type="time" class="form-control" id="work_start_time" name="work_start_time" 
                                           value="{{ $settings['work_start_time'] ?? '08:00' }}">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="work_end_time" class="form-label">Jam Pulang</label>
                                    <input type="time" class="form-control" id="work_end_time" name="work_end_time" 
                                           value="{{ $settings['work_end_time'] ?? '17:00' }}">
                                </div>
                            </div>
                            

                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="late_tolerance" class="form-label">Toleransi Terlambat (menit)</label>
                                    <input type="number" class="form-control" id="late_tolerance" name="late_tolerance" 
                                           value="{{ $settings['late_tolerance'] ?? '15' }}" min="0">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="overtime_rate" class="form-label">Tarif Lembur per Jam</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="overtime_rate" name="overtime_rate" 
                                               value="{{ $settings['overtime_rate'] ?? '25000' }}" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="require_location" name="require_location" 
                                           {{ ($settings['require_location'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_location">
                                        Wajib Lokasi untuk Absensi
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Payroll Settings -->
            <div class="tab-pane fade" id="payroll">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Payroll</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            <input type="hidden" name="section" value="payroll">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payroll_date" class="form-label">Tanggal Gajian</label>
                                    <select class="form-select" id="payroll_date" name="payroll_date">
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}" {{ ($settings['payroll_date'] ?? '25') == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="annual_leave_days" class="form-label">Jatah Cuti Tahunan</label>
                                    <input type="number" class="form-control" id="annual_leave_days" name="annual_leave_days" 
                                           value="{{ $settings['annual_leave_days'] ?? '12' }}" min="0">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tax_rate" class="form-label">Tarif Pajak (%)</label>
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                           value="{{ $settings['tax_rate'] ?? '5' }}" min="0" max="100" step="0.1">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="bpjs_rate" class="form-label">Tarif BPJS (%)</label>
                                    <input type="number" class="form-control" id="bpjs_rate" name="bpjs_rate" 
                                           value="{{ $settings['bpjs_rate'] ?? '4' }}" min="0" max="100" step="0.1">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="tab-pane fade" id="notifications">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Notifikasi</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            <input type="hidden" name="section" value="notifications">
                            
                            <div class="mb-3">
                                <h6>Email Notifications</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_leave_requests" name="email_leave_requests" 
                                           {{ ($settings['email_leave_requests'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_leave_requests">
                                        Pengajuan Cuti
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_overtime_requests" name="email_overtime_requests" 
                                           {{ ($settings['email_overtime_requests'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_overtime_requests">
                                        Pengajuan Lembur
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_payroll_ready" name="email_payroll_ready" 
                                           {{ ($settings['email_payroll_ready'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_payroll_ready">
                                        Slip Gaji Siap
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="tab-pane fade" id="security">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Keamanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Pengaturan keamanan memerlukan restart aplikasi
                        </div>
                        
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            <input type="hidden" name="section" value="security">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="session_timeout" class="form-label">Session Timeout (menit)</label>
                                    <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                           value="{{ $settings['session_timeout'] ?? '120' }}" min="30">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="max_login_attempts" class="form-label">Maksimal Percobaan Login</label>
                                    <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                           value="{{ $settings['max_login_attempts'] ?? '5' }}" min="3">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="force_https" name="force_https" 
                                           {{ ($settings['force_https'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="force_https">
                                        Paksa HTTPS
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Backup Settings -->
            <div class="tab-pane fade" id="backup">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan Backup</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Backup Manual</h6>
                                <p class="text-muted">Buat backup database secara manual</p>
                                <button type="button" class="btn btn-primary" onclick="createBackup()">
                                    <i class="fas fa-download me-2"></i>Buat Backup
                                </button>
                            </div>
                            <div class="col-md-6">
                                <h6>Backup Otomatis</h6>
                                <p class="text-muted">Jadwalkan backup otomatis</p>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto_backup" name="auto_backup">
                                    <label class="form-check-label" for="auto_backup">
                                        Aktifkan Backup Otomatis
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6>Riwayat Backup</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Ukuran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada backup</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createBackup() {
    if (confirm('Apakah Anda yakin ingin membuat backup database?')) {
        alert('Fitur backup akan diimplementasikan');
    }
}

$(document).ready(function() {
    // Show success message if settings were saved
    @if(session('success'))
        alert('{{ session('success') }}');
    @endif
});
</script>
@endpush
