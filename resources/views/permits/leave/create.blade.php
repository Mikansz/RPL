@extends('layouts.app')

@section('title', 'Ajukan Cuti')
@section('page-title', 'Ajukan Permohonan Cuti')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Form Permohonan Cuti</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permits.leave.store') }}">
                    @csrf
                    
                    <!-- Leave Type -->
                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                        <select class="form-select @error('leave_type_id') is-invalid @enderror"
                                id="leave_type_id" name="leave_type_id" required>
                            <option value="">Pilih Jenis Cuti</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->max_days_per_year }} hari/tahun)
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <div class="input-group">
                            <input type="text" class="form-control" id="duration_display" readonly>
                            <span class="input-group-text">hari</span>
                        </div>
                        <small class="text-muted">Durasi akan dihitung otomatis berdasarkan tanggal mulai dan selesai</small>
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Cuti <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" 
                                  id="reason" name="reason" rows="4" 
                                  placeholder="Jelaskan alasan mengambil cuti..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Emergency Contact -->
                    <div class="mb-3">
                        <label for="emergency_contact" class="form-label">Kontak Darurat</label>
                        <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror" 
                               id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}" 
                               placeholder="Nomor telepon yang bisa dihubungi saat cuti">
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Opsional: Nomor yang bisa dihubungi dalam keadaan darurat</small>
                    </div>

                    <!-- Leave Balance Info -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Saldo Cuti</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>Total Sisa Cuti:</strong> {{ $totalRemainingDays ?? 12 }} hari<br>
                                        <strong>Cuti Pending:</strong> {{ $pendingLeaveDays ?? 0 }} hari<br>
                                        <strong>Tersedia:</strong> {{ ($totalRemainingDays ?? 12) - ($pendingLeaveDays ?? 0) }} hari
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-end">
                                        <h6 class="text-primary mb-0">
                                            <strong>Sisa: {{ ($totalRemainingDays ?? 12) - ($pendingLeaveDays ?? 0) }} hari</strong>
                                        </h6>
                                        <small class="text-muted">Tersedia untuk digunakan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Catatan tambahan untuk atasan...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Balance Warning -->
                    <div id="balance-warning"></div>

                    <!-- Approval Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi Persetujuan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Cuti tahunan maksimal 12 hari per tahun</li>
                            <li>Cuti sakit memerlukan surat keterangan dokter untuk lebih dari 2 hari</li>
                            <li>Persetujuan akan diberikan oleh supervisor/manager langsung</li>
                            <li>Pastikan pekerjaan sudah didelegasikan sebelum cuti</li>
                            <li>Status permohonan dapat dipantau di dashboard cuti</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('permits.leave.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <button type="button" class="btn btn-warning me-2" onclick="debugForm()">
                                <i class="fas fa-bug me-2"></i>Debug
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Ajukan Cuti
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const durationDisplay = document.getElementById('duration_display');
    const leaveTypeSelect = document.getElementById('leave_type_id');
    
    // Set minimum date to today
    const today = new Date();
    const minDate = today.toISOString().split('T')[0];

    startDateInput.min = minDate;
    endDateInput.min = minDate;

    function calculateDuration() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = end.getTime() - start.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                durationDisplay.value = diffDays;
                
                // Check leave balance (warning only, not blocking)
                const availableBalance = {{ ($totalRemainingDays ?? 12) - ($pendingLeaveDays ?? 0) }};
                if (diffDays > availableBalance) {
                    console.warn(`Durasi cuti (${diffDays} hari) melebihi saldo cuti tersedia (${availableBalance} hari)!`);
                    // Show warning in UI instead of alert
                    const warningDiv = document.getElementById('balance-warning');
                    if (warningDiv) {
                        warningDiv.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Durasi cuti (${diffDays} hari) melebihi saldo tersedia (${availableBalance} hari)</div>`;
                    }
                } else {
                    const warningDiv = document.getElementById('balance-warning');
                    if (warningDiv) {
                        warningDiv.innerHTML = '';
                    }
                }
                
                return diffDays;
            } else {
                durationDisplay.value = '';
                return 0;
            }
        }
        return 0;
    }

    // Event listeners
    startDateInput.addEventListener('change', function() {
        // Update minimum end date
        if (this.value) {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        }
        calculateDuration();
    });

    endDateInput.addEventListener('change', calculateDuration);

    // Leave type specific validations
    leaveTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        const reasonField = document.getElementById('reason');
        
        // Auto-fill reason placeholder based on leave type
        switch(selectedType) {
            case '1': // Annual Leave
                reasonField.placeholder = 'Contoh: Liburan keluarga, istirahat, dll.';
                break;
            case '2': // Sick Leave
                reasonField.placeholder = 'Contoh: Sakit demam, perlu perawatan medis, dll.';
                break;
            case '3': // Maternity Leave
                reasonField.placeholder = 'Cuti melahirkan sesuai peraturan perusahaan';
                break;
            case '4': // Special Leave
                reasonField.placeholder = 'Contoh: Pernikahan, kematian keluarga, dll.';
                break;
            default:
                reasonField.placeholder = 'Jelaskan alasan mengambil cuti...';
        }
    });

    // Form validation with debugging
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('Form submit triggered');

        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const leaveType = leaveTypeSelect.value;
        const reason = document.getElementById('reason').value.trim();

        console.log('Form data:', { startDate, endDate, leaveType, reason });

        // Basic validation
        if (!startDate || !endDate) {
            e.preventDefault();
            alert('Tanggal mulai dan selesai harus diisi!');
            return false;
        }

        if (!leaveType) {
            e.preventDefault();
            alert('Jenis cuti harus dipilih!');
            return false;
        }

        if (!reason) {
            e.preventDefault();
            alert('Alasan cuti harus diisi!');
            return false;
        }

        const duration = calculateDuration();
        if (duration <= 0) {
            e.preventDefault();
            alert('Tanggal selesai harus sama atau setelah tanggal mulai!');
            return false;
        }

        // Check if start date is not in the past
        const today = new Date();
        const startDateObj = new Date(startDate);
        today.setHours(0, 0, 0, 0);
        startDateObj.setHours(0, 0, 0, 0);

        if (startDateObj < today) {
            e.preventDefault();
            alert('Tanggal mulai cuti tidak boleh di masa lalu!');
            return false;
        }

        console.log('All validations passed, submitting form...');

        // Show loading state
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        }

        return true; // Allow form submission
    });

    // Initialize calculation
    calculateDuration();

    // Debug function
    window.debugForm = function() {
        const formData = new FormData(document.querySelector('form'));
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        console.log('Form Data:', data);
        alert('Form data logged to console. Check browser developer tools.');

        // Test form submission to debug endpoint
        fetch('/test/submit-leave', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            console.log('Debug response:', result);
            alert('Debug response: ' + JSON.stringify(result, null, 2));
        })
        .catch(error => {
            console.error('Debug error:', error);
            alert('Debug error: ' + error.message);
        });
    };
});
</script>
@endpush
