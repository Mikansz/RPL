@extends('layouts.app')

@section('title', 'Buat Jadwal Periode')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-plus me-2"></i>Buat Jadwal Periode (Maksimal 30 Hari)
                        </h5>
                        <div>
                            <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#helpModal">
                                <i class="fas fa-question-circle me-1"></i>Bantuan
                            </button>
                            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <form action="{{ route('schedules.bulk-store') }}" method="POST" id="bulkCreateForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Karyawan Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="user_ids" class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllUsers()">Pilih Semua</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllUsers()">Hapus Semua</button>
                                </div>
                                <select name="user_ids[]" id="user_ids" class="form-select @error('user_ids') is-invalid @enderror" multiple required size="8">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                                            {{ $user->full_name }} ({{ $user->employee_id }})
                                            @if($user->employee && $user->employee->department)
                                                - {{ $user->employee->department->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Gunakan Ctrl+Click untuk memilih multiple karyawan</small>
                            </div>

                            <!-- Shift Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="shift_id" class="form-label">Shift <span class="text-danger">*</span></label>
                                <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                    <option value="">Pilih Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}
                                                data-start="{{ $shift->start_time }}" data-end="{{ $shift->end_time }}">
                                            {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('shift_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Date -->
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', date('Y-m-d')) }}" 
                                       min="{{ date('Y-m-d') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maksimal 30 hari dari tanggal mulai</small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Work Type -->
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

                            <!-- Office Selection (for WFO) -->
                            <div class="col-md-6 mb-3" id="office_field" style="display: none;">
                                <label for="office_id" class="form-label">Kantor <span class="text-danger">*</span></label>
                                <select name="office_id" id="office_id" class="form-select @error('office_id') is-invalid @enderror">
                                    <option value="">Pilih Kantor</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skip_weekends" id="skip_weekends" value="1" {{ old('skip_weekends') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="skip_weekends">
                                        Lewati Akhir Pekan (Sabtu & Minggu)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skip_existing" id="skip_existing" value="1" {{ old('skip_existing') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="skip_existing">
                                        Lewati Jadwal yang Sudah Ada
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Catatan tambahan untuk jadwal ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Preview Section -->
                        <div id="schedule_preview" class="alert alert-info" style="display: none;">
                            <h6><i class="fas fa-eye me-2"></i>Preview Jadwal</h6>
                            <div id="preview_content"></div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Buat Jadwal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bantuan Buat Jadwal Periode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Cara Menggunakan:</h6>
                <ol>
                    <li>Pilih satu atau lebih karyawan (gunakan Ctrl+Click)</li>
                    <li>Pilih shift yang akan diterapkan</li>
                    <li>Tentukan periode tanggal (maksimal 30 hari)</li>
                    <li>Pilih tipe kerja (WFO/WFA)</li>
                    <li>Jika WFO, pilih kantor</li>
                    <li>Atur opsi tambahan sesuai kebutuhan</li>
                    <li>Klik "Buat Jadwal"</li>
                </ol>
                
                <h6>Opsi Tambahan:</h6>
                <ul>
                    <li><strong>Lewati Akhir Pekan:</strong> Tidak membuat jadwal untuk Sabtu & Minggu</li>
                    <li><strong>Lewati Jadwal yang Sudah Ada:</strong> Tidak menimpa jadwal yang sudah ada</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize date constraints
    updateDateConstraints();
    
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
    
    // Update date constraints when start date changes
    $('#start_date').change(function() {
        updateDateConstraints();
        updatePreview();
    });
    
    // Update preview when form changes
    $('#user_ids, #end_date, #shift_id, #work_type, #office_id, #skip_weekends, #skip_existing').change(updatePreview);
    
    function updateDateConstraints() {
        const startDate = $('#start_date').val();
        if (startDate) {
            const start = new Date(startDate);
            const maxEnd = new Date(start);
            maxEnd.setDate(maxEnd.getDate() + 29); // 30 days total
            
            $('#end_date').attr('min', startDate);
            $('#end_date').attr('max', maxEnd.toISOString().split('T')[0]);
        }
    }
    
    function updatePreview() {
        const userIds = $('#user_ids').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const shiftId = $('#shift_id').val();
        const workType = $('#work_type').val();
        const skipWeekends = $('#skip_weekends').is(':checked');
        const skipExisting = $('#skip_existing').is(':checked');
        
        if (userIds && userIds.length > 0 && startDate && endDate && shiftId && workType) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            
            let previewHtml = '<div class="row">';
            previewHtml += '<div class="col-md-4"><strong>Karyawan:</strong> ' + userIds.length + ' orang</div>';
            previewHtml += '<div class="col-md-4"><strong>Periode:</strong> ' + daysDiff + ' hari</div>';
            previewHtml += '<div class="col-md-4"><strong>Shift:</strong> ' + $('#shift_id option:selected').text() + '</div>';
            previewHtml += '</div>';
            
            if (daysDiff > 30) {
                previewHtml += '<div class="alert alert-danger mt-2">⚠️ Periode melebihi 30 hari!</div>';
            }
            
            if (skipWeekends) {
                previewHtml += '<div class="text-info mt-2">ℹ️ Akhir pekan akan dilewati</div>';
            }
            
            if (skipExisting) {
                previewHtml += '<div class="text-info mt-2">ℹ️ Jadwal yang sudah ada akan dilewati</div>';
            }
            
            $('#preview_content').html(previewHtml);
            $('#schedule_preview').show();
        } else {
            $('#schedule_preview').hide();
        }
    }
    
    // Form submission with loading state
    $('#bulkCreateForm').submit(function() {
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Membuat Jadwal...');
    });
    
    // Initialize on page load
    $('#work_type').trigger('change');
});

// Helper functions for user selection
function selectAllUsers() {
    $('#user_ids option').prop('selected', true);
    updatePreview();
}

function clearAllUsers() {
    $('#user_ids option').prop('selected', false);
    updatePreview();
}
</script>
@endpush
