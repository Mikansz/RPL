@extends('layouts.app')

@section('title', 'Tambah Jadwal Kerja')
@section('page-title', 'Tambah Jadwal Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Tambah Jadwal Kerja Baru
                </h5>
            </div>
            
            <form method="POST" action="{{ route('schedules.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Karyawan <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih Karyawan</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                                   value="{{ old('schedule_date') }}" 
                                   min="{{ date('Y-m-d') }}" required>
                            @error('schedule_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="shift_id" class="form-label">Shift <span class="text-danger">*</span></label>
                            <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                <option value="">Pilih Shift</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
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
                                <option value="WFO" {{ old('work_type') == 'WFO' ? 'selected' : '' }}>WFO (Work From Office)</option>
                                <option value="WFA" {{ old('work_type') == 'WFA' ? 'selected' : '' }}>WFA (Work From Anywhere)</option>
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
                                <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                    {{ $office->name }} (Radius: {{ $office->radius }}m)
                                </option>
                                @endforeach
                            </select>
                            @error('office_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Kantor wajib dipilih untuk tipe kerja WFO</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Catatan tambahan untuk jadwal ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div id="schedule_preview" class="card bg-light" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-eye me-2"></i>
                                Preview Jadwal
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="bg-primary bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-user fa-2x text-primary"></i>
                                    </div>
                                    <h6>Karyawan</h6>
                                    <p id="preview_user" class="mb-0">-</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-info bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-calendar fa-2x text-info"></i>
                                    </div>
                                    <h6>Tanggal</h6>
                                    <p id="preview_date" class="mb-0">-</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-warning bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                    <h6>Shift</h6>
                                    <p id="preview_shift" class="mb-0">-</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div id="preview_work_type_icon" class="bg-success bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-home fa-2x text-success"></i>
                                    </div>
                                    <h6>Tipe Kerja</h6>
                                    <p id="preview_work_type" class="mb-0">-</p>
                                </div>
                            </div>
                            <div id="preview_office_row" class="row mt-3" style="display: none;">
                                <div class="col-12 text-center">
                                    <div class="bg-secondary bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-building fa-2x text-secondary"></i>
                                    </div>
                                    <h6>Kantor</h6>
                                    <p id="preview_office" class="mb-0">-</p>
                                </div>
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
                            Simpan Jadwal
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
        
        updatePreview();
    });
    
    // Update preview when form changes
    $('#user_id, #schedule_date, #shift_id, #work_type, #office_id').change(updatePreview);
    
    function updatePreview() {
        const userId = $('#user_id').val();
        const scheduleDate = $('#schedule_date').val();
        const shiftId = $('#shift_id').val();
        const workType = $('#work_type').val();
        const officeId = $('#office_id').val();
        
        if (userId && scheduleDate && shiftId && workType) {
            $('#schedule_preview').show();
            
            // Update user
            const userName = $('#user_id option:selected').text();
            $('#preview_user').text(userName);
            
            // Update date
            if (scheduleDate) {
                const date = new Date(scheduleDate);
                const formattedDate = date.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                $('#preview_date').text(formattedDate);
            }
            
            // Update shift
            const shiftText = $('#shift_id option:selected').text();
            $('#preview_shift').text(shiftText);
            
            // Update work type
            $('#preview_work_type').text(workType);
            const workTypeIcon = $('#preview_work_type_icon');
            if (workType === 'WFO') {
                workTypeIcon.removeClass('bg-success bg-opacity-10').addClass('bg-primary bg-opacity-10');
                workTypeIcon.find('i').removeClass('fa-home text-success').addClass('fa-building text-primary');
            } else {
                workTypeIcon.removeClass('bg-primary bg-opacity-10').addClass('bg-success bg-opacity-10');
                workTypeIcon.find('i').removeClass('fa-building text-primary').addClass('fa-home text-success');
            }
            
            // Update office
            if (workType === 'WFO' && officeId) {
                const officeText = $('#office_id option:selected').text();
                $('#preview_office').text(officeText);
                $('#preview_office_row').show();
            } else {
                $('#preview_office_row').hide();
            }
        } else {
            $('#schedule_preview').hide();
        }
    }
    
    // Initialize
    $('#work_type').trigger('change');
});
</script>
@endpush
