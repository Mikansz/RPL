# Fitur Attempt Button untuk Sistem Absensi

## Deskripsi Perubahan

Sistem absensi telah diperbarui dengan fitur tombol "Attempt" yang menggantikan tombol "Clock In" dan "Clock Out" terpisah. Tombol ini secara otomatis menentukan apakah user perlu melakukan clock in atau clock out berdasarkan status absensi hari ini.

## Fitur Utama

### 1. Tombol Attempt Otomatis
- **Satu tombol untuk semua**: Menggantikan tombol Clock In dan Clock Out terpisah
- **Deteksi otomatis**: Sistem otomatis menentukan apakah ini clock in atau clock out
- **Status dinamis**: Tombol berubah warna dan teks sesuai dengan aksi yang akan dilakukan

### 2. Status Otomatis
- **Tepat waktu**: Status "present" untuk clock in tepat waktu
- **Terlambat**: Status "late" dengan perhitungan menit keterlambatan otomatis
- **Lembur**: Deteksi dan perhitungan lembur otomatis saat clock out
- **Pulang awal**: Deteksi pulang lebih awal dengan perhitungan menit

### 3. Pesan Informatif
- **Clock In**: "Clock in berhasil - Tepat waktu" atau "Clock in berhasil - Terlambat X menit"
- **Clock Out**: "Clock out berhasil - Tepat waktu", "Clock out berhasil - Lembur X jam Y menit", atau "Clock out berhasil - Pulang lebih awal X menit"

## Perubahan Teknis

### 1. Frontend (resources/views/attendance/clock.blade.php)
- Mengganti dua tombol (Clock In/Clock Out) menjadi satu tombol "Attempt"
- Tombol berubah warna dan teks berdasarkan status:
  - **Hijau**: Clock In (belum absen hari ini)
  - **Biru**: Clock Out (sudah clock in, belum clock out)
  - **Abu-abu**: Selesai (sudah lengkap)
- Fungsi JavaScript `performAttempt()` untuk menangani klik tombol
- Update fungsi `updateButtons()` untuk mengelola state tombol

### 2. Backend (app/Http/Controllers/AttendanceController.php)
- Method baru `attempt()` yang menentukan aksi berdasarkan status attendance
- Update pesan response di `clockIn()` dan `clockOut()` untuk lebih informatif
- Perhitungan otomatis status berdasarkan waktu shift

### 3. Routes (routes/web.php)
- Route baru: `POST /attendance/attempt`

## Cara Kerja

### Alur Kerja Tombol Attempt:
1. **Belum ada attendance hari ini**: Tombol hijau "Clock In" → Melakukan clock in
2. **Sudah clock in, belum clock out**: Tombol biru "Clock Out" → Melakukan clock out
3. **Sudah lengkap**: Tombol abu-abu "Selesai" → Disabled

### Perhitungan Status Otomatis:
1. **Clock In**:
   - Bandingkan waktu clock in dengan waktu mulai shift
   - Jika terlambat: status "late" + hitung menit keterlambatan
   - Jika tepat waktu: status "present"

2. **Clock Out**:
   - Bandingkan waktu clock out dengan waktu selesai shift
   - Jika lebih dari waktu selesai: hitung menit lembur
   - Jika kurang dari waktu selesai: hitung menit pulang awal
   - Hitung total jam kerja dikurangi waktu istirahat

## Keuntungan

1. **User Experience**: Interface lebih sederhana dengan satu tombol
2. **Otomatisasi**: Tidak perlu manual pilih clock in/out
3. **Akurasi**: Status otomatis berdasarkan perhitungan sistem
4. **Informasi Real-time**: Pesan langsung menunjukkan status (tepat waktu/terlambat/lembur)
5. **Konsistensi**: Semua perhitungan menggunakan data shift yang sama

## Kompatibilitas

- Fitur break (istirahat) tetap menggunakan tombol terpisah
- Sistem geofencing tetap berfungsi normal
- Validasi jadwal kerja tetap berlaku
- Sistem permission tidak berubah

## Testing

Untuk menguji fitur ini:
1. Akses halaman `/attendance/clock`
2. Pastikan ada jadwal kerja untuk hari ini
3. Klik tombol "Attempt" untuk clock in
4. Verifikasi pesan status yang muncul
5. Klik tombol "Attempt" lagi untuk clock out
6. Verifikasi perhitungan lembur/pulang awal

## Catatan Implementasi

- Semua perhitungan waktu menggunakan shift yang terdaftar di jadwal
- Status attendance otomatis tersimpan di database
- Pesan response memberikan informasi detail tentang status
- Tombol break tetap terpisah untuk fleksibilitas
