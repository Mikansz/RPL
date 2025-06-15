@extends('layouts.app')

@section('title', 'Detail Jadwal Kerja')
@section('page-title', 'Detail Jadwal Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Detail Jadwal Kerja
                </h5>
                <div>
                    <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>
                        Edit
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <!-- Employee Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x text-primary"></i>
                                </div>
                                <h6 class="card-title">Karyawan</h6>
                                <p class="card-text">
                                    <strong>{{ $schedule->user->full_name }}</strong><br>
                                    <small class="text-muted">{{ $schedule->user->employee_id ?? 'N/A' }}</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Date Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-calendar fa-2x text-info"></i>
                                </div>
                                <h6 class="card-title">Tanggal</h6>
                                <p class="card-text">
                                    <strong>{{ $schedule->schedule_date->format('d F Y') }}</strong><br>
                                    <small class="text-muted">{{ $schedule->schedule_date->format('l') }}</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Shift Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                                <h6 class="card-title">Shift</h6>
                                <p class="card-text">
                                    <strong>{{ $schedule->shift->name }}</strong><br>
                                    <small class="text-muted">{{ $schedule->shift->formatted_start_time }} - {{ $schedule->shift->formatted_end_time }}</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Work Type Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="bg-{{ $schedule->work_type == 'WFO' ? 'primary' : 'success' }} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-{{ $schedule->work_type == 'WFO' ? 'building' : 'home' }} fa-2x text-{{ $schedule->work_type == 'WFO' ? 'primary' : 'success' }}"></i>
                                </div>
                                <h6 class="card-title">Tipe Kerja</h6>
                                <p class="card-text">
                                    <span class="badge bg-{{ $schedule->work_type == 'WFO' ? 'primary' : 'success' }} fs-6">
                                        {{ $schedule->work_type }}
                                    </span><br>
                                    <small class="text-muted">
                                        {{ $schedule->work_type == 'WFO' ? 'Work From Office' : 'Work From Anywhere' }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($schedule->office)
                    <!-- Office Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-map-marker-alt fa-2x text-secondary"></i>
                                </div>
                                <h6 class="card-title">Kantor</h6>
                                <p class="card-text">
                                    <strong>{{ $schedule->office->name }}</strong><br>
                                    <small class="text-muted">Radius: {{ $schedule->office->radius }}m</small>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Status Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="bg-{{ $schedule->status == 'approved' ? 'success' : 'danger' }} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-{{ $schedule->status == 'approved' ? 'check-circle' : 'times-circle' }} fa-2x text-{{ $schedule->status == 'approved' ? 'success' : 'danger' }}"></i>
                                </div>
                                <h6 class="card-title">Status</h6>
                                <p class="card-text">
                                    <span class="badge bg-{{ $schedule->status == 'approved' ? 'success' : 'danger' }} fs-6">
                                        {{ $schedule->status == 'approved' ? 'Active' : 'Cancelled' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($schedule->notes)
                <!-- Notes -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    Catatan
                                </h6>
                                <p class="card-text">{{ $schedule->notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Metadata -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informasi Tambahan
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Dibuat oleh:</strong> {{ $schedule->createdBy->full_name ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $schedule->created_at->format('d F Y H:i') }}</small>
                                        </p>
                                    </div>
                                    @if($schedule->approvedBy)
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Disetujui oleh:</strong> {{ $schedule->approvedBy->full_name }}<br>
                                            <small class="text-muted">{{ $schedule->approved_at ? $schedule->approved_at->format('d F Y H:i') : 'N/A' }}</small>
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali
                    </a>
                    <div>
                        <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            Edit Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
