@extends('layouts.app')

@section('title', 'Daftar Cuti')
@section('page-title', 'Daftar Permohonan Cuti')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Sisa Cuti</h6>
                        <h4 class="mb-0">{{ $totalRemainingDays ?? 12 }} hari</h4>
                    </div>
                    <div>
                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Cuti Disetujui</h6>
                        <h4 class="mb-0">{{ $approvedLeaves ?? 0 }} hari</h4>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Menunggu Persetujuan</h6>
                        <h4 class="mb-0">{{ $pendingLeaves ?? 0 }}</h4>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Permohonan</h6>
                        <h4 class="mb-0">{{ $totalLeaves ?? 0 }}</h4>
                    </div>
                    <div>
                        <i class="fas fa-file-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Daftar Permohonan Cuti</h5>
        <a href="{{ route('permits.leave.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Ajukan Cuti
        </a>
    </div>
    <div class="card-body">
        @if(isset($leaves) && $leaves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <th>Periode Cuti</th>
                            <th>Durasi</th>
                            <th>Jenis Cuti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td>
                                <strong>{{ $leave->created_at->format('d M Y') }}</strong><br>
                                <small class="text-muted">{{ $leave->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $leave->start_date->format('d M Y') }}</strong><br>
                                <small class="text-muted">s/d {{ $leave->end_date->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $leave->total_days }} hari</span>
                            </td>
                            <td>
                                @if($leave->leaveType)
                                    <span class="badge bg-info">{{ $leave->leaveType->name }}</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->status == 'pending')
                                    <span class="badge bg-warning">Menunggu</span>
                                @elseif($leave->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @elseif($leave->status == 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($leave->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('permits.leave.show', $leave) }}"
                                       class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($leave->status === 'approved')
                                        <a href="{{ route('permits.leave.slip', $leave) }}"
                                           class="btn btn-sm btn-outline-success" title="Cetak Slip" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endif

                                    @if($leave->status == 'pending' && $leave->canBeEdited())
                                        <a href="{{ route('permits.leave.edit', $leave) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteLeave({{ $leave->id }})" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(method_exists($leaves, 'links'))
            <div class="d-flex justify-content-center mt-3">
                {{ $leaves->links() }}
            </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada permohonan cuti</h5>
                <p class="text-muted">Klik tombol "Ajukan Cuti" untuk membuat permohonan baru.</p>
                <a href="{{ route('permits.leave.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Ajukan Cuti
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Leave Types Info -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Jenis Cuti</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <span class="badge bg-primary me-2">Cuti Tahunan</span>
                        12 hari per tahun
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-success me-2">Cuti Sakit</span>
                        Sesuai kebutuhan (dengan surat dokter)
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-info me-2">Cuti Melahirkan</span>
                        3 bulan untuk karyawan wanita
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-warning me-2">Cuti Khusus</span>
                        Pernikahan, kematian keluarga, dll
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Saldo Cuti Tahun Ini</h6>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 20px;">
                    @php
                        $totalDays = 12;
                        $remainingDays = $totalRemainingDays ?? 12;
                        $usedDays = $totalDays - $remainingDays;
                        $usedPercentage = $totalDays > 0 ? ($usedDays / $totalDays) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ 100 - $usedPercentage }}%"
                         aria-valuenow="{{ 100 - $usedPercentage }}"
                         aria-valuemin="0" aria-valuemax="100">
                        {{ $remainingDays }} hari tersisa
                    </div>
                </div>
                <small class="text-muted">
                    Dari total {{ $totalDays }} hari cuti tahunan, Anda telah menggunakan {{ $usedDays }} hari.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Delete Forms (Hidden) -->
@if(isset($leaves))
@foreach($leaves as $leave)
<form id="deleteForm{{ $leave->id }}" method="POST" action="{{ route('permits.leave.destroy', $leave) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endif
@endsection

@push('scripts')
<script>
function deleteLeave(id) {
    if (confirm('Apakah Anda yakin ingin menghapus permohonan cuti ini?')) {
        document.getElementById('deleteForm' + id).submit();
    }
}

// Auto refresh page every 30 seconds to update status
setInterval(function() {
    // Only refresh if there are pending requests
    const pendingCount = {{ $pendingLeaves ?? 0 }};
    if (pendingCount > 0) {
        // Check if user is not actively interacting with the page
        if (document.hidden === false && !document.querySelector('.modal.show')) {
            location.reload();
        }
    }
}, 30000);
</script>
@endpush
