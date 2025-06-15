@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-tachometer-alt fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">Selamat Datang di Sistem Penggajian RHI</h3>
                <p class="text-muted mb-4">
                    Anda belum memiliki role yang ditentukan. Silakan hubungi administrator untuk mendapatkan akses yang sesuai.
                </p>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Untuk mengakses fitur-fitur sistem, Anda memerlukan role yang sesuai dengan jabatan Anda.
                        </div>
                    </div>
                </div>
                <a href="{{ route('profile') }}" class="btn btn-primary">
                    <i class="fas fa-user me-2"></i>Lihat Profil
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Info Cards -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x text-primary mb-3"></i>
                <h5>Absensi</h5>
                <p class="text-muted">Kelola waktu kerja Anda</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt fa-2x text-warning mb-3"></i>
                <h5>Cuti & Izin</h5>
                <p class="text-muted">Pengajuan cuti dan izin</p>
            </div>
        </div>
    </div>
</div>
@endsection
