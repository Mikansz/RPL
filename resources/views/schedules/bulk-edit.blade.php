@extends('layouts.app')

@section('title', 'Bulk Edit Jadwal')
@section('page-title', 'Bulk Edit Jadwal Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Bulk Edit Jadwal Kerja ({{ count($schedules) }} jadwal dipilih)
                </h5>
            </div>
            
            <form method="POST" action="{{ route('schedules.bulk-update') }}">
                @csrf
                @method('PUT')
                
                <!-- Hidden schedule IDs -->
                @foreach($schedules as $schedule)
                <input type="hidden" name="schedule_ids[]" value="{{ $schedule->id }}">
                @endforeach
                
                <div class="card-body">
                    <!-- Selected Schedules Preview -->
                    <div class="mb-4">
                        <h6>Jadwal yang Dipilih:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Karyawan</th>
                                        <th>Shift</th>
                                        <th>Tipe Kerja</th>
                                        <th>Kantor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->schedule_date->format('d/m/Y') }}</td>
                                        <td>{{ $schedule->user->full_name }}</td>
                                        <td>{{ $schedule->shift->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->work_type == 'WFO' ? 'primary' : 'success' }}">
                                                {{ $schedule->work_type }}
                                            </span>
                                        </td>
                                        <td>{{ $schedule->office ? $schedule->office->name : '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->status == 'approved' ? 'success' : ($schedule->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($schedule->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Bulk Edit Options -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bulk_action" class="form-label">Aksi Bulk Edit <span class="text-danger">*</span></label>
                            <select name="bulk_action" id="bulk_action" class="form-select @error('bulk_action') is-invalid @enderror" required>
                                <option value="">Pilih Aksi</option>
                                <option value="update_shift">Update Shift</option>
                                <option value="update_work_type">Update Tipe Kerja</option>
                                <option value="update_status">Update Status</option>
                                <option value="update_office">Update Kantor</option>
                            </select>
                            @error('bulk_action')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Conditional Fields -->
                    <div id="shift_field" class="bulk-field" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shift_id" class="form-label">Shift Baru</label>
                                <select name="shift_id" id="shift_id" class="form-select">
                                    <option value="">Pilih Shift</option>
                                    @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->name }} ({{ $shift->formatted_start_time }} - {{ $shift->formatted_end_time }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="work_type_field" class="bulk-field" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="work_type" class="form-label">Tipe Kerja Baru</label>
                                <select name="work_type" id="work_type" class="form-select">
                                    <option value="">Pilih Tipe Kerja</option>
                                    <option value="WFO">WFO (Work From Office)</option>
                                    <option value="WFA">WFA (Work From Anywhere)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="office_field_bulk" style="display: none;">
                                <label for="office_id" class="form-label">Kantor</label>
                                <select name="office_id" id="office_id" class="form-select">
                                    <option value="">Pilih Kantor</option>
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="status_field" class="bulk-field" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status Baru</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Pilih Status</option>
                                    <option value="scheduled">Terjadwal</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="office_field_only" class="bulk-field" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="office_id_only" class="form-label">Kantor Baru</label>
                                <select name="office_id" id="office_id_only" class="form-select">
                                    <option value="">Pilih Kantor</option>
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Akan mengubah tipe kerja menjadi WFO secara otomatis</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Update Semua Jadwal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide fields based on bulk action
    $('#bulk_action').change(function() {
        const action = $(this).val();
        
        // Hide all bulk fields
        $('.bulk-field').hide();
        
        // Show relevant field
        switch(action) {
            case 'update_shift':
                $('#shift_field').show();
                break;
            case 'update_work_type':
                $('#work_type_field').show();
                break;
            case 'update_status':
                $('#status_field').show();
                break;
            case 'update_office':
                $('#office_field_only').show();
                break;
        }
    });
    
    // Show office field when WFO is selected in work type
    $('#work_type').change(function() {
        const workType = $(this).val();
        const officeField = $('#office_field_bulk');
        
        if (workType === 'WFO') {
            officeField.show();
        } else {
            officeField.hide();
        }
    });
});
</script>
@endpush
