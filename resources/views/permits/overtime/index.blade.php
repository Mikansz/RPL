@extends('layouts.app')

@section('title', 'Lembur')
@section('page-title', 'Daftar Pengajuan Lembur')

@section('content')
<!-- Monthly Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card-info">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-3"></i>
                <h3 class="mb-1">{{ number_format($monthlyStats['total_hours'], 1) }}</h3>
                <p class="mb-0">Total Jam Bulan Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card-success">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x mb-3"></i>
                <h3 class="mb-1">Rp {{ number_format($monthlyStats['total_amount'], 0, ',', '.') }}</h3>
                <p class="mb-0">Total Nominal Bulan Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card-warning">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $monthlyStats['total_requests'] }}</h3>
                <p class="mb-0">Total Pengajuan Bulan Ini</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock"></i> Pengajuan Lembur
                </h5>
                <a href="{{ route('permits.overtime.create') }}" class="btn btn-info">
                    <i class="fas fa-plus"></i> Ajukan Lembur
                </a>
            </div>
            <div class="card-body">
                @if($overtimes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Tanggal Lembur</th>
                                    <th>Waktu</th>
                                    <th>Durasi</th>
                                    <th>Pekerjaan</th>
                                    <th>Status</th>
                                    <th>Nominal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overtimes as $overtime)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $overtime->created_at->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $overtime->overtime_date->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $overtime->overtime_date->format('l') }}</small>
                                    </td>
                                    <td>
                                        {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                                    </td>
                                    <td>
                                        @if($overtime->actual_hours)
                                            <strong class="text-success">{{ $overtime->formatted_duration }}</strong>
                                            <br>
                                            <small class="text-muted">Aktual</small>
                                        @else
                                            <strong class="text-info">{{ number_format($overtime->planned_hours, 1) }} jam</strong>
                                            <br>
                                            <small class="text-muted">Rencana</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="max-width: 200px;">
                                            {{ Str::limit($overtime->work_description, 50) }}
                                            @if(strlen($overtime->work_description) > 50)
                                                <a href="#" data-bs-toggle="tooltip" title="{{ $overtime->work_description }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $overtime->status_badge }}">
                                            {{ $overtime->status_text }}
                                        </span>
                                        @if($overtime->approved_at)
                                            <br>
                                            <small class="text-muted">{{ $overtime->approved_at->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($overtime->overtime_amount)
                                            <strong class="text-success">Rp {{ number_format($overtime->overtime_amount, 0, ',', '.') }}</strong>
                                            @if($overtime->overtime_rate)
                                                <br>
                                                <small class="text-muted">@ Rp {{ number_format($overtime->overtime_rate, 0, ',', '.') }}/jam</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Belum dihitung</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('permits.overtime.show', $overtime) }}" 
                                               class="btn btn-outline-info" 
                                               data-bs-toggle="tooltip" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($overtime->canBeEdited())
                                                <a href="{{ route('permits.overtime.edit', $overtime) }}" 
                                                   class="btn btn-outline-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('permits.overtime.destroy', $overtime) }}" 
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
                        {{ $overtimes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Pengajuan Lembur</h5>
                        <p class="text-muted">Klik tombol "Ajukan Lembur" untuk membuat pengajuan baru.</p>
                        <a href="{{ route('permits.overtime.create') }}" class="btn btn-info">
                            <i class="fas fa-plus"></i> Ajukan Lembur
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Card -->
@if($overtimes->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Pengajuan</h6>
                <h4 class="text-info">{{ $overtimes->total() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h6 class="text-muted">Menunggu Persetujuan</h6>
                <h4 class="text-warning">{{ $overtimes->where('status', 'pending')->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h6 class="text-muted">Disetujui</h6>
                <h4 class="text-success">{{ $overtimes->where('status', 'approved')->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h6 class="text-muted">Selesai</h6>
                <h4 class="text-primary">{{ $overtimes->where('status', 'completed')->count() }}</h4>
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
                    <i class="fas fa-info-circle"></i> Panduan Lembur
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Ketentuan:</h6>
                        <ul class="small">
                            <li>Lembur dimulai setelah jam kerja normal (17:00)</li>
                            <li>Maksimal 8 jam lembur per hari</li>
                            <li>Wajib mengisi deskripsi pekerjaan yang akan dilakukan</li>
                            <li>Memerlukan persetujuan dari atasan langsung</li>
                            <li>Rate lembur 1.5x dari gaji per jam normal</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Status Pengajuan:</h6>
                        <ul class="small">
                            <li><span class="badge bg-warning">Pending</span> - Menunggu persetujuan atasan</li>
                            <li><span class="badge bg-success">Approved</span> - Disetujui dan dapat dilaksanakan</li>
                            <li><span class="badge bg-danger">Rejected</span> - Ditolak dengan alasan tertentu</li>
                            <li><span class="badge bg-info">Completed</span> - Sudah dilaksanakan dan dihitung</li>
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
