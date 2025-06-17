@extends('layouts.app')

@section('title', 'Detail Template Jadwal Kerja')
@section('page-title', 'Detail Template Jadwal Kerja')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Template Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week me-2"></i>
                    {{ $scheduleTemplate->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nama Template:</strong></td>
                                <td>{{ $scheduleTemplate->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Deskripsi:</strong></td>
                                <td>{{ $scheduleTemplate->description ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Shift:</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $scheduleTemplate->shift->name }}</span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $scheduleTemplate->shift->start_time->format('H:i') }} - 
                                        {{ $scheduleTemplate->shift->end_time->format('H:i') }}
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tipe Kerja:</strong></td>
                                <td>
                                    @if($scheduleTemplate->work_type === 'WFO')
                                        <span class="badge bg-info">WFO (Work From Office)</span>
                                        @if($scheduleTemplate->office)
                                            <br><small class="text-muted">{{ $scheduleTemplate->office->name }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-success">WFA (Work From Anywhere)</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Hari Kerja:</strong></td>
                                <td>{{ $scheduleTemplate->work_days_text }}</td>
                            </tr>
                            <tr>
                                <td><strong>Periode Berlaku:</strong></td>
                                <td>
                                    @if($scheduleTemplate->isPermanent())
                                        <span class="badge bg-success">
                                            <i class="fas fa-infinity me-1"></i>Berlaku Selamanya
                                        </span>
                                        <br><small class="text-muted">Template ini berlaku tanpa batas waktu</small>
                                    @else
                                        @if($scheduleTemplate->effective_from)
                                            <strong>Dari:</strong> {{ $scheduleTemplate->effective_from->format('d/m/Y') }}<br>
                                        @endif
                                        @if($scheduleTemplate->effective_until)
                                            <strong>Sampai:</strong> {{ $scheduleTemplate->effective_until->format('d/m/Y') }}
                                        @else
                                            <strong>Sampai:</strong> <em>Tidak terbatas</em>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pengaturan:</strong></td>
                                <td>
                                    @if($scheduleTemplate->exclude_sundays)
                                        <span class="badge bg-info">Kecuali Minggu</span><br>
                                    @endif
                                    @if($scheduleTemplate->exclude_holidays)
                                        <span class="badge bg-warning">Kecuali Libur</span><br>
                                    @endif
                                    @if($scheduleTemplate->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td>{{ $scheduleTemplate->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Employees -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Karyawan yang Ditugaskan
                </h6>
                <a href="{{ route('schedule-templates.assign-employees', $scheduleTemplate) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Assign Karyawan
                </a>
            </div>
            <div class="card-body">
                @if($scheduleTemplate->employees->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Periode Penugasan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduleTemplate->employees as $employee)
                                <tr>
                                    <td>{{ $employee->full_name }}</td>
                                    <td>{{ $employee->employee->department->name ?? '-' }}</td>
                                    <td>
                                        <small>
                                            {{ $employee->pivot->assigned_from->format('d/m/Y') }} - 
                                            {{ $employee->pivot->assigned_until ? $employee->pivot->assigned_until->format('d/m/Y') : 'Tidak terbatas' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($employee->pivot->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('schedule-templates.remove-employee', [$scheduleTemplate, $employee->pivot->id]) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus penugasan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Belum ada karyawan yang ditugaskan ke template ini.</p>
                        <a href="{{ route('schedule-templates.assign-employees', $scheduleTemplate) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Assign Karyawan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cog me-2"></i>
                    Aksi
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('schedule-templates.edit', $scheduleTemplate) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Template
                    </a>
                    <a href="{{ route('schedule-templates.assign-employees', $scheduleTemplate) }}" class="btn btn-success">
                        <i class="fas fa-users me-1"></i> Assign Karyawan
                    </a>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#generateModal">
                        <i class="fas fa-calendar-plus me-1"></i> Generate Jadwal
                    </button>
                    <hr>
                    <form action="{{ route('schedule-templates.destroy', $scheduleTemplate) }}" 
                          method="POST" onsubmit="return confirm('Yakin ingin menghapus template ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-1"></i> Hapus Template
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Template Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informasi Template
                </h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <p><strong>Template Jadwal Kerja</strong> digunakan untuk membuat jadwal kerja secara otomatis berdasarkan pola yang telah ditentukan.</p>
                    
                    @if($scheduleTemplate->isPermanent())
                        <p><strong>Template Permanen:</strong> Template ini berlaku selamanya tanpa batas waktu. Jadwal akan terus di-generate sesuai hari kerja yang ditentukan.</p>
                    @else
                        <p><strong>Template Terbatas:</strong> Template ini hanya berlaku pada periode tertentu sesuai tanggal yang telah ditentukan.</p>
                    @endif
                    
                    <p>Gunakan tombol "Generate Jadwal" untuk membuat jadwal kerja berdasarkan template ini.</p>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Generate Schedule Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('schedule-templates.generate-schedules', $scheduleTemplate) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Jadwal akan di-generate untuk semua karyawan yang ditugaskan ke template ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Generate Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <a href="{{ route('schedule-templates.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Template
        </a>
    </div>
</div>
@endsection
