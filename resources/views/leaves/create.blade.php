@extends('layouts.app')

@section('title', 'Ajukan Cuti')
@section('page-title', 'Ajukan Cuti Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Form Pengajuan Cuti</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Leave Type -->
                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                        <select class="form-select @error('leave_type_id') is-invalid @enderror" 
                                id="leave_type_id" name="leave_type_id" required>
                            <option value="">Pilih Jenis Cuti</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" 
                                        data-max-days="{{ $type->max_days }}"
                                        data-requires-document="{{ $type->requires_document ? 'true' : 'false' }}"
                                        {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} 
                                    @if($type->max_days)
                                        (Maks: {{ $type->max_days }} hari)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" id="leaveTypeInfo"></small>
                    </div>
                    
                    <!-- Date Range -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Duration Display -->
                    <div class="mb-3">
                        <label class="form-label">Durasi Cuti</label>
                        <div class="alert alert-info" id="durationDisplay">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span id="durationText">Pilih tanggal untuk melihat durasi</span>
                        </div>
                    </div>
                    
                    <!-- Reason -->
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Cuti <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" 
                                  id="reason" name="reason" rows="4" required 
                                  placeholder="Jelaskan alasan pengajuan cuti...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_name" class="form-label">Nama Kontak Darurat</label>
                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   id="emergency_contact_name" name="emergency_contact_name" 
                                   value="{{ old('emergency_contact_name') }}"
                                   placeholder="Nama yang dapat dihubungi">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_phone" class="form-label">Nomor Kontak Darurat</label>
                            <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                   id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="{{ old('emergency_contact_phone') }}"
                                   placeholder="Nomor telepon yang dapat dihubungi">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Supporting Document -->
                    <div class="mb-3" id="documentSection" style="display: none;">
                        <label for="supporting_document" class="form-label">Dokumen Pendukung <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('supporting_document') is-invalid @enderror" 
                               id="supporting_document" name="supporting_document" 
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX. Maksimal 5MB</small>
                        @error('supporting_document')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Work Handover -->
                    <div class="mb-3">
                        <label for="work_handover" class="form-label">Serah Terima Pekerjaan</label>
                        <textarea class="form-control @error('work_handover') is-invalid @enderror" 
                                  id="work_handover" name="work_handover" rows="3"
                                  placeholder="Jelaskan pekerjaan yang perlu diserahkan kepada rekan kerja...">{{ old('work_handover') }}</textarea>
                        @error('work_handover')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Leave Balance Info -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi Saldo Cuti</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Saldo Cuti Tahunan:</small>
                                    <h5 class="text-primary">{{ auth()->user()->annual_leave_balance ?? 12 }} hari</h5>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Cuti Terpakai:</small>
                                    <h5 class="text-warning">{{ auth()->user()->used_leave_days ?? 0 }} hari</h5>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Sisa Cuti:</small>
                                    <h5 class="text-success">{{ (auth()->user()->annual_leave_balance ?? 12) - (auth()->user()->used_leave_days ?? 0) }} hari</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input @error('agree_terms') is-invalid @enderror" 
                                   type="checkbox" id="agree_terms" name="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                Saya menyetujui <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">syarat dan ketentuan</a> pengajuan cuti
                            </label>
                            @error('agree_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Ajukan Cuti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Syarat dan Ketentuan Pengajuan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ol>
                    <li>Pengajuan cuti harus diajukan minimal 3 hari sebelum tanggal cuti dimulai</li>
                    <li>Cuti tahunan maksimal 12 hari per tahun</li>
                    <li>Cuti sakit harus disertai surat keterangan dokter untuk lebih dari 2 hari</li>
                    <li>Pengajuan cuti akan diproses dalam 1-2 hari kerja</li>
                    <li>Cuti yang sudah disetujui tidak dapat dibatalkan kecuali ada kondisi darurat</li>
                    <li>Karyawan wajib menyelesaikan serah terima pekerjaan sebelum cuti</li>
                    <li>Selama cuti, karyawan dapat dihubungi untuk hal-hal mendesak</li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate duration when dates change
    $('#start_date, #end_date').change(function() {
        calculateDuration();
    });
    
    // Show/hide document section based on leave type
    $('#leave_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const requiresDocument = selectedOption.data('requires-document');
        const maxDays = selectedOption.data('max-days');
        
        if (requiresDocument) {
            $('#documentSection').show();
            $('#supporting_document').prop('required', true);
        } else {
            $('#documentSection').hide();
            $('#supporting_document').prop('required', false);
        }
        
        if (maxDays) {
            $('#leaveTypeInfo').text(`Maksimal ${maxDays} hari untuk jenis cuti ini`);
        } else {
            $('#leaveTypeInfo').text('');
        }
        
        calculateDuration();
    });
    
    function calculateDuration() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const timeDiff = end.getTime() - start.getTime();
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                
                $('#durationText').text(`${daysDiff} hari (${start.toLocaleDateString('id-ID')} - ${end.toLocaleDateString('id-ID')})`);
                
                // Check against max days
                const selectedOption = $('#leave_type_id').find('option:selected');
                const maxDays = selectedOption.data('max-days');
                
                if (maxDays && daysDiff > maxDays) {
                    $('#durationDisplay').removeClass('alert-info').addClass('alert-danger');
                    $('#durationText').append(` - Melebihi batas maksimal ${maxDays} hari!`);
                    $('#submitBtn').prop('disabled', true);
                } else {
                    $('#durationDisplay').removeClass('alert-danger').addClass('alert-info');
                    $('#submitBtn').prop('disabled', false);
                }
            } else {
                $('#durationText').text('Tanggal selesai harus setelah tanggal mulai');
                $('#durationDisplay').removeClass('alert-info').addClass('alert-danger');
                $('#submitBtn').prop('disabled', true);
            }
        } else {
            $('#durationText').text('Pilih tanggal untuk melihat durasi');
            $('#durationDisplay').removeClass('alert-danger').addClass('alert-info');
        }
    }
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    $('#start_date').attr('min', today);
    
    // Update end date minimum when start date changes
    $('#start_date').change(function() {
        $('#end_date').attr('min', $(this).val());
    });
    
    // File size validation
    $('#supporting_document').change(function() {
        const file = this.files[0];
        if (file && file.size > 5 * 1024 * 1024) { // 5MB
            alert('Ukuran file tidak boleh lebih dari 5MB');
            $(this).val('');
        }
    });
});
</script>
@endpush
