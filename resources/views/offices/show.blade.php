@extends('layouts.app')

@section('title', 'Detail Kantor')
@section('page-title', 'Detail Kantor - ' . $office->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Office Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informasi Kantor</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Nama Kantor:</strong></td>
                                <td>{{ $office->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Latitude:</strong></td>
                                <td>{{ $office->latitude }}</td>
                            </tr>
                            <tr>
                                <td><strong>Longitude:</strong></td>
                                <td>{{ $office->longitude }}</td>
                            </tr>
                            <tr>
                                <td><strong>Radius:</strong></td>
                                <td>{{ $office->radius }} meter</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Status:</strong></td>
                                <td>
                                    @if($office->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td>{{ $office->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Diupdate:</strong></td>
                                <td>{{ $office->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                            @if($office->address)
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td>{{ $office->address }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Lokasi Kantor</h5>
            </div>
            <div class="card-body">
                <div id="map" style="height: 400px; border-radius: 8px;"></div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Area merah menunjukkan radius absensi {{ $office->radius }} meter dari titik kantor.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('offices.edit', $office) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Kantor
                    </a>
                    <a href="{{ route('offices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                    @if($office->is_active)
                        <button type="button" class="btn btn-outline-warning" onclick="toggleStatus(false)">
                            <i class="fas fa-pause me-2"></i>Nonaktifkan
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-success" onclick="toggleStatus(true)">
                            <i class="fas fa-play me-2"></i>Aktifkan
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-danger" onclick="deleteOffice()">
                        <i class="fas fa-trash me-2"></i>Hapus Kantor
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $office->schedules->count() ?? 0 }}</h4>
                            <small class="text-muted">Jadwal Kerja</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $office->attendances->count() ?? 0 }}</h4>
                        <small class="text-muted">Total Absensi</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if($office->attendances && $office->attendances->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Aktivitas Terbaru</h5>
            </div>
            <div class="card-body">
                @foreach($office->attendances->take(5) as $attendance)
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">{{ $attendance->user->full_name ?? 'Unknown' }}</h6>
                        <small class="text-muted">
                            {{ $attendance->date->format('d M Y') }} - 
                            @if($attendance->check_in)
                                {{ $attendance->check_in->format('H:i') }}
                            @endif
                        </small>
                    </div>
                    <div class="flex-shrink-0">
                        @if($attendance->status == 'present')
                            <span class="badge bg-success">Hadir</span>
                        @elseif($attendance->status == 'late')
                            <span class="badge bg-warning">Terlambat</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($attendance->status) }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
                
                @if($office->attendances->count() > 5)
                <div class="text-center">
                    <small class="text-muted">Dan {{ $office->attendances->count() - 5 }} aktivitas lainnya...</small>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" action="{{ route('offices.destroy', $office) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
.leaflet-container {
    border-radius: 8px;
}
.avatar {
    width: 32px;
    height: 32px;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

function initMap() {
    const lat = {{ $office->latitude }};
    const lng = {{ $office->longitude }};
    const radius = {{ $office->radius }};
    
    const map = L.map('map').setView([lat, lng], 16);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add marker for office location
    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup('<b>{{ $office->name }}</b><br>Lokasi Kantor').openPopup();
    
    // Add radius circle
    const circle = L.circle([lat, lng], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);
    
    circle.bindPopup('Radius Absensi: ' + radius + ' meter');
}

function toggleStatus(status) {
    const action = status ? 'mengaktifkan' : 'menonaktifkan';
    
    if (confirm(`Apakah Anda yakin ingin ${action} kantor ini?`)) {
        // Create form to update status
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("offices.update", $office) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';
        
        const nameField = document.createElement('input');
        nameField.type = 'hidden';
        nameField.name = 'name';
        nameField.value = '{{ $office->name }}';
        
        const latField = document.createElement('input');
        latField.type = 'hidden';
        latField.name = 'latitude';
        latField.value = '{{ $office->latitude }}';
        
        const lngField = document.createElement('input');
        lngField.type = 'hidden';
        lngField.name = 'longitude';
        lngField.value = '{{ $office->longitude }}';
        
        const radiusField = document.createElement('input');
        radiusField.type = 'hidden';
        radiusField.name = 'radius';
        radiusField.value = '{{ $office->radius }}';
        
        const statusField = document.createElement('input');
        statusField.type = 'hidden';
        statusField.name = 'is_active';
        statusField.value = status ? '1' : '0';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(nameField);
        form.appendChild(latField);
        form.appendChild(lngField);
        form.appendChild(radiusField);
        form.appendChild(statusField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteOffice() {
    if (confirm('Apakah Anda yakin ingin menghapus kantor ini? Tindakan ini tidak dapat dibatalkan!')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
