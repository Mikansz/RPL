@extends('layouts.app')

@section('title', 'Edit Template Jadwal Kerja')
@section('page-title', 'Edit Template Jadwal Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Template: {{ $scheduleTemplate->name }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('schedule-templates.update', $scheduleTemplate) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $scheduleTemplate->name) }}" 
                                   placeholder="Contoh: Jadwal Kantor Standar" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="shift_id" class="form-label">Shift <span class="text-danger">*</span></label>
                            <select name="shift_id" id="shift_id" 
                                    class="form-select @error('shift_id') is-invalid @enderror" required>
                                <option value="">Pilih Shift</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', $scheduleTemplate->shift_id) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})
                                </option>
                                @endforeach
                            </select>
                            @error('shift_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Deskripsi template jadwal kerja...">{{ old('description', $scheduleTemplate->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Work Type -->
                        <div class="col-md-6 mb-3">
                            <label for="work_type" class="form-label">Tipe Kerja <span class="text-danger">*</span></label>
                            <select name="work_type" id="work_type" 
                                    class="form-select @error('work_type') is-invalid @enderror" required>
                                <option value="">Pilih Tipe Kerja</option>
                                <option value="WFO" {{ old('work_type', $scheduleTemplate->work_type) == 'WFO' ? 'selected' : '' }}>WFO (Work From Office)</option>
                                <option value="WFA" {{ old('work_type', $scheduleTemplate->work_type) == 'WFA' ? 'selected' : '' }}>WFA (Work From Anywhere)</option>
                            </select>
                            @error('work_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Office (shown only for WFO) -->
                        <div class="col-md-6 mb-3" id="office_field" style="{{ old('work_type', $scheduleTemplate->work_type) == 'WFO' ? '' : 'display: none;' }}">
                            <label for="office_id" class="form-label">Kantor <span class="text-danger">*</span></label>
                            <select name="office_id" id="office_id" 
                                    class="form-select @error('office_id') is-invalid @enderror">
                                <option value="">Pilih Kantor</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ old('office_id', $scheduleTemplate->office_id) == $office->id ? 'selected' : '' }}>
                                    {{ $office->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('office_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Work Days -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Hari Kerja <span class="text-danger">*</span></label>
                            <div class="row">
                                @php
                                $days = [
                                    1 => 'Senin',
                                    2 => 'Selasa', 
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                    7 => 'Minggu'
                                ];
                                $workDays = old('work_days', $scheduleTemplate->work_days);
                                @endphp
                                @foreach($days as $dayNum => $dayName)
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="work_days[]" value="{{ $dayNum }}" 
                                               id="day_{{ $dayNum }}"
                                               {{ in_array($dayNum, $workDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day_{{ $dayNum }}">
                                            {{ $dayName }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('work_days')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Schedule Period -->
                        <div class="col-12 mb-3">
                            <div class="card border-info">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Periode Berlaku
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @php
                                    $isPermanent = old('is_permanent', $scheduleTemplate->isPermanent());
                                    @endphp
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               name="is_permanent" id="is_permanent" value="1"
                                               {{ $isPermanent ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_permanent">
                                            <strong>Berlaku Selamanya</strong>
                                            <small class="text-muted d-block">Template ini akan berlaku tanpa batas waktu</small>
                                        </label>
                                    </div>
                                    
                                    <div id="date_fields" style="{{ $isPermanent ? 'display: none;' : '' }}">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="effective_from" class="form-label">Berlaku Dari</label>
                                                <input type="date" name="effective_from" id="effective_from" 
                                                       class="form-control @error('effective_from') is-invalid @enderror" 
                                                       value="{{ old('effective_from', $scheduleTemplate->effective_from?->format('Y-m-d')) }}">
                                                @error('effective_from')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="effective_until" class="form-label">Berlaku Sampai</label>
                                                <input type="date" name="effective_until" id="effective_until" 
                                                       class="form-control @error('effective_until') is-invalid @enderror" 
                                                       value="{{ old('effective_until', $scheduleTemplate->effective_until?->format('Y-m-d')) }}">
                                                @error('effective_until')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Kosongkan jika tidak ada batas akhir</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="col-12 mb-3">
                            <div class="card border-secondary">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>
                                        Pengaturan Tambahan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="exclude_sundays" id="exclude_sundays" value="1"
                                                       {{ old('exclude_sundays', $scheduleTemplate->exclude_sundays) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="exclude_sundays">
                                                    Kecualikan Hari Minggu
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="exclude_holidays" id="exclude_holidays" value="1"
                                                       {{ old('exclude_holidays', $scheduleTemplate->exclude_holidays) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="exclude_holidays">
                                                    Kecualikan Hari Libur
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="is_active" id="is_active" value="1"
                                                       {{ old('is_active', $scheduleTemplate->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Template Aktif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('schedule-templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Template
                        </button>
                    </div>
                </form>
            </div>
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
    
    // Toggle date fields based on permanent checkbox
    $('#is_permanent').change(function() {
        const isPermanent = $(this).is(':checked');
        const dateFields = $('#date_fields');
        const effectiveFrom = $('#effective_from');
        const effectiveUntil = $('#effective_until');
        
        if (isPermanent) {
            dateFields.hide();
            effectiveFrom.prop('required', false).val('');
            effectiveUntil.prop('required', false).val('');
        } else {
            dateFields.show();
            effectiveFrom.prop('required', true);
        }
    });
    
    // Initialize on page load
    $('#work_type').trigger('change');
    $('#is_permanent').trigger('change');
});
</script>
@endpush
