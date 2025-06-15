@extends('layouts.app')

@section('title', 'Edit Shift')
@section('page-title', 'Edit Shift Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Shift Kerja - {{ $shift->name }}
                </h5>
            </div>
            
            <form method="POST" action="{{ route('shifts.update', $shift) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $shift->name) }}" 
                               placeholder="Contoh: Shift Pagi" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="start_time" 
                                   class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', $shift->start_time->format('H:i')) }}" required>
                            @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="end_time" 
                                   class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', $shift->end_time->format('H:i')) }}" required>
                            @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" 
                                   class="form-check-input @error('is_active') is-invalid @enderror" 
                                   value="1" {{ old('is_active', $shift->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">
                                Shift Aktif
                            </label>
                            @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Shift yang tidak aktif tidak dapat digunakan untuk jadwal baru
                            </div>
                        </div>
                    </div>

                    <!-- Current Usage Info -->
                    @php
                        $activeSchedulesCount = $shift->schedules()
                                                     ->where('status', 'approved')
                                                     ->where('schedule_date', '>=', today())
                                                     ->count();
                        $totalSchedulesCount = $shift->schedules()->count();
                    @endphp
                    
                    @if($activeSchedulesCount > 0 || $totalSchedulesCount > 0)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Informasi Penggunaan Shift</h6>
                        <ul class="mb-0">
                            <li>Total jadwal menggunakan shift ini: <strong>{{ $totalSchedulesCount }}</strong></li>
                            <li>Jadwal aktif yang akan datang: <strong>{{ $activeSchedulesCount }}</strong></li>
                            @if($activeSchedulesCount > 0)
                            <li class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Shift tidak dapat dinonaktifkan karena masih memiliki jadwal aktif
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    <!-- Preview -->
                    <div id="shift_preview" class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-eye me-2"></i>
                                Preview Shift
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="bg-info bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-play fa-2x text-info"></i>
                                    </div>
                                    <h6>Mulai</h6>
                                    <p id="preview_start" class="mb-0">{{ $shift->formatted_start_time }}</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-warning bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-stop fa-2x text-warning"></i>
                                    </div>
                                    <h6>Selesai</h6>
                                    <p id="preview_end" class="mb-0">{{ $shift->formatted_end_time }}</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-primary bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-clock fa-2x text-primary"></i>
                                    </div>
                                    <h6>Durasi</h6>
                                    <p id="preview_duration" class="mb-0">{{ $shift->shift_duration }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Update Shift
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
    $('#start_time, #end_time').change(updatePreview);
    
    function updatePreview() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (startTime && endTime) {
            // Update preview
            $('#preview_start').text(startTime);
            $('#preview_end').text(endTime);
            
            // Calculate duration
            const start = new Date('2000-01-01 ' + startTime);
            const end = new Date('2000-01-01 ' + endTime);
            
            // Handle overnight shifts
            if (end < start) {
                end.setDate(end.getDate() + 1);
            }
            
            const diffMs = end - start;
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            
            $('#preview_duration').text(diffHours + ' jam ' + diffMinutes + ' menit');
        }
    }
});
</script>
@endpush
