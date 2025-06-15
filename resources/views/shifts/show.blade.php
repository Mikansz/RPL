@extends('layouts.app')

@section('title', 'Detail Shift')
@section('page-title', 'Detail Shift Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-business-time me-2"></i>
                    Detail Shift: {{ $shift->name }}
                </h5>
                <div>
                    <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>
                        Edit Shift
                    </a>
                    <a href="{{ route('shifts.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Shift</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Nama Shift:</strong></td>
                                <td>{{ $shift->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Mulai:</strong></td>
                                <td>
                                    <span class="badge bg-info">{{ $shift->formatted_start_time }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Selesai:</strong></td>
                                <td>
                                    <span class="badge bg-warning">{{ $shift->formatted_end_time }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Durasi:</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $shift->shift_duration }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($shift->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td>{{ $shift->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Diperbarui:</strong></td>
                                <td>{{ $shift->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6>Statistik Penggunaan</h6>
                        @php
                            $totalSchedules = $shift->schedules()->count();
                            $activeSchedules = $shift->schedules()
                                                   ->where('status', 'approved')
                                                   ->where('schedule_date', '>=', today())
                                                   ->count();
                            $todaySchedules = $shift->schedules()
                                                   ->where('schedule_date', today())
                                                   ->where('status', 'approved')
                                                   ->count();
                        @endphp
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="bg-primary bg-opacity-10 rounded p-3 mb-2">
                                    <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                                </div>
                                <h6>Total Jadwal</h6>
                                <p class="mb-0 h5">{{ $totalSchedules }}</p>
                            </div>
                            <div class="col-4">
                                <div class="bg-success bg-opacity-10 rounded p-3 mb-2">
                                    <i class="fas fa-calendar-check fa-2x text-success"></i>
                                </div>
                                <h6>Jadwal Aktif</h6>
                                <p class="mb-0 h5">{{ $activeSchedules }}</p>
                            </div>
                            <div class="col-4">
                                <div class="bg-info bg-opacity-10 rounded p-3 mb-2">
                                    <i class="fas fa-calendar-day fa-2x text-info"></i>
                                </div>
                                <h6>Hari Ini</h6>
                                <p class="mb-0 h5">{{ $todaySchedules }}</p>
                            </div>
                        </div>

                        @if($activeSchedules > 0)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Shift ini memiliki {{ $activeSchedules }} jadwal aktif yang akan datang.
                        </div>
                        @endif
                    </div>
                </div>

                @if($shift->schedules()->count() > 0)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>Jadwal Terkait (10 Terbaru)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Karyawan</th>
                                        <th>Tipe Kerja</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->schedules()->with('user')->orderBy('schedule_date', 'desc')->limit(10)->get() as $schedule)
                                    <tr>
                                        <td>{{ $schedule->schedule_date->format('d/m/Y') }}</td>
                                        <td>{{ $schedule->user->full_name }}</td>
                                        <td>
                                            @if($schedule->work_type == 'WFO')
                                                <span class="badge bg-primary">WFO</span>
                                            @else
                                                <span class="badge bg-success">WFA</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->status == 'approved')
                                                <span class="badge bg-success">Disetujui</span>
                                            @elseif($schedule->status == 'cancelled')
                                                <span class="badge bg-danger">Dibatalkan</span>
                                            @else
                                                <span class="badge bg-warning">Terjadwal</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($shift->schedules()->count() > 10)
                        <div class="text-center">
                            <a href="{{ route('schedules.index', ['shift_id' => $shift->id]) }}" class="btn btn-outline-primary btn-sm">
                                Lihat Semua Jadwal
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
