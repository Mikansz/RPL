@extends('layouts.app')

@section('title', 'Edit Lembur')
@section('page-title', 'Edit Permohonan Lembur')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Permohonan Lembur</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permits.overtime.update', $overtime) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Overtime Date -->
                    <div class="mb-3">
                        <label for="overtime_date" class="form-label">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('overtime_date') is-invalid @enderror" 
                               id="overtime_date" name="overtime_date" 
                               value="{{ old('overtime_date', $overtime->overtime_date) }}" 
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
                                   id="start_time" name="start_time" 
                                   value="{{ old('start_time', $overtime->start_time) }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Waktu mulai lembur</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                   id="end_time" name="end_time" 
                                   value="{{ old('end_time', $overtime->end_time) }}" required>
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
                                  placeholder="Jelaskan pekerjaan yang akan dilakukan saat lembur..." required>{{ old('work_description', $overtime->work_description) }}</textarea>
                        @error('work_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Lembur <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror"
                                  id="reason" name="reason" rows="3"
                                  placeholder="Jelaskan alasan mengapa perlu lembur..." required>{{ old('reason', $overtime->reason) }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current Status Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Status Saat Ini:</strong> 
                        <span class="badge bg-{{ $overtime->status === 'pending' ? 'warning' : ($overtime->status === 'approved' ? 'success' : 'danger') }}">
                            {{ ucfirst($overtime->status) }}
                        </span>
                        <br>
                        <small class="mt-1 d-block">
                            @if($overtime->status === 'pending')
                                Permohonan masih menunggu persetujuan dan dapat diubah.
                            @elseif($overtime->status === 'approved')
                                Permohonan telah disetujui pada {{ $overtime->approved_at?->format('d M Y H:i') }}.
                            @else
                                Permohonan telah ditolak pada {{ $overtime->approved_at?->format('d M Y H:i') }}.
                            @endif
                        </small>
                    </div>

                    <!-- Edit Rules -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Hanya permohonan dengan status "Pending" yang dapat diubah</li>
                            <li>Perubahan tanggal lembur harus minimal H-1 sebelum tanggal lembur</li>
                            <li>Lembur maksimal 4 jam per hari dan 14 jam per minggu</li>
                            <li>Setelah diubah, permohonan akan kembali menunggu persetujuan</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('permits.overtime.show', $overtime) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <a href="{{ route('permits.overtime.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-list me-2"></i>Daftar Lembur
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
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
