@extends('layouts.app')

@section('title', 'Detail Departemen')
@section('page-title', 'Detail Departemen - ' . $department->name)

@section('content')
<div class="row">
    <!-- Department Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informasi Departemen</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-building fa-2x text-primary"></i>
                    </div>
                    <h4>{{ $department->name }}</h4>
                    <span class="badge bg-{{ $department->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $department->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>

                <table class="table table-borderless">
                    <tr>
                        <td class="fw-bold">Kode:</td>
                        <td>{{ $department->code }}</td>
                    </tr>

                    <tr>
                        <td class="fw-bold">Dibuat:</td>
                        <td>{{ $department->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Diperbarui:</td>
                        <td>{{ $department->updated_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>

                @if($department->description)
                <div class="mt-3">
                    <h6>Deskripsi:</h6>
                    <p class="text-muted">{{ $department->description }}</p>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-4">
                    @if(auth()->user()->hasPermission('departments.edit'))
                    <a href="{{ route('departments.edit', $department) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Departemen
                    </a>
                    @endif
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $department->employees->count() }}</h4>
                        <small class="text-muted">Karyawan</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $department->positions->count() }}</h4>
                        <small class="text-muted">Posisi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Karyawan ({{ $department->employees->count() }})</h5>
                @if(auth()->user()->hasPermission('employees.create'))
                <a href="{{ route('employees.create') }}?department_id={{ $department->id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Tambah Karyawan
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($department->employees->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Posisi</th>
                                    <th>Status</th>
                                    <th>Bergabung</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($department->employees as $employee)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $employee->user->full_name }}</h6>
                                                <small class="text-muted">{{ $employee->user->employee_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($employee->position)
                                            <span class="badge bg-info">{{ $employee->position->name }}</span>
                                        @else
                                            <span class="text-muted">Belum ada posisi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($employee->employment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $employee->hire_date->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h6>Belum ada karyawan</h6>
                        <p class="text-muted">Departemen ini belum memiliki karyawan.</p>
                        <a href="{{ route('employees.create') }}?department_id={{ $department->id }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Karyawan Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Positions List -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Daftar Posisi ({{ $department->positions->count() }})</h5>
                <a href="{{ route('positions.create') }}?department_id={{ $department->id }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i>Tambah Posisi
                </a>
            </div>
            <div class="card-body">
                @if($department->positions->count() > 0)
                    <div class="row">
                        @foreach($department->positions as $position)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $position->name }}</h6>
                                    <p class="card-text small text-muted">{{ $position->description ?? 'Tidak ada deskripsi' }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Level: {{ $position->level }}</small>
                                        <a href="{{ route('positions.show', $position) }}" class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                        <h6>Belum ada posisi</h6>
                        <p class="text-muted">Departemen ini belum memiliki posisi.</p>
                        <a href="{{ route('positions.create') }}?department_id={{ $department->id }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Tambah Posisi Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
