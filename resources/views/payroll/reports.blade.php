@extends('layouts.app')

@section('title', 'Laporan Payroll')
@section('page-title', 'Laporan Payroll')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('payroll.reports') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportReport()">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
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
                                <h4 class="mb-1">{{ $summary['total_employees'] }}</h4>
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
                                <h4 class="mb-1">Rp {{ number_format($summary['total_gross_salary'], 0, ',', '.') }}</h4>
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
                                <h4 class="mb-1">Rp {{ number_format($summary['total_net_salary'], 0, ',', '.') }}</h4>
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
                                    <i class="fas fa-percentage fa-2x text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</h4>
                                <p class="text-muted mb-0">Total Pajak</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detail Laporan Payroll</h5>
                <span class="badge bg-primary">{{ $payrolls->count() }} record</span>
            </div>
            <div class="card-body">
                @if($payrolls->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Karyawan</th>
                                    <th>Periode</th>
                                    <th>Gaji Pokok</th>
                                    <th>Tunjangan</th>
                                    <th>Lembur</th>
                                    <th>Gaji Kotor</th>
                                    <th>Potongan</th>
                                    <th>Pajak</th>
                                    <th>Gaji Bersih</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payrolls as $index => $payroll)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $payroll->user->name }}</h6>
                                            <small class="text-muted">{{ $payroll->user->employee->department->name ?? '-' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $payroll->payrollPeriod->name }}</strong><br>
                                            <small class="text-muted">{{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</small>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                                    <td>
                                        @if($payroll->total_allowances > 0)
                                            <span class="text-success">Rp {{ number_format($payroll->total_allowances, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payroll->overtime_amount > 0)
                                            <span class="text-info">Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><strong>Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($payroll->total_deductions > 0)
                                            <span class="text-danger">Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payroll->tax_amount > 0)
                                            <span class="text-warning">Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><strong class="text-success">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($payroll->status == 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($payroll->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($payroll->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($payroll->status == 'paid')
                                            <span class="badge bg-info">Paid</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">TOTAL</th>
                                    <th>Rp {{ number_format($payrolls->sum('basic_salary'), 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($payrolls->sum('total_allowances'), 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($payrolls->sum('overtime_amount'), 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($payrolls->sum('gross_salary'), 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($payrolls->sum('total_deductions'), 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($payrolls->sum('tax_amount'), 0, ',', '.') }}</th>
                                    <th>Rp {{ number_format($payrolls->sum('net_salary'), 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5>Tidak ada data payroll</h5>
                        <p class="text-muted">Tidak ada data payroll untuk periode yang dipilih.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportReport() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const url = `{{ route('payroll.reports.export') }}?start_date=${startDate}&end_date=${endDate}`;
    window.open(url, '_blank');
}
</script>
@endpush
