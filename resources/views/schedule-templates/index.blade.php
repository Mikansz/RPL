@extends('layouts.app')

@section('title', 'Template Jadwal Kerja')
@section('page-title', 'Template Jadwal Kerja')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('schedule-templates.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cari Template</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               value="{{ request('search') }}" placeholder="Cari nama template...">
                    </div>
                    <div class="col-md-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <a href="{{ route('schedule-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week me-2"></i>
                    Daftar Template Jadwal Kerja
                </h5>
                <div>
                    <a href="{{ route('holidays.index') }}" class="btn btn-info btn-sm me-2">
                        <i class="fas fa-calendar-times me-1"></i>
                        Kelola Hari Libur
                    </a>
                    <a href="{{ route('schedule-templates.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>
                        Buat Template
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Template</th>
                                    <th>Shift</th>
                                    <th>Tipe Kerja</th>
                                    <th>Hari Kerja</th>
                                    <th>Periode Berlaku</th>
                                    <th>Karyawan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <strong>{{ $template->name }}</strong>
                                        @if($template->description)
                                            <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $template->shift->name }}</span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $template->shift->start_time->format('H:i') }} - 
                                            {{ $template->shift->end_time->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($template->work_type === 'WFO')
                                            <span class="badge bg-info">WFO</span>
                                            @if($template->office)
                                                <br><small class="text-muted">{{ $template->office->name }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-success">WFA</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $template->work_days_text }}</small>
                                        @if($template->exclude_sundays)
                                            <br><small class="text-info"><i class="fas fa-ban me-1"></i>Kecuali Minggu</small>
                                        @endif
                                        @if($template->exclude_holidays)
                                            <br><small class="text-warning"><i class="fas fa-ban me-1"></i>Kecuali Libur</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($template->isPermanent())
                                            <span class="badge bg-success">
                                                <i class="fas fa-infinity me-1"></i>Berlaku Selamanya
                                            </span>
                                        @else
                                            <small>
                                                @if($template->effective_from)
                                                    <strong>Dari:</strong> {{ $template->effective_from->format('d/m/Y') }}<br>
                                                @endif
                                                @if($template->effective_until)
                                                    <strong>Sampai:</strong> {{ $template->effective_until->format('d/m/Y') }}
                                                @else
                                                    <strong>Sampai:</strong> <em>Tidak terbatas</em>
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $activeAssignments = $template->employeeAssignments()->active()->count();
                                        @endphp
                                        <span class="badge bg-secondary">{{ $activeAssignments }} orang</span>
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('schedule-templates.show', $template) }}" 
                                               class="btn btn-sm btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('schedule-templates.assign-employees', $template) }}" 
                                               class="btn btn-sm btn-outline-success" title="Assign Karyawan">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="{{ route('schedule-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('schedule-templates.destroy', $template) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus template ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $templates->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-week fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada template jadwal kerja</h5>
                        <p class="text-muted">Buat template jadwal kerja untuk mengatur jadwal karyawan secara otomatis.</p>
                        <a href="{{ route('schedule-templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Buat Template
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($templates->count() > 0)
<!-- Info Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-title text-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Cara Kerja Template Jadwal
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Template jadwal digunakan untuk membuat jadwal kerja otomatis</li>
                            <li>Karyawan dapat ditugaskan ke satu atau lebih template</li>
                            <li>Sistem akan generate jadwal sesuai hari kerja yang ditentukan</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Hari Minggu dan hari libur akan otomatis dikecualikan</li>
                            <li>Gunakan command <code>php artisan schedule:generate</code> untuk generate jadwal</li>
                            <li>Template dapat diatur periode efektifnya</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
