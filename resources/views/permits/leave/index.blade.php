@extends('layouts.app')

@section('title', 'Manajemen Cuti')
@section('page-title', 'Manajemen Cuti Modern')

@push('styles')
<style>
.leave-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    transition: all 0.4s ease;
    overflow: hidden;
    position: relative;
}

.leave-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    pointer-events: none;
}

.leave-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.leave-card.remaining {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.leave-card.approved {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.leave-card.pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.leave-card.total {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stats-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    backdrop-filter: blur(10px);
}

.quick-action-btn {
    border-radius: 15px;
    padding: 15px 25px;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.quick-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.quick-action-btn:hover::before {
    left: 100%;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.modern-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    overflow: hidden;
}

.modern-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 25px 30px;
    position: relative;
}

.modern-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57);
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
@endpush

@section('content')
<!-- Enhanced Statistics Cards -->
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card leave-card remaining text-white h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="stats-icon me-4">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h6 class="card-title mb-2 opacity-90 fw-light">Sisa Cuti</h6>
                    <h2 class="mb-1 fw-bold">{{ $totalRemainingDays ?? 12 }}</h2>
                    <small class="opacity-75 fw-medium">hari tersisa</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card leave-card approved text-white h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="stats-icon me-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h6 class="card-title mb-2 opacity-90 fw-light">Cuti Disetujui</h6>
                    <h2 class="mb-1 fw-bold">{{ $approvedLeaves ?? 0 }}</h2>
                    <small class="opacity-75 fw-medium">hari disetujui</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card leave-card pending text-white h-100 {{ ($pendingLeaves ?? 0) > 0 ? 'pulse-animation' : '' }}">
            <div class="card-body d-flex align-items-center p-4">
                <div class="stats-icon me-4">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h6 class="card-title mb-2 opacity-90 fw-light">Menunggu</h6>
                    <h2 class="mb-1 fw-bold">{{ $pendingLeaves ?? 0 }}</h2>
                    <small class="opacity-75 fw-medium">hari pending</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card leave-card total text-white h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="stats-icon me-4">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <h6 class="card-title mb-2 opacity-90 fw-light">Total Permohonan</h6>
                    <h2 class="mb-1 fw-bold">{{ $totalLeaves ?? 0 }}</h2>
                    <small class="opacity-75 fw-medium">permohonan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card modern-card">
            <div class="modern-card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-3"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('permits.leave.create') }}" class="btn btn-primary quick-action-btn w-100">
                            <i class="fas fa-plus me-2"></i>Ajukan Cuti Baru
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <button type="button" class="btn btn-success quick-action-btn w-100" onclick="showCalendarView()">
                            <i class="fas fa-calendar-alt me-2"></i>Lihat Kalender
                        </button>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <button type="button" class="btn btn-info quick-action-btn w-100" onclick="exportLeaveData()">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <button type="button" class="btn btn-warning quick-action-btn w-100" onclick="showLeaveBalance()">
                            <i class="fas fa-chart-pie me-2"></i>Saldo Cuti
                        </button>
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
