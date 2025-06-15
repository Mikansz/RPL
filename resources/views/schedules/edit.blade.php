@extends('layouts.app')

@section('title', 'Edit Jadwal Kerja')
@section('page-title', 'Edit Jadwal Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Jadwal Kerja
                </h5>
            </div>
            
            <form method="POST" action="{{ route('schedules.update', $schedule) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Karyawan <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih Karyawan</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('user_id', $schedule->user_id) == $user->id) ? 'selected' : '' }}>
                                    {{ $user->full_name }} ({{ $user->employee_id }})
                                </option>
                                @endforeach
                            </select>
                            @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="schedule_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="schedule_date" id="schedule_date" 
                                   class="form-control @error('schedule_date') is-invalid @enderror" 
                                   value="{{ old('schedule_date', $schedule->schedule_date->format('Y-m-d')) }}" 
                                   required>
                            @error('schedule_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="shift_id" class="form-label">Shift <span class="text-danger">*</span></label>
                            <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                <option value="">Pilih Shift</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ (old('shift_id', $schedule->shift_id) == $shift->id) ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ $shift->formatted_start_time }} - {{ $shift->formatted_end_time }})
                                </option>
                                @endforeach
                            </select>
                            @error('shift_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="work_type" class="form-label">Tipe Kerja <span class="text-danger">*</span></label>
                            <select name="work_type" id="work_type" class="form-select @error('work_type') is-invalid @enderror" required>
                                <option value="">Pilih Tipe Kerja</option>
                                <option value="WFO" {{ (old('work_type', $schedule->work_type) == 'WFO') ? 'selected' : '' }}>WFO (Work From Office)</option>
                                <option value="WFA" {{ (old('work_type', $schedule->work_type) == 'WFA') ? 'selected' : '' }}>WFA (Work From Anywhere)</option>
                            </select>
                            @error('work_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3" id="office_field" style="display: none;">
                            <label for="office_id" class="form-label">Kantor <span class="text-danger">*</span></label>
                            <select name="office_id" id="office_id" class="form-select @error('office_id') is-invalid @enderror">
                                <option value="">Pilih Kantor</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ (old('office_id', $schedule->office_id) == $office->id) ? 'selected' : '' }}>
                                    {{ $office->name }} (Radius: {{ $office->radius }}m)
                                </option>
                                @endforeach
                            </select>
                            @error('office_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Kantor wajib dipilih untuk tipe kerja WFO</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="scheduled" {{ (old('status', $schedule->status) == 'scheduled') ? 'selected' : '' }}>Terjadwal</option>
                                <option value="approved" {{ (old('status', $schedule->status) == 'approved') ? 'selected' : '' }}>Disetujui</option>
                                <option value="cancelled" {{ (old('status', $schedule->status) == 'cancelled') ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small>
                                    <strong>Terjadwal:</strong> Jadwal belum disetujui<br>
                                    <strong>Disetujui:</strong> Jadwal aktif dan dapat digunakan<br>
                                    <strong>Dibatalkan:</strong> Jadwal tidak berlaku
                                </small>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Catatan tambahan untuk jadwal ini...">{{ old('notes', $schedule->notes) }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            Update Jadwal
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
    // Toggle office field based on work type
    $('#work_type').change(function() {
        const workType = $(this).val();
        const officeField = $('#office_field');
        const officeSelect = $('#office_id');
        
        if (workType === 'WFO') {
            officeField.show();
            officeSelect.prop('required', true);
        } else {
            officeField.hide();
            officeSelect.prop('required', false);
            officeSelect.val('');
        }
    });
    
    // Initialize on page load
    $('#work_type').trigger('change');
});
</script>
@endpush
