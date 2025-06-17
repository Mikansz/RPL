@extends('layouts.app')

@section('title', 'Detail Komponen Gaji')
@section('page-title', 'Detail Komponen Gaji')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Detail Komponen Gaji</h4>
                <p class="text-muted mb-0">{{ $salaryComponent->name }}</p>
            </div>
            <div>
                @can('salary_components.edit')
                <a href="{{ route('salary-components.edit', $salaryComponent) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
                @endcan
                <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Component Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Komponen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nama Komponen</label>
                                <p class="fw-bold">{{ $salaryComponent->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Kode</label>
                                <p><code>{{ $salaryComponent->code }}</code></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tipe</label>
                                <p>
                                    @if($salaryComponent->type == 'allowance')
                                        <span class="badge bg-success">Tunjangan</span>
                                    @elseif($salaryComponent->type == 'deduction')
                                        <span class="badge bg-danger">Potongan</span>
                                    @elseif($salaryComponent->type == 'benefit')
                                        <span class="badge bg-info">Benefit</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tipe Perhitungan</label>
                                <p>
                                    @if($salaryComponent->calculation_type == 'fixed')
                                        <span class="badge bg-secondary">Nominal Tetap</span>
                                    @elseif($salaryComponent->calculation_type == 'percentage')
                                        <span class="badge bg-warning">Persentase</span>
                                    @elseif($salaryComponent->calculation_type == 'formula')
                                        <span class="badge bg-primary">Formula</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nilai Default</label>
                                <p class="fw-bold">
                                    @if($salaryComponent->calculation_type == 'fixed')
                                        Rp {{ number_format($salaryComponent->default_amount, 0, ',', '.') }}
                                    @elseif($salaryComponent->calculation_type == 'percentage')
                                        {{ $salaryComponent->percentage }}% dari gaji pokok
                                    @else
                                        <small class="text-muted">Berdasarkan formula</small>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Urutan</label>
                                <p>{{ $salaryComponent->sort_order }}</p>
                            </div>
                        </div>
                        
                        @if($salaryComponent->description)
                        <div class="mb-3">
                            <label class="form-label text-muted">Deskripsi</label>
                            <p>{{ $salaryComponent->description }}</p>
                        </div>
                        @endif
                        
                        @if($salaryComponent->calculation_type == 'formula' && $salaryComponent->formula)
                        <div class="mb-3">
                            <label class="form-label text-muted">Formula</label>
                            <p><code>{{ $salaryComponent->formula }}</code></p>
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Kena Pajak</label>
                                <p>
                                    @if($salaryComponent->is_taxable)
                                        <span class="badge bg-warning">Ya</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Status</label>
                                <p>
                                    @if($salaryComponent->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Dibuat</label>
                                <p>{{ $salaryComponent->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Terakhir Diupdate</label>
                                <p>{{ $salaryComponent->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Usage Statistics -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Penggunaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h3 class="text-primary">{{ $salaryComponent->employees()->count() }}</h3>
                            <p class="text-muted mb-0">Karyawan Menggunakan</p>
                        </div>
                        
                        <div class="text-center mb-3">
                            <h3 class="text-info">{{ $salaryComponent->payrollDetails()->count() }}</h3>
                            <p class="text-muted mb-0">Record Payroll</p>
                        </div>
                        
                        @if($salaryComponent->employees()->count() > 0)
                        <hr>
                        <h6>Karyawan yang Menggunakan:</h6>
                        <div class="list-group list-group-flush">
                            @foreach($salaryComponent->employees()->limit(5)->get() as $employee)
                            <div class="list-group-item px-0 py-2">
                                <small>{{ $employee->full_name }}</small>
                                @if($employee->pivot->amount > 0)
                                <br><small class="text-muted">Rp {{ number_format($employee->pivot->amount, 0, ',', '.') }}</small>
                                @endif
                            </div>
                            @endforeach
                            @if($salaryComponent->employees()->count() > 5)
                            <div class="list-group-item px-0 py-2">
                                <small class="text-muted">dan {{ $salaryComponent->employees()->count() - 5 }} lainnya...</small>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                
                @can('salary_components.edit')
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-{{ $salaryComponent->is_active ? 'warning' : 'success' }}" 
                                    onclick="toggleStatus({{ $salaryComponent->id }}, {{ $salaryComponent->is_active ? 'false' : 'true' }})">
                                <i class="fas fa-{{ $salaryComponent->is_active ? 'pause' : 'play' }} me-2"></i>
                                {{ $salaryComponent->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            
                            @can('salary_components.delete')
                            <button type="button" class="btn btn-outline-danger" onclick="deleteComponent({{ $salaryComponent->id }})">
                                <i class="fas fa-trash me-2"></i>Hapus
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(componentId, newStatus) {
    const action = newStatus ? 'mengaktifkan' : 'menonaktifkan';
    if (confirm(`Apakah Anda yakin ingin ${action} komponen gaji ini?`)) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: `/salary-components/${componentId}/toggle-status`
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

function deleteComponent(componentId) {
    if (confirm('Apakah Anda yakin ingin menghapus komponen gaji ini?')) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: `/salary-components/${componentId}`
        });
        
        // Add CSRF token and method
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_method',
            value: 'DELETE'
        }));
        
        // Submit form
        $('body').append(form);
        form.submit();
    }
}
</script>
@endpush
