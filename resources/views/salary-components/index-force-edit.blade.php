@extends('layouts.app')

@section('title', 'Manajemen Komponen Gaji (Force Edit)')
@section('page-title', 'Manajemen Komponen Gaji (Force Edit)')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-plus-circle fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $allowances }}</div>
                                <div>Tunjangan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-minus-circle fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $deductions }}</div>
                                <div>Potongan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-gift fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $benefits }}</div>
                                <div>Benefit</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-list fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold fs-4">{{ $total }}</div>
                                <div>Total Komponen</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Components Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Komponen Gaji (FORCE EDIT - NO PERMISSION CHECK)</h5>
                <div>
                    <a href="{{ route('salary-components.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Komponen
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(isset($salaryComponents) && $salaryComponents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Komponen</th>
                                    <th>Tipe</th>
                                    <th>Perhitungan</th>
                                    <th>Nilai Default</th>
                                    <th>Kena Pajak</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salaryComponents as $component)
                                <tr>
                                    <td><code>{{ $component->code }}</code></td>
                                    <td>{{ $component->name }}</td>
                                    <td>
                                        @if($component->type == 'allowance')
                                            <span class="badge bg-success">Tunjangan</span>
                                        @elseif($component->type == 'deduction')
                                            <span class="badge bg-danger">Potongan</span>
                                        @else
                                            <span class="badge bg-info">Benefit</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->calculation_type == 'fixed')
                                            <span class="badge bg-primary">Tetap</span>
                                        @elseif($component->calculation_type == 'percentage')
                                            <span class="badge bg-warning">Persentase</span>
                                        @else
                                            <span class="badge bg-secondary">Formula</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->calculation_type == 'fixed')
                                            Rp {{ number_format($component->default_amount, 0, ',', '.') }}
                                        @elseif($component->calculation_type == 'percentage')
                                            {{ $component->percentage }}%
                                        @else
                                            <small class="text-muted">Formula</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->is_taxable)
                                            <span class="badge bg-warning">Ya</span>
                                        @else
                                            <span class="badge bg-success">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            {{-- FORCE SHOW ALL BUTTONS --}}
                                            <a href="{{ route('salary-components.show', $component) }}" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('salary-components.edit', $component) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button type="button" class="btn btn-outline-{{ $component->is_active ? 'warning' : 'success' }}"
                                                    onclick="toggleStatus({{ $component->id }}, {{ $component->is_active ? 'false' : 'true' }})"
                                                    title="{{ $component->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas fa-{{ $component->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteComponent({{ $component->id }})" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($salaryComponents, 'links'))
                        <div class="d-flex justify-content-center">
                            {{ $salaryComponents->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <h5>Belum ada komponen gaji</h5>
                        <p class="text-muted">Tambahkan komponen gaji seperti tunjangan dan potongan untuk memulai.</p>
                        <a href="{{ route('salary-components.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Komponen Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning mt-3">
    <h6><i class="fas fa-exclamation-triangle me-2"></i>Debug Mode</h6>
    <p class="mb-0">Halaman ini menampilkan semua tombol tanpa permission check untuk testing. 
    User: <strong>{{ auth()->user()->full_name ?? 'Not logged in' }}</strong></p>
</div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush
