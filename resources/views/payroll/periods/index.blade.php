@extends('layouts.app')

@section('title', 'Periode Payroll')
@section('page-title', 'Manajemen Periode Payroll')

@section('content')
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $periods->total() }}</h3>
                        <p class="text-muted mb-0">Total Periode</p>
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
                        <h3 class="mb-1">{{ $periods->where('status', 'approved')->count() }}</h3>
                        <p class="text-muted mb-0">Periode Disetujui</p>
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
                        <h3 class="mb-1">{{ $periods->where('status', 'calculated')->count() }}</h3>
                        <p class="text-muted mb-0">Menunggu Approval</p>
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
                            <i class="fas fa-edit fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ $periods->where('status', 'draft')->count() }}</h3>
                        <p class="text-muted mb-0">Draft</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
@if(auth()->user()->hasPermission('payroll.create'))
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-2">
                <a href="{{ route('payroll.periods.create') }}" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Buat Periode Baru
                </a>
            </div>
            <div class="col-md-4 mb-2">
                <button type="button" class="btn btn-info w-100" onclick="exportPeriods()">
                    <i class="fas fa-download me-2"></i>Export Data
                </button>
            </div>
            <div class="col-md-4 mb-2">
                <button type="button" class="btn btn-warning w-100" onclick="generateReport()">
                    <i class="fas fa-chart-bar me-2"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Periods Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Periode Payroll</h5>
    </div>
    <div class="card-body">
        @if($periods->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Periode</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Tanggal Gaji</th>
                            <th>Total Payroll</th>
                            <th>Total Gaji</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $period)
                        <tr>
                            <td>
                                <strong>{{ $period->name }}</strong>
                            </td>
                            <td>{{ $period->start_date->format('d M Y') }}</td>
                            <td>{{ $period->end_date->format('d M Y') }}</td>
                            <td>{{ $period->pay_date ? $period->pay_date->format('d M Y') : '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $period->total_payrolls }} karyawan</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($period->total_net_salary, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'calculated' => 'warning',
                                        'approved' => 'success',
                                        'paid' => 'info'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'calculated' => 'Calculated',
                                        'approved' => 'Approved',
                                        'paid' => 'Paid'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$period->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$period->status] ?? ucfirst($period->status) }}
                                </span>
                            </td>
                            <td>
                                @if($period->approved_by)
                                    <div>
                                        <strong>{{ $period->approvedBy->full_name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $period->approved_at ? $period->approved_at->format('d M Y H:i') : '' }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('payroll.periods.calculate', $period) }}" 
                                       class="btn btn-outline-info" title="Calculate">
                                        <i class="fas fa-calculator"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('payroll.approve'))
                                        @if($period->status === 'calculated')
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="approvePeriod({{ $period->id }})" title="Approve Period">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                    @endif
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="viewPeriodDetail({{ $period->id }})" title="Detail">
                                        <i class="fas fa-eye"></i>
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
                {{ $periods->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <h5>Tidak ada periode payroll</h5>
                <p class="text-muted">Belum ada periode payroll yang dibuat.</p>
                @if(auth()->user()->hasPermission('payroll.create'))
                <a href="{{ route('payroll.periods.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Periode Pertama
                </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function approvePeriod(periodId) {
    if (confirm('Apakah Anda yakin ingin approve periode payroll ini? Semua payroll dalam periode ini akan disetujui.')) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: `/payroll/period/${periodId}/approve`
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

function viewPeriodDetail(periodId) {
    alert('Detail periode akan ditampilkan');
}

function exportPeriods() {
    alert('Fitur export akan diimplementasikan');
}

function generateReport() {
    alert('Fitur generate report akan diimplementasikan');
}
</script>
@endpush
