@extends('layouts.app')

@section('title', 'Buat Periode Payroll')
@section('page-title', 'Buat Periode Payroll Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Buat Periode Payroll Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payroll.periods.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Periode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="Contoh: Gaji Januari 2024">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pay_date" class="form-label">Tanggal Pembayaran</label>
                            <input type="date" class="form-control @error('pay_date') is-invalid @enderror" 
                                   id="pay_date" name="pay_date" value="{{ old('pay_date') }}">
                            @error('pay_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    

                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Periode payroll akan dibuat dengan status "Draft"</li>
                            <li>Setelah dibuat, Anda dapat menghitung payroll untuk semua karyawan</li>
                            <li>Setelah dihitung, periode dapat disetujui oleh CFO</li>
                            <li>Pastikan tanggal tidak bertumpang tindih dengan periode lain</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('payroll.periods.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Periode
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
$(document).ready(function() {
    // Auto-generate name based on dates
    $('#start_date, #end_date').change(function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            const month = monthNames[start.getMonth()];
            const year = start.getFullYear();
            
            if ($('#name').val() === '') {
                $('#name').val(`Gaji ${month} ${year}`);
            }
            
            // Auto-set pay date to end of month + 5 days
            if ($('#pay_date').val() === '') {
                const payDate = new Date(end);
                payDate.setDate(payDate.getDate() + 5);
                $('#pay_date').val(payDate.toISOString().split('T')[0]);
            }
        }
    });
    
    // Validate date range
    $('#end_date').change(function() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($(this).val());
        
        if (endDate <= startDate) {
            alert('Tanggal selesai harus lebih besar dari tanggal mulai');
            $(this).val('');
        }
    });
});
</script>
@endpush
