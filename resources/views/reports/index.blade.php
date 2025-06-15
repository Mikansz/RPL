@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan & Analisis')

@section('content')
<!-- Report Categories -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary h-100">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-clock fa-2x text-primary"></i>
                </div>
                <h5 class="card-title">Laporan Absensi</h5>
                <p class="card-text text-muted">Laporan kehadiran, keterlambatan, dan absensi karyawan</p>
                <a href="{{ route('reports.attendance') }}" class="btn btn-primary">
                    <i class="fas fa-eye me-2"></i>Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    

    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info h-100">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-users fa-2x text-info"></i>
                </div>
                <h5 class="card-title">Laporan HR</h5>
                <p class="card-text text-muted">Laporan karyawan, departemen, dan posisi</p>
                <a href="{{ route('reports.hr') }}" class="btn btn-info">
                    <i class="fas fa-eye me-2"></i>Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning h-100">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-calendar-times fa-2x text-warning"></i>
                </div>
                <h5 class="card-title">Laporan Cuti</h5>
                <p class="card-text text-muted">Laporan penggunaan cuti dan izin karyawan</p>
                <a href="{{ route('reports.leaves') }}" class="btn btn-warning">
                    <i class="fas fa-eye me-2"></i>Lihat Laporan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Reports -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Laporan Cepat</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-outline-primary w-100" onclick="generateQuickReport('daily-attendance')">
                    <i class="fas fa-calendar-day me-2"></i>Absensi Harian
                </button>
            </div>

            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-outline-info w-100" onclick="generateQuickReport('employee-summary')">
                    <i class="fas fa-user-friends me-2"></i>Ringkasan Karyawan
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-outline-warning w-100" onclick="generateQuickReport('leave-balance')">
                    <i class="fas fa-balance-scale me-2"></i>Saldo Cuti
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Laporan Terbaru</h5>
            </div>
            <div class="card-body">
                @if(isset($recent_reports) && count($recent_reports) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Laporan</th>
                                    <th>Jenis</th>
                                    <th>Periode</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_reports as $report)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">{{ $report->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $report->description ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($report->type ?? 'N/A') }}</span>
                                    </td>
                                    <td>{{ $report->period ?? 'N/A' }}</td>
                                    <td>
                                        <small>{{ $report->created_at ? $report->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                        <br><small class="text-muted">{{ $report->created_at ? $report->created_at->diffForHumans() : '' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="viewReport({{ $report->id }})" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="downloadReport({{ $report->id }})" title="Download">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5>Belum ada laporan</h5>
                        <p class="text-muted">Laporan yang dibuat akan muncul di sini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Report Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik Laporan</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-primary">{{ $report_stats['total'] ?? 0 }}</h4>
                        <small class="text-muted">Total Laporan</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success">{{ $report_stats['this_month'] ?? 0 }}</h4>
                        <small class="text-muted">Bulan Ini</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Jenis Laporan Populer:</small>
                    <div class="mt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Absensi</span>
                            <span>{{ $report_stats['attendance'] ?? 0 }}</span>
                        </div>
                        <div class="progress mb-2" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 70%"></div>
                        </div>
                        

                        
                        <div class="d-flex justify-content-between mb-1">
                            <span>HR</span>
                            <span>{{ $report_stats['hr'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 30%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Custom Report Builder -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Buat Laporan Custom</h5>
            </div>
            <div class="card-body">
                <form id="customReportForm">
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Jenis Laporan</label>
                        <select class="form-select" id="report_type" name="report_type" required>
                            <option value="">Pilih Jenis</option>
                            <option value="attendance">Absensi</option>

                            <option value="hr">HR</option>
                            <option value="leaves">Cuti</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="period_type" class="form-label">Periode</label>
                        <select class="form-select" id="period_type" name="period_type" required>
                            <option value="">Pilih Periode</option>
                            <option value="daily">Harian</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                            <option value="yearly">Tahunan</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="customDateRange" style="display: none;">
                        <label class="form-label">Rentang Tanggal</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-chart-bar me-2"></i>Generate Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generateQuickReport(type) {
    alert(`Generating ${type} report...`);
    // Implementation would go here
}

function viewReport(reportId) {
    alert(`Viewing report ${reportId}...`);
    // Implementation would go here
}

function downloadReport(reportId) {
    alert(`Downloading report ${reportId}...`);
    // Implementation would go here
}

$(document).ready(function() {
    // Show/hide custom date range
    $('#period_type').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });
    
    // Custom report form submission
    $('#customReportForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            report_type: $('#report_type').val(),
            period_type: $('#period_type').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val()
        };
        
        alert('Generating custom report...');
        console.log('Report data:', formData);
        
        // Implementation would send AJAX request to generate report
    });
});
</script>
@endpush
