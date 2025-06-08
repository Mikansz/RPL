@extends('layouts.app')

@section('title', 'Tukar Hari')
@section('page-title', 'Daftar Pengajuan Tukar Hari')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt"></i> Pengajuan Tukar Hari
                </h5>
                <a href="{{ route('permits.day-exchange.create') }}" class="btn btn-warning">
                    <i class="fas fa-plus"></i> Ajukan Tukar Hari
                </a>
            </div>
            <div class="card-body">
                @if($exchanges->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Hari Kerja Ditukar</th>
                                    <th>Hari Pengganti</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Disetujui Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exchanges as $exchange)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $exchange->created_at->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $exchange->original_work_date->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $exchange->original_work_date->format('l') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $exchange->replacement_date->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $exchange->replacement_date->format('l') }}</small>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px;">
                                            {{ Str::limit($exchange->reason, 50) }}
                                            @if(strlen($exchange->reason) > 50)
                                                <a href="#" data-bs-toggle="tooltip" title="{{ $exchange->reason }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $exchange->status_badge }}">
                                            {{ $exchange->status_text }}
                                        </span>
                                        @if($exchange->approved_at)
                                            <br>
                                            <small class="text-muted">{{ $exchange->approved_at->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($exchange->approvedBy)
                                            {{ $exchange->approvedBy->full_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('permits.day-exchange.show', $exchange) }}" 
                                               class="btn btn-outline-info" 
                                               data-bs-toggle="tooltip" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($exchange->canBeEdited())
                                                <a href="{{ route('permits.day-exchange.edit', $exchange) }}" 
                                                   class="btn btn-outline-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('permits.day-exchange.destroy', $exchange) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger" 
                                                            data-bs-toggle="tooltip" 
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $exchanges->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Pengajuan Tukar Hari</h5>
                        <p class="text-muted">Klik tombol "Ajukan Tukar Hari" untuk membuat pengajuan baru.</p>
                        <a href="{{ route('permits.day-exchange.create') }}" class="btn btn-warning">
                            <i class="fas fa-plus"></i> Ajukan Tukar Hari
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Card -->
@if($exchanges->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Pengajuan</h6>
                <h4 class="text-warning">{{ $exchanges->total() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h6 class="text-muted">Menunggu Persetujuan</h6>
                <h4 class="text-info">{{ $exchanges->where('status', 'pending')->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h6 class="text-muted">Disetujui</h6>
                <h4 class="text-success">{{ $exchanges->where('status', 'approved')->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h6 class="text-muted">Ditolak</h6>
                <h4 class="text-danger">{{ $exchanges->where('status', 'rejected')->count() }}</h4>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Help Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Panduan Tukar Hari
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Ketentuan:</h6>
                        <ul class="small">
                            <li>Hari kerja yang ditukar harus hari Senin - Jumat</li>
                            <li>Hari pengganti harus hari Sabtu atau Minggu</li>
                            <li>Pengajuan minimal H-1 dari tanggal yang akan ditukar</li>
                            <li>Memerlukan persetujuan dari atasan langsung</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Status Pengajuan:</h6>
                        <ul class="small">
                            <li><span class="badge bg-warning">Pending</span> - Menunggu persetujuan atasan</li>
                            <li><span class="badge bg-success">Approved</span> - Disetujui dan dapat dilaksanakan</li>
                            <li><span class="badge bg-danger">Rejected</span> - Ditolak dengan alasan tertentu</li>
                            <li><span class="badge bg-info">Completed</span> - Sudah dilaksanakan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
