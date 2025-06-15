@extends('layouts.app')

@section('title', 'Clock In/Out')
@section('page-title', 'Absensi - Clock In/Out')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .user-location-marker {
        background: transparent;
        border: none;
    }
    .office-location-marker {
        background: transparent;
        border: none;
    }
    .map-container {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    .location-status {
        font-size: 0.9rem;
    }
    .distance-info {
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Current Time Display -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <h2 id="currentTime" class="text-primary mb-2"></h2>
                <p class="text-muted mb-0" id="currentDate"></p>
            </div>
        </div>
        
        <!-- Schedule Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Jadwal Kerja Hari Ini</h5>
            </div>
            <div class="card-body">
                <div id="scheduleStatus">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat jadwal kerja...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Status Absensi Hari Ini</h5>
            </div>
            <div class="card-body">
                <div id="attendanceStatus">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat status absensi...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attempt Button -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row" id="clockButtons">
                    <div class="col-12 mb-3">
                        <button type="button" class="btn btn-primary btn-lg w-100" id="attemptBtn" disabled>
                            <i class="fas fa-clock me-2"></i>
                            <span id="attemptBtnText">Attempt</span>
                        </button>
                        <small class="text-muted d-block mt-2 text-center" id="attemptBtnHint">
                            Klik untuk melakukan absensi otomatis
                        </small>
                    </div>
                </div>
                

            </div>
        </div>
        
        <!-- Location Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Informasi Lokasi</h5>
            </div>
            <div class="card-body">
                <div id="locationInfo">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Mendapatkan lokasi...</p>
                    </div>
                </div>

                <!-- Map Container -->
                <div id="mapContainer" style="display: none;">
                    <div class="mt-3">
                        <div id="locationValidation" class="mb-3"></div>
                        <div id="attendanceMap" style="height: 400px; border-radius: 8px;"></div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Peta menampilkan lokasi Anda saat ini dan radius kantor yang diizinkan untuk absensi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Attendance -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Absensi Terbaru</h5>
            </div>
            <div class="card-body">
                <div id="recentAttendance">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat riwayat absensi...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <!-- Content will be filled by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    let currentLocation = null;
    let todayAttendance = null;
    let attendanceMap = null;
    let currentSchedule = null;
    
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        $('#currentTime').text(timeString);
        $('#currentDate').text(dateString);
    }
    
    // Get user location
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    currentLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };

                    $('#locationInfo').html(`
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Lokasi berhasil didapatkan</strong><br>
                            <small>Lat: ${currentLocation.latitude.toFixed(6)}, Lng: ${currentLocation.longitude.toFixed(6)}</small>
                        </div>
                    `);

                    loadTodayAttendance();
                },
                function(error) {
                    $('#locationInfo').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Tidak dapat mendapatkan lokasi</strong><br>
                            <small>Pastikan GPS aktif dan izinkan akses lokasi</small>
                        </div>
                    `);

                    loadTodayAttendance();
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes
                }
            );
        } else {
            $('#locationInfo').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>Browser tidak mendukung geolocation</strong>
                </div>
            `);

            loadTodayAttendance();
        }
    }
    
    // Load today's attendance
    function loadTodayAttendance() {
        console.log('Loading today attendance...');

        $.get('{{ route("attendance.today") }}')
            .done(function(response) {
                console.log('Today attendance response:', response);

                if (response.success) {
                    todayAttendance = response.data.attendance;
                    updateScheduleStatus(response.data.schedule, response.data.can_clock_in, response.data.work_type);
                    updateAttendanceStatus();
                    updateButtons(response.data.can_clock_in);
                    loadRecentAttendance();
                } else {
                    console.error('API returned success=false:', response);
                    showErrorMessage('Gagal memuat data absensi: ' + (response.message || 'Unknown error'));
                }
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX request failed:', {xhr, status, error});
                console.error('Response text:', xhr.responseText);

                let errorMessage = 'Gagal memuat data absensi';
                if (xhr.status === 401) {
                    errorMessage = 'Sesi login telah berakhir. Silakan login kembali.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Terjadi kesalahan server. Silakan coba lagi.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                }

                showErrorMessage(errorMessage);
            });
    }

    function showErrorMessage(message) {
        $('#attendanceStatus').html(`
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                ${message}
            </div>
        `);
        $('#scheduleStatus').html(`
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                ${message}
            </div>
        `);
    }

    // Update schedule status display
    function updateScheduleStatus(schedule, canClockIn, workType) {
        currentSchedule = schedule; // Store schedule for map usage

        console.log('Schedule data:', schedule); // Debug log

        if (!schedule) {
            $('#scheduleStatus').html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Tidak ada jadwal kerja hari ini</strong><br>
                    <small>Silakan hubungi HR untuk mengatur jadwal kerja Anda</small>
                </div>
            `);
            return;
        }

        const workTypeClass = schedule.work_type === 'WFO' ? 'primary' : 'success';
        const workTypeIcon = schedule.work_type === 'WFO' ? 'building' : 'home';

        let scheduleHtml = `
            <div class="row">
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-${workTypeClass} bg-opacity-10 rounded p-3 mb-2">
                        <i class="fas fa-${workTypeIcon} fa-2x text-${workTypeClass}"></i>
                    </div>
                    <h6>Tipe Kerja</h6>
                    <h5 class="text-${workTypeClass}">${schedule.work_type}</h5>
                    <small class="text-muted">${schedule.work_type === 'WFO' ? 'Work From Office' : 'Work From Anywhere'}</small>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded p-3 mb-2">
                        <i class="fas fa-clock fa-2x text-info"></i>
                    </div>
                    <h6>Shift</h6>
                    <h5 class="text-info">${schedule.shift ? schedule.shift.name : 'N/A'}</h5>
                    <small class="text-muted">${getShiftTimeDisplay(schedule.shift)}</small>
                </div>`;

        if (schedule.office) {
            scheduleHtml += `
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-warning bg-opacity-10 rounded p-3 mb-2">
                        <i class="fas fa-map-marker-alt fa-2x text-warning"></i>
                    </div>
                    <h6>Kantor</h6>
                    <h5 class="text-warning">${schedule.office.name}</h5>
                    <small class="text-muted">Radius: ${schedule.office.radius}m</small>
                </div>`;
        } else {
            scheduleHtml += `
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-secondary bg-opacity-10 rounded p-3 mb-2">
                        <i class="fas fa-globe fa-2x text-secondary"></i>
                    </div>
                    <h6>Lokasi</h6>
                    <h5 class="text-secondary">Fleksibel</h5>
                    <small class="text-muted">Dapat bekerja dari mana saja</small>
                </div>`;
        }

        scheduleHtml += `
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-success bg-opacity-10 rounded p-3 mb-2">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <h6>Status</h6>
                    <h5 class="text-success">${schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1)}</h5>
                    <small class="text-muted">Jadwal disetujui</small>
                </div>
            </div>`;

        if (schedule.notes) {
            scheduleHtml += `
                <div class="mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Catatan:</strong> ${schedule.notes}
                    </div>
                </div>`;
        }

        $('#scheduleStatus').html(scheduleHtml);

        // Initialize map if location is available
        if (currentLocation && schedule) {
            initializeAttendanceMap();
        }
    }
    
    // Update attendance status display
    function updateAttendanceStatus() {
        if (!todayAttendance) {
            $('#attendanceStatus').html(`
                <div class="text-center py-4">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <h5>Belum ada absensi hari ini</h5>
                    <p class="text-muted">Silakan lakukan clock in untuk memulai</p>
                </div>
            `);
            return;
        }
        
        const attendance = todayAttendance;
        let statusHtml = '<div class="row">';
        
        // Clock In Status
        statusHtml += '<div class="col-md-4 text-center mb-3">';
        statusHtml += '<div class="bg-success bg-opacity-10 rounded p-3 mb-2">';
        statusHtml += '<i class="fas fa-sign-in-alt fa-2x text-success"></i>';
        statusHtml += '</div>';
        statusHtml += '<h6>Clock In</h6>';
        statusHtml += `<h5 class="text-success">${attendance.clock_in || '-'}</h5>`;
        if (attendance.late_minutes > 0) {
            statusHtml += `<small class="text-danger">Terlambat ${attendance.late_minutes} menit</small>`;
        } else if (attendance.early_minutes > 0) {
            statusHtml += `<small class="text-warning">Terlalu dini ${attendance.early_minutes} menit</small>`;
        } else if (attendance.clock_in) {
            statusHtml += `<small class="text-success">Tepat waktu</small>`;
        }
        statusHtml += '</div>';

        // Clock Out Status
        statusHtml += '<div class="col-md-4 text-center mb-3">';
        statusHtml += '<div class="bg-info bg-opacity-10 rounded p-3 mb-2">';
        statusHtml += '<i class="fas fa-sign-out-alt fa-2x text-info"></i>';
        statusHtml += '</div>';
        statusHtml += '<h6>Clock Out</h6>';
        statusHtml += `<h5 class="text-info">${attendance.clock_out || '-'}</h5>`;
        if (attendance.early_leave_minutes > 0) {
            statusHtml += `<small class="text-warning">Pulang awal ${attendance.early_leave_minutes} menit</small>`;
        } else if (attendance.overtime_minutes > 0) {
            statusHtml += `<small class="text-success">Lembur ${attendance.overtime_minutes} menit</small>`;
        } else if (attendance.clock_out) {
            statusHtml += `<small class="text-success">Tepat waktu</small>`;
        }
        statusHtml += '</div>';
        
        // Work Hours
        statusHtml += '<div class="col-md-4 text-center mb-3">';
        statusHtml += '<div class="bg-primary bg-opacity-10 rounded p-3 mb-2">';
        statusHtml += '<i class="fas fa-hourglass-half fa-2x text-primary"></i>';
        statusHtml += '</div>';
        statusHtml += '<h6>Jam Kerja</h6>';
        statusHtml += `<h5 class="text-primary">${(attendance.total_work_minutes / 60).toFixed(1)} jam</h5>`;
        statusHtml += '</div>';
        
        statusHtml += '</div>';
        
        $('#attendanceStatus').html(statusHtml);
    }
    
    // Update button states
    function updateButtons(canClockIn = false) {
        const attemptBtn = $('#attemptBtn');
        const attemptBtnText = $('#attemptBtnText');
        const attemptBtnHint = $('#attemptBtnHint');
        const breakStartBtn = $('#breakStartBtn');
        const breakEndBtn = $('#breakEndBtn');

        // Reset all buttons
        attemptBtn.prop('disabled', true);
        breakStartBtn.prop('disabled', true);
        breakEndBtn.prop('disabled', true);

        // Only enable buttons if user has approved schedule
        if (!canClockIn) {
            attemptBtnText.text('Attempt');
            attemptBtnHint.text('Tidak dapat melakukan absensi saat ini');
            return;
        }

        if (!todayAttendance) {
            // No attendance today - enable clock in
            attemptBtn.prop('disabled', false);
            attemptBtn.removeClass('btn-info btn-secondary').addClass('btn-success');
            attemptBtnText.text('Clock In');
            attemptBtnHint.text('Klik untuk melakukan clock in otomatis');
        } else {
            const attendance = todayAttendance;

            if (!attendance.clock_in) {
                // Not clocked in yet
                attemptBtn.prop('disabled', false);
                attemptBtn.removeClass('btn-info btn-secondary').addClass('btn-success');
                attemptBtnText.text('Clock In');
                attemptBtnHint.text('Klik untuk melakukan clock in otomatis');
            } else if (!attendance.clock_out) {
                // Clocked in but not out
                attemptBtn.prop('disabled', false);
                attemptBtn.removeClass('btn-success btn-secondary').addClass('btn-info');
                attemptBtnText.text('Clock Out');
                attemptBtnHint.text('Klik untuk melakukan clock out otomatis');


            } else {
                // Already completed for today
                attemptBtn.prop('disabled', true);
                attemptBtn.removeClass('btn-success btn-info').addClass('btn-secondary');
                attemptBtnText.text('Selesai');
                attemptBtnHint.text('Absensi hari ini sudah lengkap');
            }
        }
    }
    
    // Load recent attendance
    function loadRecentAttendance() {
        $('#recentAttendance').html(`
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat riwayat absensi...</p>
            </div>
        `);

        $.get('{{ route("attendance.recent") }}')
            .done(function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '<div class="list-group list-group-flush">';
                    response.data.forEach(function(attendance) {
                        const date = new Date(attendance.date).toLocaleDateString('id-ID');
                        const clockIn = attendance.clock_in ? attendance.clock_in : '-';
                        const clockOut = attendance.clock_out ? attendance.clock_out : '-';
                        const statusClass = getStatusClass(attendance.status);
                        const statusText = getStatusText(attendance.status);

                        html += `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${date}</h6>
                                        <small class="text-muted">Masuk: ${clockIn} | Keluar: ${clockOut}</small>
                                    </div>
                                    <span class="badge ${statusClass}">${statusText}</span>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#recentAttendance').html(html);
                } else {
                    $('#recentAttendance').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Belum ada riwayat absensi</p>
                        </div>
                    `);
                }
            })
            .fail(function() {
                $('#recentAttendance').html(`
                    <div class="text-center py-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                        <p class="text-muted">Gagal memuat riwayat absensi</p>
                    </div>
                `);
            });
    }

    function getStatusClass(status) {
        switch(status) {
            case 'present': return 'bg-success';
            case 'late': return 'bg-warning';
            case 'early': return 'bg-info';
            case 'early_leave': return 'bg-info';
            case 'absent': return 'bg-danger';
            case 'sick': return 'bg-secondary';
            case 'leave': return 'bg-primary';
            case 'holiday': return 'bg-dark';
            case 'half_day': return 'bg-light text-dark';
            default: return 'bg-secondary';
        }
    }

    function getStatusText(status) {
        switch(status) {
            case 'present': return 'Hadir';
            case 'late': return 'Terlambat';
            case 'early': return 'Terlalu Dini';
            case 'early_leave': return 'Pulang Awal';
            case 'absent': return 'Tidak Hadir';
            case 'sick': return 'Sakit';
            case 'leave': return 'Cuti';
            case 'holiday': return 'Libur';
            case 'half_day': return 'Setengah Hari';
            default: return 'Unknown';
        }
    }

    function getShiftTimeDisplay(shift) {
        if (!shift) {
            return 'Waktu tidak tersedia';
        }

        // Try formatted times first
        if (shift.formatted_start_time && shift.formatted_end_time) {
            return shift.formatted_start_time + ' - ' + shift.formatted_end_time;
        }

        // Fallback to raw times and format them
        if (shift.start_time && shift.end_time) {
            const startTime = formatTime(shift.start_time);
            const endTime = formatTime(shift.end_time);
            return startTime + ' - ' + endTime;
        }

        return 'Waktu tidak tersedia';
    }

    function formatTime(timeString) {
        if (!timeString) return '';

        // Handle different time formats
        if (timeString.includes('T')) {
            // ISO datetime format
            const date = new Date(timeString);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        } else if (timeString.includes(':')) {
            // Time format HH:MM or HH:MM:SS
            const parts = timeString.split(':');
            return parts[0] + ':' + parts[1];
        }

        return timeString;
    }

    // Initialize attendance map
    function initializeAttendanceMap() {
        if (!currentLocation || !currentSchedule) {
            return;
        }

        // Show map container
        $('#mapContainer').show();

        // Initialize map if not already done
        if (!attendanceMap) {
            attendanceMap = L.map('attendanceMap').setView([currentLocation.latitude, currentLocation.longitude], 16);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(attendanceMap);
        }

        // Clear existing layers
        attendanceMap.eachLayer(function(layer) {
            if (layer instanceof L.Marker || layer instanceof L.Circle) {
                attendanceMap.removeLayer(layer);
            }
        });

        // Add user location marker
        const userMarker = L.marker([currentLocation.latitude, currentLocation.longitude], {
            icon: L.divIcon({
                className: 'user-location-marker',
                html: '<i class="fas fa-user-circle" style="color: #007bff; font-size: 24px;"></i>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(attendanceMap);
        userMarker.bindPopup('<b>Lokasi Anda</b><br>Posisi saat ini');

        // Add office location and validation for WFO
        if (currentSchedule.work_type === 'WFO' && currentSchedule.office) {
            const office = currentSchedule.office;

            // Add office marker
            const officeMarker = L.marker([office.latitude, office.longitude], {
                icon: L.divIcon({
                    className: 'office-location-marker',
                    html: '<i class="fas fa-building" style="color: #dc3545; font-size: 20px;"></i>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            }).addTo(attendanceMap);
            officeMarker.bindPopup(`<b>${office.name}</b><br>Kantor`);

            // Add radius circle
            const radiusCircle = L.circle([office.latitude, office.longitude], {
                color: '#dc3545',
                fillColor: '#dc3545',
                fillOpacity: 0.1,
                radius: office.radius
            }).addTo(attendanceMap);

            // Calculate distance and show validation
            const distance = calculateDistance(
                currentLocation.latitude,
                currentLocation.longitude,
                office.latitude,
                office.longitude
            );

            const isWithinRadius = distance <= office.radius;
            updateLocationValidation(distance, office.radius, office.name, isWithinRadius);

            // Fit map to show both user and office
            const group = new L.featureGroup([userMarker, officeMarker, radiusCircle]);
            attendanceMap.fitBounds(group.getBounds().pad(0.1));
        } else {
            // WFA - just show user location
            attendanceMap.setView([currentLocation.latitude, currentLocation.longitude], 16);
            updateLocationValidation(null, null, null, true, 'WFA');
        }
    }

    // Calculate distance between two points using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Update location validation display
    function updateLocationValidation(distance, requiredRadius, officeName, isValid, workType = 'WFO') {
        let validationHtml = '';

        if (workType === 'WFA') {
            validationHtml = `
                <div class="alert alert-success location-status">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Work From Anywhere (WFA)</strong><br>
                    <small>Anda dapat melakukan absensi dari lokasi mana saja</small>
                </div>
            `;
        } else if (isValid) {
            validationHtml = `
                <div class="alert alert-success location-status">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Lokasi Valid</strong><br>
                    <small>Anda berada dalam radius ${requiredRadius}m dari ${officeName}</small>
                    <br><small class="text-muted">Jarak: ${Math.round(distance)}m</small>
                </div>
            `;
        } else {
            validationHtml = `
                <div class="alert alert-danger location-status">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>Lokasi Tidak Valid</strong><br>
                    <small>Anda berada ${Math.round(distance)}m dari ${officeName}</small>
                    <br><small class="text-muted">Diperlukan: dalam radius ${requiredRadius}m</small>
                </div>
            `;
        }

        $('#locationValidation').html(validationHtml);
    }

    // Attempt function - automatically determines clock in or clock out
    function performAttempt() {
        // Check if location is available
        if (!currentLocation) {
            alert('Lokasi GPS diperlukan untuk absensi. Pastikan GPS aktif dan izinkan akses lokasi.');
            return;
        }

        // Show confirmation modal with location info
        showAttendanceConfirmation();
    }

    // Show attendance confirmation modal
    function showAttendanceConfirmation() {
        let modalBody = `
            <div class="mb-3">
                <h6><i class="fas fa-map-marker-alt me-2"></i>Informasi Lokasi</h6>
                <p class="mb-2"><strong>Koordinat:</strong> ${currentLocation.latitude.toFixed(6)}, ${currentLocation.longitude.toFixed(6)}</p>
        `;

        if (currentSchedule && currentSchedule.work_type === 'WFO' && currentSchedule.office) {
            const office = currentSchedule.office;
            const distance = calculateDistance(
                currentLocation.latitude,
                currentLocation.longitude,
                office.latitude,
                office.longitude
            );
            const isValid = distance <= office.radius;

            modalBody += `
                <p class="mb-2"><strong>Kantor:</strong> ${office.name}</p>
                <p class="mb-2"><strong>Jarak:</strong> ${Math.round(distance)}m</p>
                <p class="mb-0"><strong>Status:</strong>
                    <span class="badge ${isValid ? 'bg-success' : 'bg-danger'}">
                        ${isValid ? 'Dalam Radius' : 'Diluar Radius'}
                    </span>
                </p>
            `;
        } else if (currentSchedule && currentSchedule.work_type === 'WFA') {
            modalBody += `
                <p class="mb-0"><strong>Tipe Kerja:</strong>
                    <span class="badge bg-success">Work From Anywhere</span>
                </p>
            `;
        }

        modalBody += `
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Pastikan lokasi Anda sudah benar sebelum melanjutkan absensi.
            </div>
        `;

        $('#confirmModalTitle').text('Konfirmasi Absensi');
        $('#confirmModalBody').html(modalBody);

        // Set confirm button action
        $('#confirmBtn').off('click').on('click', function() {
            $('#confirmModal').modal('hide');
            executeAttempt();
        });

        $('#confirmModal').modal('show');
    }

    // Execute the actual attempt
    function executeAttempt() {
        const data = {
            _token: '{{ csrf_token() }}'
        };

        if (currentLocation) {
            data.latitude = currentLocation.latitude;
            data.longitude = currentLocation.longitude;
        }

        // Show loading state
        const attemptBtn = $('#attemptBtn');
        const originalText = attemptBtn.html();
        attemptBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');

        $.post(`{{ url('/attendance') }}/attempt`, data)
            .done(function(response) {
                if (response.success) {
                    // Show success message
                    showSuccessMessage(response.message);
                    loadTodayAttendance();
                } else {
                    // Show error message with location data if available
                    let errorMessage = response.message;
                    if (response.location_data) {
                        errorMessage += `\n\nInformasi Lokasi:`;
                        if (response.location_data.distance) {
                            errorMessage += `\nJarak: ${Math.round(response.location_data.distance)}m`;
                        }
                        if (response.location_data.required_radius) {
                            errorMessage += `\nRadius yang diperlukan: ${response.location_data.required_radius}m`;
                        }
                    }
                    alert('Error: ' + errorMessage);
                }
            })
            .fail(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            })
            .always(function() {
                // Restore button state
                attemptBtn.prop('disabled', false).html(originalText);
            });
    }

    // Show success message
    function showSuccessMessage(message) {
        // Create success alert
        const successAlert = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Berhasil!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert at the top of the page
        $('.row.justify-content-center').prepend(`<div class="col-lg-8">${successAlert}</div>`);

        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert-success').alert('close');
        }, 5000);
    }

    // Clock In/Out functions (for break buttons)
    function performClockAction(action) {
        const data = {
            _token: '{{ csrf_token() }}'
        };

        if (currentLocation) {
            data.latitude = currentLocation.latitude;
            data.longitude = currentLocation.longitude;
        }

        $.post(`{{ url('/attendance') }}/${action}`, data)
            .done(function(response) {
                if (response.success) {
                    alert('Berhasil! ' + response.message);
                    loadTodayAttendance();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
    }
    
    // Event handlers
    $('#attemptBtn').click(function() {
        performAttempt();
    });


    
    // Initialize
    updateTime();
    setInterval(updateTime, 1000);
    getLocation();
});
</script>
@endpush
