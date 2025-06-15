# Fitur Map Lokasi untuk Attendance System

## Deskripsi
Fitur ini menambahkan peta interaktif yang menampilkan lokasi saat ini pengguna dan radius kantor saat melakukan attempt absensi. Fitur ini membantu karyawan memahami apakah mereka berada dalam radius yang diizinkan untuk melakukan absensi.

## Fitur yang Ditambahkan

### 1. Peta Interaktif
- **Library**: Leaflet.js untuk rendering peta
- **Tile Layer**: OpenStreetMap
- **Marker User**: Ikon user biru menunjukkan lokasi saat ini
- **Marker Office**: Ikon building merah menunjukkan lokasi kantor
- **Circle Radius**: Lingkaran merah menunjukkan radius absensi kantor

### 2. Validasi Lokasi Real-time
- **WFO (Work From Office)**: Validasi jarak terhadap kantor yang ditugaskan
- **WFA (Work From Anywhere)**: Tidak ada validasi lokasi
- **Status Visual**: Alert hijau (valid) atau merah (tidak valid)
- **Informasi Jarak**: Menampilkan jarak aktual ke kantor dalam meter

### 3. Konfirmasi Absensi
- **Modal Konfirmasi**: Menampilkan informasi lokasi sebelum absensi
- **Koordinat GPS**: Latitude dan longitude saat ini
- **Status Lokasi**: Valid/tidak valid dengan badge warna
- **Informasi Kantor**: Nama kantor dan jarak untuk WFO

## File yang Dimodifikasi

### 1. Frontend (resources/views/attendance/clock.blade.php)
```php
// Penambahan CSS dan JavaScript library
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .user-location-marker { background: transparent; border: none; }
    .office-location-marker { background: transparent; border: none; }
    .map-container { border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden; }
    .location-status { font-size: 0.9rem; }
    .distance-info { background: rgba(0,0,0,0.7); color: white; padding: 8px 12px; border-radius: 4px; font-size: 0.85rem; }
</style>
@endpush

// Penambahan container map
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
```

### 2. JavaScript Functions
- **initializeAttendanceMap()**: Inisialisasi peta dengan marker dan radius
- **calculateDistance()**: Menghitung jarak menggunakan formula Haversine
- **updateLocationValidation()**: Update status validasi lokasi
- **showAttendanceConfirmation()**: Modal konfirmasi dengan info lokasi
- **executeAttempt()**: Eksekusi absensi dengan loading state

### 3. Backend (app/Http/Controllers/AttendanceController.php)
```php
/**
 * Validate current location for attendance
 */
public function validateCurrentLocation(Request $request)
{
    $request->validate([
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]);

    $user = Auth::user();
    $todaySchedule = $user->getTodaySchedule();

    // Validasi untuk WFA dan WFO
    // Return JSON response dengan status validasi
}
```

### 4. Routes (routes/web.php)
```php
Route::post('/validate-location', [AttendanceController::class, 'validateCurrentLocation'])->name('validate-location');
```

## Cara Kerja

### 1. Inisialisasi
1. User membuka halaman attendance/clock
2. Browser meminta izin akses lokasi GPS
3. Setelah lokasi didapat, sistem load jadwal hari ini
4. Jika ada jadwal, peta akan ditampilkan

### 2. Tampilan Peta
- **WFO**: Menampilkan marker user, marker kantor, dan radius circle
- **WFA**: Hanya menampilkan marker user
- **Auto-fit**: Peta otomatis menyesuaikan zoom untuk menampilkan semua marker

### 3. Validasi Lokasi
- **Real-time**: Validasi dilakukan saat peta dimuat
- **Visual Feedback**: Alert berwarna menunjukkan status
- **Informasi Detail**: Jarak aktual dan radius yang diperlukan

### 4. Proses Absensi
1. User klik tombol "Attempt"
2. Sistem cek ketersediaan lokasi GPS
3. Tampilkan modal konfirmasi dengan info lokasi
4. User konfirmasi untuk melanjutkan
5. Sistem kirim data ke server dengan koordinat
6. Server validasi lokasi dan proses absensi

## Keamanan dan Validasi

### 1. Client-side
- Validasi ketersediaan GPS
- Validasi akurasi lokasi
- Timeout handling untuk GPS

### 2. Server-side
- Validasi koordinat GPS (latitude: -90 to 90, longitude: -180 to 180)
- Validasi jadwal kerja aktif
- Validasi radius kantor menggunakan GeofencingService
- Audit trail lokasi untuk tracking

## Testing

### File Test
- **public/test-attendance-map-new.html**: Halaman test standalone
- **Fitur Test**: Get location, simulate office location, test attempt
- **Sample Data**: Kantor Jakarta dengan radius 100m

### Skenario Test
1. **Normal WFO**: User di dalam radius kantor
2. **WFO Outside**: User di luar radius kantor  
3. **WFA**: User dengan jadwal Work From Anywhere
4. **No GPS**: Browser tidak support atau GPS diblokir
5. **No Schedule**: User tanpa jadwal kerja

## Browser Compatibility
- **Modern Browsers**: Chrome, Firefox, Safari, Edge
- **Mobile**: Android Chrome, iOS Safari
- **Requirements**: GPS/Location services enabled
- **Fallback**: Graceful degradation jika GPS tidak tersedia

## Performance Considerations
- **Lazy Loading**: Peta hanya dimuat setelah lokasi didapat
- **Caching**: Koordinat office di-cache di client
- **Optimized**: Tile loading dari OpenStreetMap
- **Memory**: Proper cleanup saat peta di-reinitialize

## Future Enhancements
1. **Offline Maps**: Support untuk area dengan koneksi terbatas
2. **Multiple Offices**: Support untuk karyawan dengan multiple office assignment
3. **Geofence History**: Tracking pergerakan dalam radius kantor
4. **Push Notifications**: Notifikasi saat masuk/keluar radius kantor
5. **Advanced Analytics**: Heatmap lokasi absensi karyawan
