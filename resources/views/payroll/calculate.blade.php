@extends('layouts.app')

@section('title', 'Hitung Payroll')
@section('page-title', 'Hitung Payroll - ' . $period->name)

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Period Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Informasi Periode</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Nama Periode:</strong><br>
                        {{ $period->name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Tanggal Mulai:</strong><br>
                        {{ $period->start_date->format('d/m/Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Tanggal Selesai:</strong><br>
                        {{ $period->end_date->format('d/m/Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-warning">{{ ucfirst($period->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Components Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-coins me-2"></i>Ringkasan Komponen Gaji</h5>
            </div>
            <div class="card-body">
                @php
                    $totalEmployees = $employees->count();
                    $employeesWithComponents = $employees->filter(function($emp) { return $emp->user->salaryComponents->count() > 0; })->count();
                    $employeesWithoutComponents = $totalEmployees - $employeesWithComponents;

                    $totalAllowances = 0;
                    $totalDeductions = 0;
                    $totalGrossEstimate = 0;

                    foreach($employees as $employee) {
                        $basicSalary = $employee->basic_salary;
                        $totalGrossEstimate += $basicSalary;

                        foreach($employee->user->salaryComponents as $component) {
                            $amount = $component->pivot->amount ?? $component->calculateAmount($basicSalary);
                            if($component->type === 'allowance') {
                                $totalAllowances += $amount;
                                $totalGrossEstimate += $amount;
                            } elseif($component->type === 'deduction') {
                                $totalDeductions += $amount;
                            }
                        }
                    }
                    $totalNetEstimate = $totalGrossEstimate - $totalDeductions;
                @endphp

                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary mb-1">{{ $totalEmployees }}</h4>
                            <p class="text-muted mb-0">Total Karyawan</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success mb-1">{{ $employeesWithComponents }}</h4>
                            <p class="text-muted mb-0">Dengan Komponen</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning mb-1">{{ $employeesWithoutComponents }}</h4>
                            <p class="text-muted mb-0">Tanpa Komponen</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info mb-1">Rp {{ number_format($totalNetEstimate, 0, ',', '.') }}</h4>
                            <p class="text-muted mb-0">Estimasi Total Gaji</p>
                        </div>
                    </div>
                </div>

                @if($employeesWithoutComponents > 0)
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> {{ $employeesWithoutComponents }} karyawan belum memiliki komponen gaji.
                    Pastikan untuk mengatur komponen gaji sebelum memproses payroll.
                </div>
                @endif
            </div>
        </div>

        <!-- Employees Detail -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detail Karyawan & Komponen Gaji</h5>
                <div>
                    <button type="button" class="btn btn-success" onclick="processPayroll()">
                        <i class="fas fa-calculator me-2"></i>Proses Payroll
                    </button>
                    <a href="{{ route('payroll.periods.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($employees->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%"></th>
                                    <th>Nama Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Gaji Pokok</th>
                                    <th>Tunjangan</th>
                                    <th>Potongan</th>
                                    <th>Estimasi Gaji Bersih</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                @php
                                    $basicSalary = $employee->basic_salary;
                                    $allowances = $employee->user->salaryComponents->where('type', 'allowance');
                                    $deductions = $employee->user->salaryComponents->where('type', 'deduction');

                                    $totalAllowance = 0;
                                    $totalDeduction = 0;

                                    foreach($allowances as $component) {
                                        $totalAllowance += $component->pivot->amount ?? $component->calculateAmount($basicSalary);
                                    }

                                    foreach($deductions as $component) {
                                        $totalDeduction += $component->pivot->amount ?? $component->calculateAmount($basicSalary);
                                    }

                                    $grossSalary = $basicSalary + $totalAllowance;
                                    $netSalary = $grossSalary - $totalDeduction;
                                @endphp
                                <tr>
                                    <td>
                                        @if($employee->user->salaryComponents->count() > 0)
                                            <button class="btn btn-sm btn-outline-primary" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#components-{{ $employee->id }}"
                                                    aria-expanded="false">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <div class="avatar-title bg-primary rounded-circle">
                                                    {{ strtoupper(substr($employee->user->name, 0, 2)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $employee->user->name }}</h6>
                                                <small class="text-muted">{{ $employee->position->name ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $employee->department->name ?? '-' }}</td>
                                    <td><strong>Rp {{ number_format($basicSalary, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($totalAllowance > 0)
                                            <span class="text-success">+Rp {{ number_format($totalAllowance, 0, ',', '.') }}</span>
                                            <br><small class="text-muted">{{ $allowances->count() }} item</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($totalDeduction > 0)
                                            <span class="text-danger">-Rp {{ number_format($totalDeduction, 0, ',', '.') }}</span>
                                            <br><small class="text-muted">{{ $deductions->count() }} item</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><strong class="text-primary">Rp {{ number_format($netSalary, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($employee->user->salaryComponents->count() > 0)
                                            <span class="badge bg-success">Siap</span>
                                        @else
                                            <span class="badge bg-warning">Perlu Setup</span>
                                        @endif
                                    </td>
                                </tr>

                                @if($employee->user->salaryComponents->count() > 0)
                                <tr class="collapse" id="components-{{ $employee->id }}">
                                    <td colspan="8">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="mb-3"><i class="fas fa-list me-2"></i>Detail Komponen Gaji</h6>
                                            <div class="row">
                                                @if($allowances->count() > 0)
                                                <div class="col-md-6">
                                                    <h6 class="text-success"><i class="fas fa-plus-circle me-1"></i>Tunjangan</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            @foreach($allowances as $component)
                                                            @php
                                                                $amount = $component->pivot->amount ?? $component->calculateAmount($basicSalary);
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $component->name }}</td>
                                                                <td class="text-end">
                                                                    <span class="text-success">+Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            <tr class="table-success">
                                                                <td><strong>Total Tunjangan</strong></td>
                                                                <td class="text-end"><strong>+Rp {{ number_format($totalAllowance, 0, ',', '.') }}</strong></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                                @endif

                                                @if($deductions->count() > 0)
                                                <div class="col-md-6">
                                                    <h6 class="text-danger"><i class="fas fa-minus-circle me-1"></i>Potongan</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            @foreach($deductions as $component)
                                                            @php
                                                                $amount = $component->pivot->amount ?? $component->calculateAmount($basicSalary);
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $component->name }}</td>
                                                                <td class="text-end">
                                                                    <span class="text-danger">-Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            <tr class="table-danger">
                                                                <td><strong>Total Potongan</strong></td>
                                                                <td class="text-end"><strong>-Rp {{ number_format($totalDeduction, 0, ',', '.') }}</strong></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>

                                            <div class="mt-3 p-2 bg-primary text-white rounded">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <small>Gaji Pokok:</small><br>
                                                        <strong>Rp {{ number_format($basicSalary, 0, ',', '.') }}</strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small>Gaji Kotor:</small><br>
                                                        <strong>Rp {{ number_format($grossSalary, 0, ',', '.') }}</strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small>Gaji Bersih:</small><br>
                                                        <strong>Rp {{ number_format($netSalary, 0, ',', '.') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5>Tidak ada karyawan aktif</h5>
                        <p class="text-muted">Tidak ada karyawan aktif yang dapat diproses untuk periode ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function processPayroll() {
    if (confirm('Apakah Anda yakin ingin memproses payroll untuk periode ini? Proses ini akan menghitung gaji semua karyawan aktif.')) {
        // Show loading
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        btn.disabled = true;

        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("payroll.periods.process", $period->id) }}'
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

// Handle collapse toggle icons
$(document).ready(function() {
    $('.collapse').on('show.bs.collapse', function () {
        $(this).prev().find('.fas').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });

    $('.collapse').on('hide.bs.collapse', function () {
        $(this).prev().find('.fas').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });
});
</script>
@endpush
