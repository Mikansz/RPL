@extends('layouts.app')

@section('title', 'Demo Fitur')
@section('page-title', 'Demo Fitur Edit Jadwal & Shift')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <h4><i class="fas fa-check-circle me-2"></i>Fitur Edit Jadwal & Shift Berhasil Dibuat!</h4>
            <p class="mb-0">Semua fitur untuk menambah dan mengedit shift serta kantor sudah siap digunakan.</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Shift Management -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-business-time me-2"></i>
                    Manajemen Shift
                </h5>
            </div>
            <div class="card-body">
                <p>Kelola shift kerja karyawan dengan mudah:</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Tambah shift baru</li>
                    <li><i class="fas fa-check text-success me-2"></i>Edit shift existing</li>
                    <li><i class="fas fa-check text-success me-2"></i>Atur waktu mulai & selesai</li>
                    <li><i class="fas fa-check text-success me-2"></i>Aktifkan/nonaktifkan shift</li>
                    <li><i class="fas fa-check text-success me-2"></i>Preview real-time</li>
                    <li><i class="fas fa-check text-success me-2"></i>Statistik penggunaan</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('shifts.index') }}" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i>Lihat Semua Shift
                    </a>
                    <a href="{{ route('shifts.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Shift Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Office Management -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    Manajemen Kantor
                </h5>
            </div>
            <div class="card-body">
                <p>Kelola lokasi kantor untuk absensi:</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Tambah kantor baru</li>
                    <li><i class="fas fa-check text-success me-2"></i>Edit data kantor</li>
                    <li><i class="fas fa-check text-success me-2"></i>Atur koordinat GPS</li>
                    <li><i class="fas fa-check text-success me-2"></i>Atur radius absensi</li>
                    <li><i class="fas fa-check text-success me-2"></i>Aktifkan/nonaktifkan kantor</li>
                    <li><i class="fas fa-check text-success me-2"></i>Validasi lokasi</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('offices.index') }}" class="btn btn-info">
                        <i class="fas fa-list me-1"></i>Lihat Semua Kantor
                    </a>
                    <a href="{{ route('offices.create') }}" class="btn btn-outline-info">
                        <i class="fas fa-plus me-1"></i>Tambah Kantor Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Management -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Manajemen Jadwal
                </h5>
            </div>
            <div class="card-body">
                <p>Kelola jadwal kerja karyawan:</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Tambah jadwal baru</li>
                    <li><i class="fas fa-check text-success me-2"></i>Edit jadwal individual</li>
                    <li><i class="fas fa-check text-success me-2"></i>Bulk edit multiple jadwal</li>
                    <li><i class="fas fa-check text-success me-2"></i>Atur tipe kerja (WFO/WFA)</li>
                    <li><i class="fas fa-check text-success me-2"></i>Status management</li>
                    <li><i class="fas fa-check text-success me-2"></i>Calendar view</li>
                    <li><i class="fas fa-check text-success me-2"></i>Preview real-time</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('schedules.index') }}" class="btn btn-success">
                        <i class="fas fa-list me-1"></i>Lihat Semua Jadwal
                    </a>
                    <a href="{{ route('schedules.create') }}" class="btn btn-outline-success">
                        <i class="fas fa-plus me-1"></i>Tambah Jadwal Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Fitur-Fitur yang Tersedia
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>✅ Fitur Shift Kerja:</h6>
                        <ul>
                            <li><strong>Create Shift:</strong> Tambah shift baru dengan nama, waktu mulai, waktu selesai</li>
                            <li><strong>Edit Shift:</strong> Ubah data shift yang sudah ada</li>
                            <li><strong>Preview Real-time:</strong> Lihat preview durasi shift saat input</li>
                            <li><strong>Status Management:</strong> Aktifkan/nonaktifkan shift</li>
                            <li><strong>Validation:</strong> Cegah deaktivasi shift yang masih digunakan</li>
                            <li><strong>Statistics:</strong> Lihat statistik penggunaan shift</li>
                        </ul>

                        <h6>✅ Fitur Kantor:</h6>
                        <ul>
                            <li><strong>Create Office:</strong> Tambah kantor baru dengan koordinat GPS</li>
                            <li><strong>Edit Office:</strong> Ubah data kantor yang sudah ada</li>
                            <li><strong>GPS Coordinates:</strong> Atur latitude dan longitude</li>
                            <li><strong>Radius Setting:</strong> Atur radius absensi (10-1000 meter)</li>
                            <li><strong>Status Management:</strong> Aktifkan/nonaktifkan kantor</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>✅ Fitur Jadwal Kerja:</h6>
                        <ul>
                            <li><strong>Create Schedule:</strong> Tambah jadwal kerja baru</li>
                            <li><strong>Individual Edit:</strong> Edit jadwal satu per satu</li>
                            <li><strong>Bulk Edit:</strong> Edit multiple jadwal sekaligus</li>
                            <li><strong>Work Type:</strong> Atur WFO (Work From Office) atau WFA (Work From Anywhere)</li>
                            <li><strong>Status Management:</strong> Terjadwal, Disetujui, Dibatalkan</li>
                            <li><strong>Calendar View:</strong> Tampilan kalender untuk jadwal</li>
                            <li><strong>Preview Real-time:</strong> Preview jadwal saat input</li>
                            <li><strong>Validation:</strong> Cegah duplikasi dan konflik jadwal</li>
                        </ul>

                        <h6>✅ Security & Permission:</h6>
                        <ul>
                            <li><strong>Role-based Access:</strong> Admin, HR, Manager, Karyawan</li>
                            <li><strong>Permission Middleware:</strong> Kontrol akses pada setiap fitur</li>
                            <li><strong>Data Validation:</strong> Validasi input yang ketat</li>
                            <li><strong>Audit Trail:</strong> Tracking perubahan data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-rocket me-2"></i>
                    Quick Start Guide
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>1. Buat Shift Kerja</h6>
                        <p>Mulai dengan membuat shift kerja yang akan digunakan karyawan.</p>
                        <a href="{{ route('shifts.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Buat Shift
                        </a>
                    </div>
                    <div class="col-md-4">
                        <h6>2. Tambah Kantor</h6>
                        <p>Daftarkan lokasi kantor untuk sistem absensi berbasis lokasi.</p>
                        <a href="{{ route('offices.create') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-plus me-1"></i>Tambah Kantor
                        </a>
                    </div>
                    <div class="col-md-4">
                        <h6>3. Atur Jadwal</h6>
                        <p>Buat dan kelola jadwal kerja karyawan dengan shift dan lokasi yang sudah dibuat.</p>
                        <a href="{{ route('schedules.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Buat Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
