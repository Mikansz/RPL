@extends('layouts.app')

@section('title', 'CEO Reports Dashboard')
@section('page-title', 'CEO Reports Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1">Laporan Eksekutif</h3>
                        <p class="mb-0">Akses semua laporan perusahaan dalam satu dashboard</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-users fa-2x text-primary"></i>
                </div>
                <h4 class="mb-1">{{ number_format($summary['total_employees'] ?? 0) }}</h4>
                <small class="text-muted">Total Karyawan</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-building fa-2x text-info"></i>
                </div>
                <h4 class="mb-1">{{ number_format($summary['total_departments'] ?? 0) }}</h4>
                <small class="text-muted">Departemen</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-chart-line fa-2x text-success"></i>
                </div>
                <h4 class="mb-1">{{ number_format($summary['attendance_rate'] ?? 0, 1) }}%</h4>
                <small class="text-muted">Tingkat Kehadiran</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                </div>
                <h4 class="mb-1">Rp {{ number_format($summary['total_payroll'] ?? 0, 0, ',', '.') }}</h4>
                <small class="text-muted">Total Payroll</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                     style="width: 60px; height: 60px;">
                    <i class="fas fa-calendar-times fa-2x text-danger"></i>
                </div>
                <h4 class="mb-1">{{ number_format($summary['pending_leaves'] ?? 0) }}</h4>
                <small class="text-muted">Cuti Pending</small>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.ceo') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="downloadAllReports()">
                            <i class="fas fa-download me-2"></i>Download Semua
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reports Grid -->
<div class="row">
    @foreach($reports as $key => $report)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-{{ $report['color'] }} h-100">
            <div class="card-body text-center">
                <div class="bg-{{ $report['color'] }} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="{{ $report['icon'] }} fa-2x text-{{ $report['color'] }}"></i>
                </div>
                <h5 class="card-title">{{ $report['title'] }}</h5>
                <p class="card-text text-muted">{{ $report['description'] }}</p>
                
                <div class="d-grid gap-2">
                    <a href="{{ route($report['route']) }}?start_date={{ $startDate }}&end_date={{ $endDate }}" 
                       class="btn btn-{{ $report['color'] }}">
                        <i class="fas fa-eye me-2"></i>Lihat Laporan
                    </a>
                    <a href="{{ route('reports.export', $report['export']) }}?start_date={{ $startDate }}&end_date={{ $endDate }}" 
                       class="btn btn-outline-{{ $report['color'] }}">
                        <i class="fas fa-download me-2"></i>Download CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="generateQuickReport('daily-summary')">
                            <i class="fas fa-calendar-day me-2"></i>Ringkasan Harian
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-info w-100" onclick="generateQuickReport('monthly-summary')">
                            <i class="fas fa-calendar-alt me-2"></i>Ringkasan Bulanan
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-success w-100" onclick="generateQuickReport('financial-summary')">
                            <i class="fas fa-chart-pie me-2"></i>Ringkasan Keuangan
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-warning w-100" onclick="generateQuickReport('performance-summary')">
                            <i class="fas fa-trophy me-2"></i>Ringkasan Kinerja
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function downloadAllReports() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const reports = ['attendance', 'payroll', 'employees', 'leaves', 'overtime'];
    
    if (confirm('Download semua laporan? Ini akan mengunduh ' + reports.length + ' file CSV.')) {
        reports.forEach((report, index) => {
            setTimeout(() => {
                const url = `{{ route('reports.export', '') }}/${report}?start_date=${startDate}&end_date=${endDate}`;
                window.open(url, '_blank');
            }, index * 1000); // Delay 1 second between downloads
        });
    }
}

function generateQuickReport(type) {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Show loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    button.disabled = true;
    
    // Simulate report generation
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        switch(type) {
            case 'daily-summary':
                window.open(`{{ route('reports.export', 'attendance') }}?start_date=${startDate}&end_date=${endDate}`, '_blank');
                break;
            case 'monthly-summary':
                window.open(`{{ route('reports.export', 'employees') }}?start_date=${startDate}&end_date=${endDate}`, '_blank');
                break;
            case 'financial-summary':
                window.open(`{{ route('reports.export', 'payroll') }}?start_date=${startDate}&end_date=${endDate}`, '_blank');
                break;
            case 'performance-summary':
                downloadAllReports();
                break;
        }
    }, 2000);
}

// Auto-refresh summary every 5 minutes
setInterval(() => {
    location.reload();
}, 300000);
</script>
@endpush
