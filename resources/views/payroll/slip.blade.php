@extends('layouts.app')

@section('title', 'Slip Gaji Saya')
@section('page-title', 'Slip Gaji Saya')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Slip Gaji Saya</h4>
                <p class="text-muted mb-0">Riwayat slip gaji dan tunjangan</p>
            </div>
        </div>

        @if($payrolls->count() > 0)
            <div class="row">
                @foreach($payrolls as $payroll)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $payroll->payrollPeriod->name }}</h6>
                            @if($payroll->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($payroll->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($payroll->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($payroll->status == 'paid')
                                <span class="badge bg-info">Paid</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h5 class="text-primary mb-1">Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</h5>
                                        <p class="text-muted mb-0 small">Gaji Kotor</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-success mb-1">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</h5>
                                    <p class="text-muted mb-0 small">Gaji Bersih</p>
                                </div>
                            </div>
                            
                            <div class="row small text-muted mb-3">
                                <div class="col-6">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $payroll->total_present_days }}/{{ $payroll->total_working_days }} hari
                                </div>
                            </div>

                            <!-- Quick Summary -->
                            <div class="row small mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <span>Gaji Pokok:</span>
                                        <span>Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</span>
                                    </div>
                                    @if($payroll->total_allowances > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>Tunjangan:</span>
                                        <span class="text-success">+Rp {{ number_format($payroll->total_allowances, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($payroll->overtime_amount > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>Lembur:</span>
                                        <span class="text-success">+Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($payroll->total_deductions > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>Potongan:</span>
                                        <span class="text-danger">-Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($payroll->tax_amount > 0)
                                    <div class="d-flex justify-content-between">
                                        <span>Pajak:</span>
                                        <span class="text-danger">-Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewSlipDetail({{ $payroll->id }})">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </button>
                                @if(in_array($payroll->status, ['approved', 'paid']))
                                <button type="button" class="btn btn-primary btn-sm" onclick="downloadSlip({{ $payroll->id }})">
                                    <i class="fas fa-download me-1"></i>Download
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payrolls->links() }}
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5>Belum Ada Slip Gaji</h5>
                    <p class="text-muted">Slip gaji Anda akan muncul di sini setelah payroll diproses oleh HRD.</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="slipDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Slip Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="slipDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewSlipDetail(payrollId) {
    $('#slipDetailContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    
    const modal = new bootstrap.Modal(document.getElementById('slipDetailModal'));
    modal.show();
    
    // Load detail via AJAX
    $.get(`/payroll/${payrollId}`)
        .done(function(response) {
            // Extract the payroll details from the response
            // For now, redirect to the show page
            window.location.href = `/payroll/${payrollId}`;
        })
        .fail(function() {
            $('#slipDetailContent').html('<div class="alert alert-danger">Gagal memuat detail slip gaji.</div>');
        });
}

function downloadSlip(payrollId) {
    window.open(`/payroll/slip/${payrollId}/download`, '_blank');
}
</script>
@endpush
