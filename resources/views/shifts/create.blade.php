@extends('layouts.app')

@section('title', 'Tambah Shift')
@section('page-title', 'Tambah Shift Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Tambah Shift Kerja Baru
                </h5>
            </div>
            
            <form method="POST" action="{{ route('shifts.store') }}">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" 
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
                                   value="{{ old('start_time') }}" required>
                            @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="end_time" 
                                   class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time') }}" required>
                            @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <!-- Preview -->
                    <div id="shift_preview" class="card bg-light" style="display: none;">
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
                                    <p id="preview_start" class="mb-0">-</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-warning bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-stop fa-2x text-warning"></i>
                                    </div>
                                    <h6>Selesai</h6>
                                    <p id="preview_end" class="mb-0">-</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-primary bg-opacity-10 rounded p-3 mb-2">
                                        <i class="fas fa-clock fa-2x text-primary"></i>
                                    </div>
                                    <h6>Durasi</h6>
                                    <p id="preview_duration" class="mb-0">-</p>
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
                            Simpan Shift
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
            $('#shift_preview').show();
            
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
        } else {
            $('#shift_preview').hide();
        }
    }
});
</script>
@endpush
