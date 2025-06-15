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

        <!-- Employees Summary -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Karyawan Aktif ({{ $employees->count() }})</h5>
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
                                    <th>Nama Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Posisi</th>
                                    <th>Gaji Pokok</th>
                                    <th>Komponen Gaji</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <div class="avatar-title bg-primary rounded-circle">
                                                    {{ strtoupper(substr($employee->user->name, 0, 2)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $employee->user->name }}</h6>
                                                <small class="text-muted">{{ $employee->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $employee->department->name ?? '-' }}</td>
                                    <td>{{ $employee->position->name ?? '-' }}</td>
                                    <td>Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</td>
                                    <td>
                                        @if($employee->user->salaryComponents->count() > 0)
                                            <span class="badge bg-info">{{ $employee->user->salaryComponents->count() }} komponen</span>
                                        @else
                                            <span class="badge bg-warning">Belum ada komponen</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($employee->employment_status) }}</span>
                                    </td>
                                </tr>
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
</script>
@endpush
