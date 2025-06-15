@extends('layouts.app')

@section('title', 'Manajemen Shift')
@section('page-title', 'Manajemen Shift Kerja')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-business-time me-2"></i>
                    Daftar Shift Kerja
                </h5>
                <a href="{{ route('shifts.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Tambah Shift
                </a>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Shift</th>
                                <th>Waktu Mulai</th>
                                <th>Waktu Selesai</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                            <tr>
                                <td>
                                    <strong>{{ $shift->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $shift->formatted_start_time }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $shift->formatted_end_time }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $shift->shift_duration }}</span>
                                </td>
                                <td>
                                    @if($shift->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('shifts.show', $shift) }}" class="btn btn-outline-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-outline-primary" title="Edit Shift">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('shifts.destroy', $shift) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"
                                                    onclick="return confirm('Hapus shift ini?')" title="Hapus Shift">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-business-time fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada shift yang terdaftar</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $shifts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
