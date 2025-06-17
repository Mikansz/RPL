@extends('layouts.app')

@section('title', 'Laporan Periode Payroll')
@section('page-title', 'Laporan Periode: ' . $period->name)

@section('content')
<!-- Period Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Informasi Periode</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Nama Periode:</strong><br>
                        {{ $period->name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Periode:</strong><br>
                        {{ $period->start_date->format('d M Y') }} - {{ $period->end_date->format('d M Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Tanggal Gaji:</strong><br>
                        {{ $period->pay_date ? $period->pay_date->format('d M Y') : 'Belum ditentukan' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'calculated' => 'warning',
                                'approved' => 'success',
                                'paid' => 'info'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$period->status] ?? 'secondary' }}">
                            {{ ucfirst($period->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info h-100">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-users fa-2x text-info"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['total_employees']) }}</h3>
                <p class="text-muted mb-0">Total Karyawan</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success h-100">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                </div>
                <h3 class="mb-1">Rp {{ number_format($stats['total_net_salary'], 0, ',', '.') }}</h3>
                <p class="text-muted mb-0">Total Gaji Bersih</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary h-100">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                </div>
                <h3 class="mb-1">Rp {{ number_format($stats['average_salary'], 0, ',', '.') }}</h3>
                <p class="text-muted mb-0">Rata-rata Gaji</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning h-100">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-calculator fa-2x text-warning"></i>
                </div>
                <h3 class="mb-1">Rp {{ number_format($stats['total_gross_salary'], 0, ',', '.') }}</h3>
                <p class="text-muted mb-0">Total Gaji Kotor</p>
            </div>
        </div>
    </div>
</div>

<!-- Additional Statistics -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h5 class="text-success">Total Tunjangan</h5>
                <h3>Rp {{ number_format($stats['total_allowances'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h5 class="text-danger">Total Potongan</h5>
                <h3>Rp {{ number_format($stats['total_deductions'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h5 class="text-warning">Total Pajak</h5>
                <h3>Rp {{ number_format($stats['total_tax'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Department Breakdown -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Breakdown per Departemen</h5>
            </div>
            <div class="card-body">
                @if($departmentStats->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Departemen</th>
                                    <th>Karyawan</th>
                                    <th>Total Gaji</th>
                                    <th>Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departmentStats as $dept)
                                <tr>
                                    <td>{{ $dept['department'] }}</td>
                                    <td>{{ $dept['employee_count'] }}</td>
                                    <td>Rp {{ number_format($dept['total_salary'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($dept['average_salary'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Tidak ada data departemen.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Komponen Gaji</h5>
            </div>
            <div class="card-body">
                @if(count($componentStats) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Komponen</th>
                                    <th>Tipe</th>
                                    <th>Total</th>
                                    <th>Karyawan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($componentStats as $component)
                                <tr>
                                    <td>{{ $component['name'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $component['type'] === 'allowance' ? 'success' : 'danger' }}">
                                            {{ $component['type'] === 'allowance' ? 'Tunjangan' : 'Potongan' }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($component['total_amount'], 0, ',', '.') }}</td>
                                    <td>{{ $component['employee_count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Tidak ada data komponen gaji.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-download me-2"></i>Export & Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <a href="{{ route('payroll.periods.export', $period) }}" class="btn btn-success w-100">
                            <i class="fas fa-file-csv me-2"></i>Export CSV
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="{{ route('payroll.periods.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="button" class="btn btn-primary w-100" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Payroll List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detail Payroll Karyawan</h5>
    </div>
    <div class="card-body">
        @if($period->payrolls->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Karyawan</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Gaji Kotor</th>
                            <th>Tunjangan</th>
                            <th>Potongan</th>
                            <th>Pajak</th>
                            <th>Gaji Bersih</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($period->payrolls as $payroll)
                        <tr>
                            <td>{{ $payroll->user->employee_id ?? '-' }}</td>
                            <td>
                                <strong>{{ $payroll->user->full_name }}</strong>
                            </td>
                            <td>{{ $payroll->user->employee->department->name ?? '-' }}</td>
                            <td>Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($payroll->total_allowances, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $payroll->status === 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data payroll</h5>
                <p class="text-muted">Belum ada payroll yang dihitung untuk periode ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .card-header, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@section('content')
<!-- Header Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Periode Payroll</h4>
        <p class="text-muted mb-0">{{ $period->name }} ({{ $period->start_date->format('d M Y') }} - {{ $period->end_date->format('d M Y') }})</p>
    </div>
    <div>
        <button type="button" class="btn btn-success me-2" onclick="exportPeriod({{ $period->id }})">
            <i class="fas fa-download me-2"></i>Export CSV
        </button>
        <a href="{{ route('payroll.periods.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">{{ number_format($summary['total_employees']) }}</h3>
                        <p class="text-muted mb-0">Total Karyawan</p>
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
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">Rp {{ number_format($summary['total_gross_salary'], 0, ',', '.') }}</h3>
                        <p class="text-muted mb-0">Total Gaji Kotor</p>
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
                            <i class="fas fa-hand-holding-usd fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">Rp {{ number_format($summary['total_net_salary'], 0, ',', '.') }}</h3>
                        <p class="text-muted mb-0">Total Gaji Bersih</p>
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
                            <i class="fas fa-calculator fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1">Rp {{ number_format($summary['average_salary'], 0, ',', '.') }}</h3>
                        <p class="text-muted mb-0">Rata-rata Gaji</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h4 class="text-success mb-1">Rp {{ number_format($summary['total_allowances'], 0, ',', '.') }}</h4>
                <p class="text-muted mb-0">Total Tunjangan</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h4 class="text-danger mb-1">Rp {{ number_format($summary['total_deductions'], 0, ',', '.') }}</h4>
                <p class="text-muted mb-0">Total Potongan</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h4 class="text-warning mb-1">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</h4>
                <p class="text-muted mb-0">Total Pajak</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h4 class="text-info mb-1">Rp {{ number_format($summary['total_overtime'], 0, ',', '.') }}</h4>
                <p class="text-muted mb-0">Total Lembur</p>
            </div>
        </div>
    </div>
</div>

<!-- Department Summary -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Ringkasan per Departemen</h5>
    </div>
    <div class="card-body">
        @if($departmentSummary->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Departemen</th>
                            <th>Jumlah Karyawan</th>
                            <th>Total Gaji Kotor</th>
                            <th>Total Gaji Bersih</th>
                            <th>Rata-rata Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentSummary as $dept)
                        <tr>
                            <td><strong>{{ $dept['department'] }}</strong></td>
                            <td><span class="badge bg-primary">{{ $dept['employee_count'] }} orang</span></td>
                            <td>Rp {{ number_format($dept['total_gross'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($dept['total_net'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($dept['average_salary'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data departemen</h5>
                <p class="text-muted">Belum ada data payroll untuk periode ini.</p>
            </div>
        @endif
    </div>
</div>

<!-- Status Breakdown -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Payroll</h5>
    </div>
    <div class="card-body">
        @if($statusBreakdown->count() > 0)
            <div class="row">
                @foreach($statusBreakdown as $status)
                <div class="col-md-4 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">
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
                            <span class="badge bg-{{ $statusColors[$status['status']] ?? 'secondary' }} fs-6 mb-2">
                                {{ $statusLabels[$status['status']] ?? ucfirst($status['status']) }}
                            </span>
                            <h4 class="mb-1">{{ $status['count'] }} orang</h4>
                            <p class="text-muted mb-0">Rp {{ number_format($status['total_amount'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data status</h5>
                <p class="text-muted">Belum ada data payroll untuk periode ini.</p>
            </div>
        @endif
    </div>
</div>

<!-- Detailed Payroll List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detail Payroll Karyawan</h5>
        <small class="text-muted">{{ $period->payrolls->count() }} karyawan</small>
    </div>
    <div class="card-body">
        @if($period->payrolls->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Karyawan</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Gaji Pokok</th>
                            <th>Tunjangan</th>
                            <th>Potongan</th>
                            <th>Gaji Bersih</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($period->payrolls as $payroll)
                        <tr>
                            <td>{{ $payroll->user->employee_id ?? '-' }}</td>
                            <td>
                                <strong>{{ $payroll->user->full_name }}</strong>
                                <br><small class="text-muted">{{ $payroll->user->employee->position->name ?? '' }}</small>
                            </td>
                            <td>{{ $payroll->user->employee->department->name ?? '-' }}</td>
                            <td>Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($payroll->total_allowances, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</strong></td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'paid' => 'info'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$payroll->status] ?? 'secondary' }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <h5>Tidak ada data payroll</h5>
                <p class="text-muted">Belum ada payroll yang dihitung untuk periode ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportPeriod(periodId) {
    window.open(`{{ route('payroll.periods.export', '') }}/${periodId}`, '_blank');
}
</script>
@endpush
