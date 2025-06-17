@extends('layouts.app')

@section('title', 'Komponen Gaji')
@section('page-title', 'Manajemen Komponen Gaji')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Komponen Gaji</h4>
                <p class="text-muted mb-0">Kelola tunjangan, potongan, dan komponen gaji lainnya</p>
            </div>
            <div>
                @can('salary_components.create')
                <a href="{{ route('salary-components.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Komponen
                </a>
                @endcan
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded p-3">
                                    <i class="fas fa-plus-circle fa-2x text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">{{ $allowances ?? 0 }}</h4>
                                <p class="text-muted mb-0">Tunjangan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded p-3">
                                    <i class="fas fa-minus-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">{{ $deductions ?? 0 }}</h4>
                                <p class="text-muted mb-0">Potongan</p>
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
                                    <i class="fas fa-gift fa-2x text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">{{ $benefits ?? 0 }}</h4>
                                <p class="text-muted mb-0">Benefit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                    <i class="fas fa-list fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">{{ $total ?? 0 }}</h4>
                                <p class="text-muted mb-0">Total Komponen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Components Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Komponen Gaji</h5>
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
                                        @elseif($component->type == 'benefit')
                                            <span class="badge bg-info">Benefit</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($component->calculation_type == 'fixed')
                                            <span class="badge bg-secondary">Tetap</span>
                                        @elseif($component->calculation_type == 'percentage')
                                            <span class="badge bg-warning">Persentase</span>
                                        @elseif($component->calculation_type == 'formula')
                                            <span class="badge bg-primary">Formula</span>
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
                                            <span class="badge bg-secondary">Tidak</span>
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
                                            @can('salary_components.view')
                                            <a href="{{ route('salary-components.show', $component) }}" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            {{-- Show edit button with proper permission check --}}
                                            @if(auth()->user() && (auth()->user()->hasPermission('salary_components.edit') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('HRD') || auth()->user()->hasRole('CFO')))
                                            <a href="{{ route('salary-components.edit', $component) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @else
                                            {{-- Show debug info if no permission --}}
                                            <span class="btn btn-outline-secondary disabled" title="Tidak ada permission edit">
                                                <i class="fas fa-edit"></i>
                                            </span>
                                            @endif

                                            @if(auth()->user() && (auth()->user()->hasPermission('salary_components.edit') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('HRD') || auth()->user()->hasRole('CFO')))
                                            <button type="button" class="btn btn-outline-{{ $component->is_active ? 'warning' : 'success' }}"
                                                    onclick="toggleStatus({{ $component->id }}, {{ $component->is_active ? 'false' : 'true' }})"
                                                    title="{{ $component->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas fa-{{ $component->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            @endif
                                            @can('salary_components.delete')
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteComponent({{ $component->id }})" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endcan
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
                        @can('salary_components.create')
                        <a href="{{ route('salary-components.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Komponen Pertama
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
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
