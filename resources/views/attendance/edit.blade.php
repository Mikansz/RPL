@extends('layouts.app')

@section('title', 'Edit Absensi')
@section('page-title', 'Edit Data Absensi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Absensi - {{ $attendance->user->full_name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('attendance.update', $attendance) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Employee Info -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Informasi Karyawan</h6>
                                    <p class="mb-1"><strong>{{ $attendance->user->full_name }}</strong></p>
                                    <p class="mb-1">{{ $attendance->user->employee_id }}</p>
                                    <p class="mb-0">{{ $attendance->user->employee->department->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Tanggal</h6>
                                    <p class="mb-0">
                                        <strong>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d F Y') : 'N/A' }}</strong>
                                        <br>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('l') : '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Times -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="clock_in" class="form-label">Clock In</label>
                            <input type="time" class="form-control @error('clock_in') is-invalid @enderror" 
                                   id="clock_in" name="clock_in" 
                                   value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                            @error('clock_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="clock_out" class="form-label">Clock Out</label>
                            <input type="time" class="form-control @error('clock_out') is-invalid @enderror" 
                                   id="clock_out" name="clock_out" 
                                   value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                            @error('clock_out')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    

                    
                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="early" {{ old('status', $attendance->status) == 'early' ? 'selected' : '' }}>Terlalu Dini</option>
                            <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="early_leave" {{ old('status', $attendance->status) == 'early_leave' ? 'selected' : '' }}>Pulang Awal</option>
                            <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Alpha</option>
                            <option value="sick" {{ old('status', $attendance->status) == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="leave" {{ old('status', $attendance->status) == 'leave' ? 'selected' : '' }}>Cuti</option>
                            <option value="holiday" {{ old('status', $attendance->status) == 'holiday' ? 'selected' : '' }}>Libur</option>
                            <option value="half_day" {{ old('status', $attendance->status) == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Manual Adjustments -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Penyesuaian Manual</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="late_minutes" class="form-label">Menit Terlambat</label>
                                    <input type="number" class="form-control @error('late_minutes') is-invalid @enderror"
                                           id="late_minutes" name="late_minutes" min="0"
                                           value="{{ old('late_minutes', $attendance->late_minutes ?? 0) }}">
                                    @error('late_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="early_minutes" class="form-label">Menit Terlalu Dini</label>
                                    <input type="number" class="form-control @error('early_minutes') is-invalid @enderror"
                                           id="early_minutes" name="early_minutes" min="0"
                                           value="{{ old('early_minutes', $attendance->early_minutes ?? 0) }}">
                                    @error('early_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="early_leave_minutes" class="form-label">Menit Pulang Awal</label>
                                    <input type="number" class="form-control @error('early_leave_minutes') is-invalid @enderror"
                                           id="early_leave_minutes" name="early_leave_minutes" min="0"
                                           value="{{ old('early_leave_minutes', $attendance->early_leave_minutes ?? 0) }}">
                                    @error('early_leave_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="overtime_minutes" class="form-label">Menit Lembur</label>
                                    <input type="number" class="form-control @error('overtime_minutes') is-invalid @enderror"
                                           id="overtime_minutes" name="overtime_minutes" min="0"
                                           value="{{ old('overtime_minutes', $attendance->overtime_minutes ?? 0) }}">
                                    @error('overtime_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Values Display -->
                    <div class="card bg-info bg-opacity-10 mb-3">
                        <div class="card-body">
                            <h6 class="text-info">Perhitungan Otomatis</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Total Jam Kerja:</small>
                                    <p class="mb-0" id="totalWorkHours">{{ number_format(($attendance->total_work_minutes ?? 0) / 60, 1) }} jam</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Jam Efektif:</small>
                                    <p class="mb-0" id="effectiveHours">{{ number_format(($attendance->total_work_minutes ?? 0) / 60, 1) }} jam</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <button type="button" class="btn btn-info me-2" onclick="calculateTimes()">
                                <i class="fas fa-calculator me-2"></i>Hitung Ulang
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Absensi
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
$(document).ready(function() {
    // Auto-calculate when times change
    $('#clock_in, #clock_out').change(function() {
        calculateTimes();
    });
    
    // Auto-set status based on times
    $('#clock_in, #late_minutes, #early_minutes, #early_leave_minutes').change(function() {
        const lateMinutes = parseInt($('#late_minutes').val()) || 0;
        const earlyMinutes = parseInt($('#early_minutes').val()) || 0;
        const earlyLeaveMinutes = parseInt($('#early_leave_minutes').val()) || 0;
        const status = $('#status');

        if (lateMinutes > 0) {
            status.val('late');
        } else if (earlyLeaveMinutes > 0) {
            status.val('early_leave');
        } else if (earlyMinutes > 0) {
            status.val('early');
        } else if ($('#clock_in').val()) {
            status.val('present');
        }
    });
});

function calculateTimes() {
    const clockIn = $('#clock_in').val();
    const clockOut = $('#clock_out').val();

    if (clockIn && clockOut) {
        // Calculate total work time
        const startTime = new Date(`2000-01-01 ${clockIn}`);
        const endTime = new Date(`2000-01-01 ${clockOut}`);

        let totalMinutes = (endTime - startTime) / (1000 * 60);

        // Update display
        $('#totalWorkHours').text((totalMinutes / 60).toFixed(1) + ' jam');
        $('#effectiveHours').text((totalMinutes / 60).toFixed(1) + ' jam');
        
        // Auto-calculate late/early minutes
        const standardStart = new Date(`2000-01-01 08:00`);
        if (startTime > standardStart) {
            const lateMinutes = (startTime - standardStart) / (1000 * 60);
            $('#late_minutes').val(Math.round(lateMinutes));
            $('#early_minutes').val(0);
        } else if (startTime < standardStart) {
            const earlyMinutes = (standardStart - startTime) / (1000 * 60);
            $('#early_minutes').val(Math.round(earlyMinutes));
            $('#late_minutes').val(0);
        } else {
            $('#late_minutes').val(0);
            $('#early_minutes').val(0);
        }
        
        // Auto-calculate early leave
        const standardEnd = new Date(`2000-01-01 17:00`);
        if (endTime < standardEnd) {
            const earlyMinutes = (standardEnd - endTime) / (1000 * 60);
            $('#early_leave_minutes').val(Math.round(earlyMinutes));
        } else {
            $('#early_leave_minutes').val(0);
        }
        
        // Auto-calculate overtime
        if (endTime > standardEnd) {
            const overtimeMinutes = (endTime - standardEnd) / (1000 * 60);
            $('#overtime_minutes').val(Math.round(overtimeMinutes));
        } else {
            $('#overtime_minutes').val(0);
        }
    }
}
</script>
@endpush
