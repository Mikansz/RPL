@extends('layouts.app')

@section('title', 'Manajemen Kantor')
@section('page-title', 'Manajemen Kantor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    Daftar Kantor
                </h5>
                <a href="{{ route('offices.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Tambah Kantor
                </a>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Kantor</th>
                                <th>Koordinat</th>
                                <th>Radius</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offices as $office)
                            <tr>
                                <td>
                                    <strong>{{ $office->name }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Lat: {{ $office->latitude }}<br>
                                        Lng: {{ $office->longitude }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $office->radius }}m</span>
                                </td>
                                <td>
                                    @if($office->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('offices.show', $office) }}" class="btn btn-outline-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('offices.edit', $office) }}" class="btn btn-outline-primary" title="Edit Kantor">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('offices.destroy', $office) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"
                                                    onclick="return confirm('Hapus kantor ini?')" title="Hapus Kantor">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada kantor yang terdaftar</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $offices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
