@extends('layouts.app')

@section('title', 'Manajemen Cuti - HRD')

@push('styles')
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
/* Modern Dashboard Styles */
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin: -1.5rem -1.5rem 2rem -1.5rem;
    border-radius: 0 0 20px 20px;
}

.stat-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    transition: all 0.4s ease;
    overflow: hidden;
    position: relative;
    height: 140px;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    pointer-events: none;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.stat-card.pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card.approved {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card.monthly {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card.days {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto 15px;
    backdrop-filter: blur(10px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    padding: 20px 25px;
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

.filter-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.action-btn {
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.modern-table {
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    padding: 15px 20px;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    transition: all 0.3s ease;
    border: none;
}

.modern-table tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    transform: scale(1.01);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.modern-table tbody td {
    padding: 20px;
    border: none;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

.employee-info {
    display: flex;
    align-items: center;
}

.employee-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 15px;
    font-size: 16px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.status-approved {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.status-rejected {
    background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-action {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.3s ease;
    font-size: 14px;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
@endpush

@section('content')
<!-- Dashboard Header -->
<div class="dashboard-header text-center">
    <div class="container">
        <h1 class="mb-2 fw-bold"><i class="fas fa-users-cog me-3"></i>Manajemen Cuti HRD</h1>
        <p class="mb-0 opacity-90">Dashboard untuk mengelola semua pengajuan cuti karyawan</p>
    </div>
</div>

<!-- Enhanced Statistics Cards -->
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card pending text-white {{ $stats['total_pending'] > 0 ? 'pulse-animation' : '' }}">
            <div class="card-body text-center p-4">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-number">{{ $stats['total_pending'] }}</div>
                <div class="stat-label">Menunggu Persetujuan</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card approved text-white">
            <div class="card-body text-center p-4">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number">{{ $stats['total_approved'] }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card monthly text-white">
            <div class="card-body text-center p-4">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number">{{ $stats['total_this_month'] }}</div>
                <div class="stat-label">Bulan Ini</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stat-card days text-white">
            <div class="card-body text-center p-4">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number">{{ $stats['total_days_this_month'] }}</div>
                <div class="stat-label">Hari Bulan Ini</div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filters -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card filter-card">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter & Pencarian</h5>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('permits.leave.management') }}" class="filter-form">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4">
                            <label for="status" class="form-label fw-semibold text-dark">Status</label>
                            <select name="status" id="status" class="form-select border-0 shadow-sm">
                                <option value="">🔍 Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ Disetujui</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label for="leave_type_id" class="form-label fw-semibold text-dark">Jenis Cuti</label>
                            <select name="leave_type_id" id="leave_type_id" class="form-select border-0 shadow-sm">
                                <option value="">📋 Semua Jenis</option>
                                @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <label for="start_date" class="form-label fw-semibold text-dark">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control border-0 shadow-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <label for="end_date" class="form-label fw-semibold text-dark">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control border-0 shadow-sm" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label fw-semibold text-dark">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary action-btn">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="{{ route('permits.leave.management') }}" class="btn btn-outline-secondary action-btn">
                                    <i class="fas fa-refresh me-2"></i>Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Leave Requests Table -->
<div class="row">
    <div class="col-12">
        <div class="card modern-card">
            <div class="modern-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2"></i>Semua Pengajuan Cuti
                    <span class="badge bg-light text-dark ms-2">{{ $leaves->total() ?? $leaves->count() }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light action-btn" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <button type="button" class="btn btn-success action-btn" onclick="bulkApprove()">
                        <i class="fas fa-check-double me-2"></i>Setujui Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($leaves->count() > 0)
                    <form id="bulkForm" method="POST" action="{{ route('permits.leave.bulk-approve') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Karyawan</th>
                                        <th>Jenis Cuti</th>
                                        <th>Periode</th>
                                        <th>Durasi</th>
                                        <th>Alasan</th>
                                        <th>Status</th>
                                        <th width="200">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaves as $leave)
                                    <tr>
                                        <td>
                                            @if($leave->status === 'pending')
                                            <input type="checkbox" name="leave_ids[]" value="{{ $leave->id }}" class="form-check-input leave-checkbox">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">
                                                    {{ strtoupper(substr($leave->user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($leave->user->last_name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $leave->user->employee->full_name ?? $leave->user->first_name . ' ' . $leave->user->last_name }}</div>
                                                    <small class="text-muted">{{ $leave->user->employee->employee_id ?? $leave->user->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 8px 12px; border-radius: 15px;">
                                                {{ $leave->leaveType->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $leave->start_date->format('d M Y') }}</div>
                                            <small class="text-muted">s/d {{ $leave->end_date->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 12px; border-radius: 15px;">
                                                {{ $leave->total_days }} hari
                                            </span>
                                            @if($leave->is_half_day)
                                                <br><small class="text-info fw-semibold">({{ ucfirst($leave->half_day_type) }})</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width: 200px;" class="text-dark">
                                                {{ Str::limit($leave->reason, 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($leave->status === 'pending')
                                                <span class="status-badge status-pending">⏳ Pending</span>
                                            @elseif($leave->status === 'approved')
                                                <span class="status-badge status-approved">✅ Disetujui</span>
                                            @elseif($leave->status === 'rejected')
                                                <span class="status-badge status-rejected">❌ Ditolak</span>
                                            @else
                                                <span class="status-badge" style="background: #6c757d; color: white;">{{ ucfirst($leave->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('permits.leave.show', $leave) }}" class="btn btn-action btn-outline-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('permits.leave.edit', $leave) }}" class="btn btn-action btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($leave->status === 'approved')
                                                <a href="{{ route('permits.leave.slip', $leave) }}" class="btn btn-action btn-outline-warning" title="Cetak Slip" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @endif
                                                @if($leave->status === 'pending')
                                                <form method="POST" action="{{ route('permits.leave.approve', $leave) }}" class="d-inline approve-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-action btn-outline-success" title="Setujui">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-action btn-outline-danger" title="Tolak" onclick="rejectLeave({{ $leave->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <form method="POST" action="{{ route('permits.leave.destroy', $leave) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengajuan cuti ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-action btn-outline-secondary" title="Hapus">
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

                    <!-- Enhanced Pagination -->
                    <div class="d-flex justify-content-center p-4">
                        {{ $leaves->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-times fa-3x text-white"></i>
                            </div>
                        </div>
                        <h4 class="text-dark fw-bold mb-3">Tidak ada pengajuan cuti</h4>
                        <p class="text-muted mb-4">Belum ada pengajuan cuti yang sesuai dengan filter yang dipilih.</p>
                        <a href="{{ route('permits.leave.management') }}" class="btn btn-primary action-btn">
                            <i class="fas fa-refresh me-2"></i>Reset Filter
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>



@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Enhanced JavaScript for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality with visual feedback
    const selectAllCheckbox = document.getElementById('selectAll');
    const leaveCheckboxes = document.querySelectorAll('.leave-checkbox');

    selectAllCheckbox.addEventListener('change', function() {
        leaveCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            // Add visual feedback
            const row = checkbox.closest('tr');
            if (this.checked) {
                row.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%)';
            } else {
                row.style.background = '';
            }
        });
        updateBulkActionButton();
    });

    // Individual checkbox change
    leaveCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%)';
            } else {
                row.style.background = '';
            }
            updateBulkActionButton();
        });
    });

    function updateBulkActionButton() {
        const checkedBoxes = document.querySelectorAll('.leave-checkbox:checked');
        const bulkButton = document.querySelector('button[onclick="bulkApprove()"]');
        if (bulkButton) {
            if (checkedBoxes.length > 0) {
                bulkButton.innerHTML = `<i class="fas fa-check-double me-2"></i>Setujui ${checkedBoxes.length} Terpilih`;
                bulkButton.classList.add('pulse-animation');
            } else {
                bulkButton.innerHTML = '<i class="fas fa-check-double me-2"></i>Setujui Terpilih';
                bulkButton.classList.remove('pulse-animation');
            }
        }
    }
});

function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.leave-checkbox:checked');
    if (checkedBoxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak ada yang dipilih',
            text: 'Pilih minimal satu pengajuan cuti untuk disetujui.',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Persetujuan',
        text: `Yakin ingin menyetujui ${checkedBoxes.length} pengajuan cuti?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#11998e',
        cancelButtonColor: '#fc466b',
        confirmButtonText: 'Ya, Setujui!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memproses persetujuan cuti',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('bulkForm').submit();
        }
    });
}

function exportData() {
    Swal.fire({
        title: 'Export Data',
        text: 'Pilih format export yang diinginkan',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        confirmButtonColor: '#4facfe',
        cancelButtonColor: '#667eea'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export to Excel
            window.location.href = '{{ route("permits.leave.management") }}?export=excel&' + new URLSearchParams(window.location.search);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Export to PDF
            window.location.href = '{{ route("permits.leave.management") }}?export=pdf&' + new URLSearchParams(window.location.search);
        }
    });
}

function rejectLeave(leaveId) {
    Swal.fire({
        title: 'Tolak Pengajuan Cuti',
        html: `
            <div class="text-start">
                <label for="swal-rejection-reason" class="form-label fw-semibold">Alasan Penolakan:</label>
                <textarea id="swal-rejection-reason" class="form-control" rows="4" placeholder="Masukkan alasan penolakan cuti..."></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fc466b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Tolak Cuti',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const reason = document.getElementById('swal-rejection-reason').value.trim();
            if (!reason) {
                Swal.showValidationMessage('Alasan penolakan harus diisi!');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('permits/leave') }}/${leaveId}/reject`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = result.value;

            form.appendChild(csrfToken);
            form.appendChild(reasonInput);
            document.body.appendChild(form);

            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memproses penolakan cuti',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.submit();
        }
    });
}

// Handle individual approve forms with SweetAlert
const approveForms = document.querySelectorAll('.approve-form');
approveForms.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Konfirmasi Persetujuan',
            text: 'Yakin ingin menyetujui pengajuan cuti ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#11998e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang memproses persetujuan cuti',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit the form
                form.submit();
            }
        });
    });
});

// Auto refresh notification for pending requests
const pendingCount = {{ $stats['total_pending'] ?? 0 }};
if (pendingCount > 0) {
    // Show notification every 5 minutes
    setInterval(() => {
        if (document.hidden === false) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'info',
                title: `${pendingCount} pengajuan cuti menunggu persetujuan`
            });
        }
    }, 300000); // 5 minutes
}

});
</script>
@endpush
@endsection
