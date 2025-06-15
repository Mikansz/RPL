@extends('layouts.app')

@section('title', 'Manajemen Lembur - HRD')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card stat-card-warning">
            <div class="card-body text-center">
                <i class="fas fa-hourglass-half fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_pending'] }}</h3>
                <p class="mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card-success">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_approved'] }}</h3>
                <p class="mb-0">Disetujui</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card-info">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                <h3 class="mb-1">{{ $stats['total_this_month'] }}</h3>
                <p class="mb-0">Bulan Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-primary">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-3"></i>
                <h3 class="mb-1">{{ number_format($stats['total_hours_this_month'], 1) }}</h3>
                <p class="mb-0">Jam Bulan Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card-dark">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x mb-3"></i>
                <h3 class="mb-1">Rp {{ number_format($stats['total_amount_this_month'], 0, ',', '.') }}</h3>
                <p class="mb-0">Nominal Bulan Ini</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('permits.overtime.management') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Overtime Requests Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Semua Pengajuan Lembur
                </h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                        <i class="fas fa-check-double me-2"></i>Setujui Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($overtimes->count() > 0)
                    <form id="bulkForm" method="POST" action="{{ route('permits.overtime.bulk-approve') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Karyawan</th>
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
                                            @if($overtime->status === 'pending')
                                            <input type="checkbox" name="overtime_ids[]" value="{{ $overtime->id }}" class="form-check-input overtime-checkbox">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong>{{ $overtime->user->employee->full_name ?? $overtime->user->first_name . ' ' . $overtime->user->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $overtime->user->employee->employee_id ?? $overtime->user->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $overtime->overtime_date->format('d M Y') }}</strong><br>
                                            <small class="text-muted">{{ $overtime->created_at->format('d M Y H:i') }}</small>
                                        </td>
                                        <td>
                                            {{ $overtime->start_time }} - {{ $overtime->end_time }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ number_format($overtime->planned_hours, 1) }} jam</span>
                                            @if($overtime->actual_hours)
                                                <br><small class="text-muted">Aktual: {{ number_format($overtime->actual_hours, 1) }} jam</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width: 200px;">
                                                {{ Str::limit($overtime->work_description, 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($overtime->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($overtime->status === 'approved')
                                                <span class="badge bg-success">Disetujui</span>
                                            @elseif($overtime->status === 'rejected')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($overtime->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($overtime->overtime_amount)
                                                <strong>Rp {{ number_format($overtime->overtime_amount, 0, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('permits.overtime.show', $overtime) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('permits.overtime.edit', $overtime) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($overtime->status === 'approved')
                                                <a href="{{ route('permits.overtime.slip', $overtime) }}" class="btn btn-sm btn-outline-warning" title="Cetak Slip" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @endif
                                                @if($overtime->status === 'pending')
                                                <form method="POST" action="{{ route('permits.overtime.destroy', $overtime) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengajuan lembur ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
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
                    </form>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $overtimes->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada pengajuan lembur</h5>
                        <p class="text-muted">Belum ada pengajuan lembur yang sesuai dengan filter yang dipilih.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.overtime-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.overtime-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu pengajuan lembur untuk disetujui.');
        return;
    }
    
    if (confirm(`Yakin ingin menyetujui ${checkedBoxes.length} pengajuan lembur?`)) {
        document.getElementById('bulkForm').submit();
    }
}
</script>
@endsection
