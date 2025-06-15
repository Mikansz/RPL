@extends('layouts.app')

@section('title', 'Penggajian')
@section('page-title', 'Manajemen Penggajian')

@section('content')
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">Rp {{ number_format($total_payroll ?? 0, 0, ',', '.') }}</h4>
                        <p class="text-muted mb-0">Total Payroll Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $processed_count ?? 0 }}</h3>
                        <p class="text-muted mb-0">Gaji Diproses</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $pending_count ?? 0 }}</h3>
                        <p class="text-muted mb-0">Pending Approval</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $total_employees ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Karyawan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="/periods" class="btn btn-primary w-100">
                    <i class="fas fa-calendar-alt me-2"></i>Kelola Periode
                </a>
            </div>
            @if(auth()->user()->hasPermission('payroll.approve'))
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-success w-100" onclick="approveAll()">
                    <i class="fas fa-check-double me-2"></i>Approve All
                </button>
            </div>
            @endif
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-info w-100" onclick="exportPayroll()">
                    <i class="fas fa-download me-2"></i>Export Excel
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-warning w-100" onclick="sendSlips()">
                    <i class="fas fa-envelope me-2"></i>Send Slip Gaji
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payroll.index') }}">
            <div class="row">
                <div class="col-md-2">
                    <label for="period" class="form-label">Periode</label>
                    <select class="form-select" name="period">
                        <option value="">Pilih Periode</option>
                        @for($i = 0; $i < 12; $i++)
                            @php
                                $date = now()->subMonths($i);
                                $value = $date->format('Y-m');
                                $label = $date->format('F Y');
                            @endphp
                            <option value="{{ $value }}" {{ request('period') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="department" class="form-label">Departemen</label>
                    <select class="form-select" name="department">
                        <option value="">Semua Departemen</option>
                        <!-- Options would be populated from controller -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari Karyawan</label>
                    <input type="text" class="form-control" name="search" 
                           placeholder="Nama atau Employee ID..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Payroll Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Data Penggajian</h5>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAll">
            <label class="form-check-label" for="selectAll">
                Pilih Semua
            </label>
        </div>
    </div>
    <div class="card-body">
        @if(isset($payrolls) && $payrolls->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllHeader">
                            </th>
                            <th>Karyawan</th>
                            <th>Periode</th>
                            <th>Gaji Pokok</th>
                            <th>Tunjangan</th>
                            <th>Potongan</th>
                            <th>Gaji Bersih</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $payroll)
                        <tr>
                            <td>
                                <input type="checkbox" class="payroll-checkbox" value="{{ $payroll->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $payroll->user->full_name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $payroll->user->employee_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $payroll->payrollPeriod->name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $payroll->payrollPeriod->period_start ? \Carbon\Carbon::parse($payroll->payrollPeriod->period_start)->format('M Y') : '' }}</small>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($payroll->basic_salary ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <span class="text-success">Rp {{ number_format($payroll->total_allowances ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="text-danger">Rp {{ number_format($payroll->total_deductions ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <strong class="text-primary">Rp {{ number_format($payroll->net_salary ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'paid' => 'info'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'paid' => 'Paid'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$payroll->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$payroll->status] ?? ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td>
                                @if($payroll->approved_by)
                                    <div>
                                        <strong>{{ $payroll->approvedBy->full_name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $payroll->approved_at ? $payroll->approved_at->format('d M Y H:i') : '' }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info"
                                            onclick="viewDetail({{ $payroll->id }})" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(auth()->user()->hasPermission('payroll.approve'))
                                        @if(in_array($payroll->status, ['draft', 'pending']))
                                        <button type="button" class="btn btn-outline-success"
                                                onclick="approvePayroll({{ $payroll->id }})" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                    @endif
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="downloadSlip({{ $payroll->id }})" title="Download Slip">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="sendSlip({{ $payroll->id }})" title="Send Email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $payrolls->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data payroll</h5>
                <p class="text-muted">Belum ada data penggajian untuk periode yang dipilih.</p>
                <a href="/periods/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Periode Payroll
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Slip Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generatePayroll() {
    // Redirect to periods page to create a new period first
    window.location.href = '/periods';
}

function approveAll() {
    const selected = $('.payroll-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (selected.length === 0) {
        alert('Pilih payroll yang ingin diapprove');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin approve ${selected.length} payroll?`)) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("payroll.bulk.approve") }}'
        });

        // Add CSRF token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // Add selected payroll IDs
        selected.forEach(function(id) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'payroll_ids[]',
                value: id
            }));
        });

        // Submit form
        $('body').append(form);
        form.submit();
    }
}

function exportPayroll() {
    alert('Fitur export akan diimplementasikan');
}

function sendSlips() {
    alert('Fitur send slip gaji akan diimplementasikan');
}

function viewDetail(payrollId) {
    // Redirect to the show page instead of modal
    window.location.href = `/payroll/${payrollId}`;
}

function approvePayroll(payrollId) {
    if (confirm('Apakah Anda yakin ingin approve payroll ini?')) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: `/payroll/${payrollId}/approve`
        });

        // Add CSRF token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // Submit form
        $('body').append(form);
        form.submit();
    }
}

function downloadSlip(payrollId) {
    window.open(`/payroll/slip/${payrollId}/download`, '_blank');
}

function sendSlip(payrollId) {
    if (confirm('Apakah Anda yakin ingin mengirim slip gaji via email?')) {
        // This would implement email sending functionality
        alert('Fitur send slip via email akan segera diimplementasikan');
    }
}

$(document).ready(function() {
    // Select all functionality
    $('#selectAll, #selectAllHeader').change(function() {
        $('.payroll-checkbox').prop('checked', this.checked);
    });
    
    $('.payroll-checkbox').change(function() {
        const total = $('.payroll-checkbox').length;
        const checked = $('.payroll-checkbox:checked').length;
        
        $('#selectAll, #selectAllHeader').prop('checked', total === checked);
        $('#selectAll, #selectAllHeader').prop('indeterminate', checked > 0 && checked < total);
    });
});
</script>
@endpush
