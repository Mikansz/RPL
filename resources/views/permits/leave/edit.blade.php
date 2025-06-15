@extends('layouts.app')

@section('title', 'Edit Pengajuan Cuti')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Pengajuan Cuti
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permits.leave.update', $leave) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Employee Info (for HRD) -->
                    @if(auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR']))
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>Karyawan:</strong> {{ $leave->user->employee->full_name ?? $leave->user->first_name . ' ' . $leave->user->last_name }}
                                <br><strong>ID:</strong> {{ $leave->user->employee->employee_id ?? $leave->user->username }}
                                @if($leave->user->employee && $leave->user->employee->department)
                                    <br><strong>Departemen:</strong> {{ $leave->user->employee->department->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leave_type_id" class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                                <select name="leave_type_id" id="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis Cuti</option>
                                    @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id', $leave->leave_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} (Max: {{ $type->max_days_per_year }} hari/tahun)
                                    </option>
                                    @endforeach
                                </select>
                                @error('leave_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_half_day" id="is_half_day" value="1" 
                                           {{ old('is_half_day', $leave->is_half_day) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_half_day">
                                        Cuti Setengah Hari
                                    </label>
                                </div>
                                <div id="half_day_type_container" style="{{ old('is_half_day', $leave->is_half_day) ? '' : 'display: none;' }}">
                                    <select name="half_day_type" id="half_day_type" class="form-select mt-2">
                                        <option value="">Pilih Waktu</option>
                                        <option value="morning" {{ old('half_day_type', $leave->half_day_type) == 'morning' ? 'selected' : '' }}>Pagi</option>
                                        <option value="afternoon" {{ old('half_day_type', $leave->half_day_type) == 'afternoon' ? 'selected' : '' }}>Siang</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', $leave->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date', $leave->end_date->format('Y-m-d')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Cuti <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="3" 
                                  class="form-control @error('reason') is-invalid @enderror" 
                                  placeholder="Jelaskan alasan mengajukan cuti..." required>{{ old('reason', $leave->reason) }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" id="notes" rows="2" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  placeholder="Catatan tambahan (opsional)">{{ old('notes', $leave->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergency_contact" class="form-label">Kontak Darurat</label>
                                <input type="text" name="emergency_contact" id="emergency_contact" 
                                       class="form-control @error('emergency_contact') is-invalid @enderror" 
                                       value="{{ old('emergency_contact', $leave->emergency_contact) }}"
                                       placeholder="Nama kontak darurat">
                                @error('emergency_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergency_phone" class="form-label">Telepon Darurat</label>
                                <input type="text" name="emergency_phone" id="emergency_phone" 
                                       class="form-control @error('emergency_phone') is-invalid @enderror" 
                                       value="{{ old('emergency_phone', $leave->emergency_phone) }}"
                                       placeholder="Nomor telepon darurat">
                                @error('emergency_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="work_handover" class="form-label">Serah Terima Pekerjaan</label>
                        <textarea name="work_handover" id="work_handover" rows="3" 
                                  class="form-control @error('work_handover') is-invalid @enderror" 
                                  placeholder="Jelaskan serah terima pekerjaan selama cuti...">{{ old('work_handover', $leave->work_handover) }}</textarea>
                        @error('work_handover')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="attachments" class="form-label">Lampiran Tambahan</label>
                        <input type="file" name="attachments[]" id="attachments" 
                               class="form-control @error('attachments.*') is-invalid @enderror" 
                               multiple accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">
                            Format yang diizinkan: PDF, JPG, JPEG, PNG. Maksimal 2MB per file.
                        </div>
                        @error('attachments.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Existing Attachments -->
                    @if($leave->attachments && count($leave->attachments) > 0)
                    <div class="mb-3">
                        <label class="form-label">Lampiran Saat Ini</label>
                        <div class="row">
                            @foreach($leave->attachments as $attachment)
                            <div class="col-md-6 mb-2">
                                <div class="card card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small><strong>{{ $attachment['original_name'] }}</strong></small><br>
                                            <small class="text-muted">{{ number_format($attachment['size'] / 1024, 2) }} KB</small>
                                        </div>
                                        <a href="{{ asset('uploads/leaves/' . $attachment['filename']) }}" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('permits.leave.show', $leave) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('is_half_day').addEventListener('change', function() {
    const container = document.getElementById('half_day_type_container');
    const endDateInput = document.getElementById('end_date');
    const startDateInput = document.getElementById('start_date');
    
    if (this.checked) {
        container.style.display = 'block';
        // Set end date same as start date for half day
        if (startDateInput.value) {
            endDateInput.value = startDateInput.value;
        }
    } else {
        container.style.display = 'none';
        document.getElementById('half_day_type').value = '';
    }
});

document.getElementById('start_date').addEventListener('change', function() {
    const endDateInput = document.getElementById('end_date');
    const isHalfDay = document.getElementById('is_half_day').checked;
    
    if (isHalfDay) {
        endDateInput.value = this.value;
    } else if (!endDateInput.value || endDateInput.value < this.value) {
        endDateInput.value = this.value;
    }
});

// Set minimum date to today for regular users
@if(!auth()->user()->hasAnyRole(['Admin', 'HRD', 'HR']))
document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
document.getElementById('end_date').min = new Date().toISOString().split('T')[0];
@endif
</script>
@endsection
