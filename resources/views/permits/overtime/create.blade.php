@extends('layouts.app')

@section('title', 'Ajukan Lembur')
@section('page-title', 'Ajukan Permohonan Lembur')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Form Permohonan Lembur</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permits.overtime.store') }}">
                    @csrf
                    
                    <!-- Overtime Date -->
                    <div class="mb-3">
                        <label for="overtime_date" class="form-label">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('overtime_date') is-invalid @enderror" 
                               id="overtime_date" name="overtime_date" value="{{ old('overtime_date') }}" 
                               min="{{ date('Y-m-d') }}" required>
                        @error('overtime_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pilih tanggal lembur yang akan dilakukan</small>
                    </div>

                    <!-- Time Range -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                   id="start_time" name="start_time" value="{{ old('start_time', '18:00') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Waktu mulai lembur</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                   id="end_time" name="end_time" value="{{ old('end_time', '20:00') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Waktu selesai lembur</small>
                        </div>
                    </div>

                    <!-- Duration Display -->
                    <div class="mb-3">
                        <label class="form-label">Durasi Lembur</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="duration_display" readonly>
                            <span class="input-group-text">jam</span>
                        </div>
                        <small class="text-muted">Durasi akan dihitung otomatis berdasarkan waktu mulai dan selesai</small>
                    </div>

                    <!-- Work Description -->
                    <div class="mb-3">
                        <label for="work_description" class="form-label">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('work_description') is-invalid @enderror"
                                  id="work_description" name="work_description" rows="4"
                                  placeholder="Jelaskan pekerjaan yang akan dilakukan saat lembur..." required>{{ old('work_description') }}</textarea>
                        @error('work_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Lembur <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror"
                                  id="reason" name="reason" rows="3"
                                  placeholder="Jelaskan alasan mengapa perlu lembur..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

                    <!-- Approval Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi Persetujuan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Permohonan lembur harus diajukan minimal H-1 sebelum tanggal lembur</li>
                            <li>Lembur maksimal 4 jam per hari dan 14 jam per minggu</li>
                            <li>Persetujuan akan diberikan oleh supervisor/manager langsung</li>
                            <li>Kompensasi akan dihitung berdasarkan gaji pokok dan jenis lembur</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('permits.overtime.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Ajukan Lembur
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
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const durationDisplay = document.getElementById('duration_display');

    function calculateDuration() {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        if (startTime && endTime) {
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);

            if (end > start) {
                const diffMs = end - start;
                const diffHours = diffMs / (1000 * 60 * 60);

                durationDisplay.value = diffHours.toFixed(1);
                return diffHours;
            } else {
                durationDisplay.value = '';
                return 0;
            }
        }
        return 0;
    }

    // Event listeners
    startTimeInput.addEventListener('change', calculateDuration);
    endTimeInput.addEventListener('change', calculateDuration);

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const overtimeDate = document.getElementById('overtime_date').value;
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        const workDescription = document.getElementById('work_description').value.trim();
        const reason = document.getElementById('reason').value.trim();

        if (!overtimeDate) {
            e.preventDefault();
            alert('Tanggal lembur harus diisi!');
            return;
        }

        if (!startTime || !endTime) {
            e.preventDefault();
            alert('Waktu mulai dan selesai harus diisi!');
            return;
        }

        if (!workDescription) {
            e.preventDefault();
            alert('Deskripsi pekerjaan harus diisi!');
            return;
        }

        if (!reason) {
            e.preventDefault();
            alert('Alasan lembur harus diisi!');
            return;
        }

        const duration = calculateDuration();
        if (duration <= 0) {
            e.preventDefault();
            alert('Waktu selesai harus lebih besar dari waktu mulai!');
            return;
        }

        if (duration > 8) {
            e.preventDefault();
            alert('Durasi lembur maksimal 8 jam per hari!');
            return;
        }

        // Check if date is not in the past
        const today = new Date();
        const selectedDate = new Date(overtimeDate);
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            e.preventDefault();
            alert('Tanggal lembur tidak boleh di masa lalu!');
            return;
        }
    });

    // Initialize calculation
    calculateDuration();
});
</script>
@endpush
