@extends('layouts.app')

@section('title', 'CFO Dashboard')
@section('page-title', 'CFO Dashboard - Financial Overview')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-success text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1">Dashboard CFO</h3>
                        <p class="mb-0">Selamat datang di dashboard Chief Financial Officer</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded p-3 mb-3">
                    <i class="fas fa-users fa-2x text-primary"></i>
                </div>
                <h5>Manajemen Karyawan</h5>
                <p class="text-muted">Kelola data karyawan</p>
                <a href="{{ route('employees.index') }}" class="btn btn-primary btn-sm">Akses</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded p-3 mb-3">
                    <i class="fas fa-clock fa-2x text-success"></i>
                </div>
                <h5>Data Absensi</h5>
                <p class="text-muted">Monitor kehadiran karyawan</p>
                <a href="{{ route('attendance.index') }}" class="btn btn-success btn-sm">Akses</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded p-3 mb-3">
                    <i class="fas fa-calendar-times fa-2x text-warning"></i>
                </div>
                <h5>Izin & Cuti</h5>
                <p class="text-muted">Kelola permohonan cuti</p>
                <a href="{{ route('permits.index') }}" class="btn btn-warning btn-sm">Akses</a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded p-3 mb-3">
                    <i class="fas fa-building fa-2x text-info"></i>
                </div>
                <h5>Departemen</h5>
                <p class="text-muted">Kelola struktur organisasi</p>
                <a href="{{ route('departments.index') }}" class="btn btn-info btn-sm">Akses</a>
            </div>
        </div>
    </div>
</div>

<!-- Information Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi CFO Dashboard</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Akses Fitur</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Manajemen Data Karyawan</li>
                            <li><i class="fas fa-check text-success me-2"></i>Monitoring Absensi</li>
                            <li><i class="fas fa-check text-success me-2"></i>Persetujuan Cuti</li>
                            <li><i class="fas fa-check text-success me-2"></i>Kelola Departemen</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Sistem</h6>
                        <p class="text-muted">
                            Dashboard ini menyediakan akses cepat ke berbagai fitur manajemen
                            karyawan dan operasional perusahaan. Gunakan menu navigasi di
                            sebelah kiri untuk mengakses fitur lengkap.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
