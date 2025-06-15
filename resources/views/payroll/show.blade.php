@extends('layouts.app')

@section('title', 'Detail Payroll')
@section('page-title', 'Detail Payroll - ' . $payroll->user->name)

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Detail Slip Gaji</h4>
                <p class="text-muted mb-0">{{ $payroll->payrollPeriod->name }}</p>
            </div>
            <div>
                @can('payroll.approve')
                    @if(in_array($payroll->status, ['draft', 'pending']))
                        <button type="button" class="btn btn-success me-2" onclick="approvePayroll({{ $payroll->id }})">
                            <i class="fas fa-check me-2"></i>Approve
                        </button>
                    @endif
                @endcan
                <button type="button" class="btn btn-primary me-2" onclick="downloadSlip({{ $payroll->id }})">
                    <i class="fas fa-download me-2"></i>Download PDF
                </button>
                <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Employee Info -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto">
                                <div class="avatar-title bg-primary rounded-circle">
                                    {{ strtoupper(substr($payroll->user->name, 0, 2)) }}
                                </div>
                            </div>
                            <h5 class="mt-2 mb-0">{{ $payroll->user->name }}</h5>
                            <p class="text-muted">{{ $payroll->user->email }}</p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Departemen:</strong></td>
                                    <td>{{ $payroll->user->employee->department->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Posisi:</strong></td>
                                    <td>{{ $payroll->user->employee->position->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
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
                                @if($payroll->approved_by)
                                <tr>
                                    <td><strong>Disetujui oleh:</strong></td>
                                    <td>{{ $payroll->approvedBy->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Approve:</strong></td>
                                    <td>{{ $payroll->approved_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Rincian Gaji</h5>
                    </div>
                    <div class="card-body">
                        <!-- Period Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <strong>Periode:</strong> {{ $payroll->payrollPeriod->name }}<br>
                                <strong>Tanggal:</strong> {{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Hari Kerja:</strong> {{ $payroll->total_working_days }} hari<br>
                                <strong>Hari Hadir:</strong> {{ $payroll->total_present_days }} hari
                            </div>
                        </div>

                        <!-- Salary Breakdown -->
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Komponen</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Gaji Pokok</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    
                                    @if($payroll->details->where('salaryComponent.type', 'allowance')->count() > 0)
                                        <tr class="table-light">
                                            <td colspan="2"><strong>Tunjangan</strong></td>
                                        </tr>
                                        @foreach($payroll->details->where('salaryComponent.type', 'allowance') as $detail)
                                        <tr>
                                            <td class="ps-4">{{ $detail->salaryComponent->name }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    
                                    @if($payroll->overtime_amount > 0)
                                        <tr>
                                            <td><strong>Lembur ({{ $payroll->total_overtime_hours }} jam)</strong></td>
                                            <td class="text-end"><strong>Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="table-success">
                                        <td><strong>Total Kotor</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    
                                    @if($payroll->details->where('salaryComponent.type', 'deduction')->count() > 0)
                                        <tr class="table-light">
                                            <td colspan="2"><strong>Potongan</strong></td>
                                        </tr>
                                        @foreach($payroll->details->where('salaryComponent.type', 'deduction') as $detail)
                                        <tr>
                                            <td class="ps-4">{{ $detail->salaryComponent->name }}</td>
                                            <td class="text-end text-danger">-Rp {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    
                                    @if($payroll->tax_amount > 0)
                                        <tr>
                                            <td><strong>Pajak PPh 21</strong></td>
                                            <td class="text-end text-danger"><strong>-Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="table-primary">
                                        <td><strong>GAJI BERSIH</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($payroll->notes)
                        <div class="mt-3">
                            <strong>Catatan:</strong>
                            <p class="text-muted">{{ $payroll->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approvePayroll(payrollId) {
    if (confirm('Apakah Anda yakin ingin menyetujui payroll ini?')) {
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
</script>
@endpush
