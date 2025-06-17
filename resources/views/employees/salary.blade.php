@extends('layouts.app')

@section('title', 'Manajemen Gaji - ' . $employee->user->full_name)
@section('page-title', 'Manajemen Gaji Karyawan')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Employee Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </div>
                </div>
                <h5 class="text-center mb-3">{{ $employee->user->full_name }}</h5>
                
                <table class="table table-sm">
                    <tr>
                        <td><strong>Employee ID:</strong></td>
                        <td>{{ $employee->user->employee_id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Departemen:</strong></td>
                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Posisi:</strong></td>
                        <td>{{ $employee->position->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $employee->employment_status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($employee->employment_status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Bergabung:</strong></td>
                        <td>{{ $employee->hire_date->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Basic Salary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Gaji Pokok</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.salary.update', $employee) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="basic_salary" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" 
                                       id="basic_salary" name="basic_salary" 
                                       value="{{ old('basic_salary', $employee->basic_salary) }}" 
                                       step="1000" min="0" required>
                            </div>
                            @error('basic_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Gaji Pokok
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Current Salary Components -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-coins me-2"></i>Komponen Gaji Aktif</h5>
                <div>
                    @if($availableComponents->where('type', 'allowance')->count() > 0)
                    <button type="button" class="btn btn-sm btn-success me-2" onclick="assignDefaultAllowances()">
                        <i class="fas fa-magic me-1"></i>Tambah Tunjangan Default
                    </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                        <i class="fas fa-plus me-1"></i>Tambah Komponen
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($employee->user->salaryComponents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Komponen</th>
                                    <th>Tipe</th>
                                    <th>Nominal</th>
                                    <th>Berlaku Sejak</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->user->salaryComponents as $component)
                                <tr>
                                    <td>
                                        <strong>{{ $component->name }}</strong>
                                        <br><small class="text-muted">{{ $component->code }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $component->type == 'allowance' ? 'success' : 'danger' }}">
                                            {{ $component->type == 'allowance' ? 'Tunjangan' : 'Potongan' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>Rp {{ number_format($component->pivot->amount ?? $component->default_amount, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($component->pivot->effective_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $component->pivot->is_active ? 'success' : 'secondary' }}">
                                            {{ $component->pivot->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="editComponent({{ $component->id }}, '{{ $component->name }}', {{ $component->pivot->amount ?? $component->default_amount }}, '{{ $component->pivot->effective_date }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="removeComponent({{ $component->id }})">
                                                <i class="fas fa-trash"></i>
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
                        <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                        <h6>Belum ada komponen gaji</h6>
                        <p class="text-muted">Tambahkan komponen gaji untuk karyawan ini.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Salary Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Gaji</h5>
            </div>
            <div class="card-body">
                @php
                    $totalAllowances = $employee->user->salaryComponents->where('type', 'allowance')->sum(function($component) {
                        return $component->pivot->amount ?? $component->default_amount;
                    });
                    $totalDeductions = $employee->user->salaryComponents->where('type', 'deduction')->sum(function($component) {
                        return $component->pivot->amount ?? $component->default_amount;
                    });
                    $grossSalary = $employee->basic_salary + $totalAllowances;
                    $netSalary = $grossSalary - $totalDeductions;
                @endphp
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-muted">Gaji Pokok</h6>
                            <h4>Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-success">Total Tunjangan</h6>
                            <h4 class="text-success">Rp {{ number_format($totalAllowances, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-danger">Total Potongan</h6>
                            <h4 class="text-danger">Rp {{ number_format($totalDeductions, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6 class="text-primary">Gaji Bersih</h6>
                            <h4 class="text-primary">Rp {{ number_format($netSalary, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Komponen Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employees.salary.update', $employee) }}" method="POST">
                @csrf
                <input type="hidden" name="basic_salary" value="{{ $employee->basic_salary }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="component_id" class="form-label">Komponen Gaji</label>
                        <select class="form-select" id="component_id" name="components[0][component_id]" required>
                            <option value="">Pilih Komponen</option>
                            @foreach($availableComponents as $component)
                                <option value="{{ $component->id }}" data-amount="{{ $component->default_amount }}">
                                    {{ $component->name }} ({{ $component->type == 'allowance' ? 'Tunjangan' : 'Potongan' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Nominal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="amount" name="components[0][amount]" 
                                   step="1000" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="effective_date" class="form-label">Berlaku Sejak</label>
                        <input type="date" class="form-control" id="effective_date" name="components[0][effective_date]" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#component_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const defaultAmount = selectedOption.data('amount');
        if (defaultAmount) {
            $('#amount').val(defaultAmount);
        }
    });
});

function editComponent(componentId, name, amount, effectiveDate) {
    const newAmount = prompt(`Edit nominal untuk ${name}:`, amount);
    if (newAmount !== null && newAmount !== '' && !isNaN(newAmount)) {
        // Create a form to update the component
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("employees.salary.update", $employee) }}'
        });

        // Add CSRF token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // Add basic salary (required field)
        form.append($('<input>', {
            type: 'hidden',
            name: 'basic_salary',
            value: '{{ $employee->basic_salary }}'
        }));

        // Add component data
        form.append($('<input>', {
            type: 'hidden',
            name: 'components[0][component_id]',
            value: componentId
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'components[0][amount]',
            value: newAmount
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'components[0][effective_date]',
            value: effectiveDate
        }));

        // Submit form
        $('body').append(form);
        form.submit();
    }
}

function removeComponent(componentId) {
    if (confirm('Apakah Anda yakin ingin menghapus komponen gaji ini?')) {
        // Create a form to remove the component
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("employees.salary.remove", $employee) }}'
        });

        // Add CSRF token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // Add component ID
        form.append($('<input>', {
            type: 'hidden',
            name: 'component_id',
            value: componentId
        }));

        // Submit form
        $('body').append(form);
        form.submit();
    }
}

function assignDefaultAllowances() {
    if (confirm('Apakah Anda yakin ingin menambahkan semua tunjangan default untuk karyawan ini?')) {
        // Create a form to assign default allowances
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("employees.salary.assign-defaults", $employee) }}'
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
