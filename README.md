# Sistem Penggajian STEA

Sistem Penggajian Terintegrasi dengan Absensi untuk PT. STEA Indonesia

## Fitur Utama

### 🔐 Manajemen User & Role
- **5 Role Berbeda**: CEO, CFO, HRD, Personalia, Karyawan
- **Dashboard Khusus** untuk setiap role dengan tampilan yang disesuaikan
- **Permission-based Access Control** untuk keamanan data

### 👥 Manajemen Karyawan
- **Data Karyawan Lengkap**: Informasi personal, jabatan, departemen
- **Struktur Organisasi**: Departemen dan posisi dengan hierarki
- **Bank Account Management**: Data rekening untuk transfer gaji

### ⏰ Sistem Absensi
- **Clock In/Out** dengan deteksi lokasi GPS
- **Break Time Management**: Pencatatan waktu istirahat
- **Automatic Calculation**: Perhitungan otomatis keterlambatan dan lembur
- **Real-time Monitoring**: Monitoring absensi real-time

### 📅 Manajemen Cuti
- **8 Jenis Cuti**: Tahunan, Sakit, Melahirkan, Menikah, dll.
- **Approval Workflow**: Sistem persetujuan bertingkat
- **Leave Balance**: Tracking sisa cuti karyawan

### 💰 Sistem Penggajian
- **Flexible Salary Components**: Tunjangan, potongan, benefit
- **Automatic Calculation**: Perhitungan otomatis berdasarkan absensi
- **Tax Calculation**: Perhitungan PPh 21 otomatis
- **Payroll Periods**: Manajemen periode penggajian

### 📊 Dashboard & Laporan
- **Role-specific Dashboards**: Dashboard khusus untuk setiap role
- **Comprehensive Reports**: Laporan HR, keuangan, dan absensi

## Teknologi yang Digunakan

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Bootstrap 5, Chart.js, jQuery
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel

## Instalasi

### Persyaratan Sistem
- PHP 8.1 atau lebih tinggi
- Composer
- Node.js & NPM
- MySQL 5.7+

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/Maretume/stea.git
cd stea
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Configuration**
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payroll_system
DB_USERNAME=root
DB_PASSWORD=
```

5. **Database Migration & Seeding**
```bash
php artisan migrate
php artisan db:seed
```

6. **Build Assets**
```bash
npm run build
```

7. **Start Development Server**
```bash
php artisan serve
```

## Demo Accounts

Setelah seeding, Anda dapat login dengan akun berikut:

| Role | Username | Password | Deskripsi |
|------|----------|----------|-----------|
| CEO | ceo.stea | password123 | Akses penuh ke semua modul |
| CFO | cfo.stea | password123 | Fokus pada laporan keuangan |
| HRD | hrd.stea | password123 | Manajemen HR lengkap |
| Personalia | personalia.stea | password123 | Input data dan monitoring |
| Karyawan | john.doe | password123 | Self-service untuk karyawan |

## Struktur Role & Permission

### CEO Dashboard
- Overview bisnis dan finansial
- Statistik karyawan per departemen
- Tren kinerja perusahaan
- Pengajuan cuti pending

### CFO Dashboard
- Analisis budget dan variance
- Tren biaya payroll
- Biaya per departemen
- Pending approvals

### HRD Dashboard
- Manajemen karyawan lengkap
- Approval cuti dan payroll
- Laporan HR dan absensi
- Konfigurasi sistem

### Personalia Dashboard
- Input data karyawan
- Monitoring absensi harian
- Data cuti mendatang
- Edit data attendance

### Karyawan Dashboard
- Clock in/out dengan GPS
- Slip gaji personal
- Pengajuan cuti
- Riwayat absensi pribadi

## Demo Accounts

| Role | Username | Password |
|------|----------|----------|
| CEO | ceo.stea | password123 |
| CFO | cfo.stea | password123 |
| HRD | hrd.stea | password123 |
| Personalia | personalia.stea | password123 |
| Karyawan | john.doe | password123 |

---

**PT. STEA Indonesia** - Sistem Penggajian Modern untuk Perusahaan Modern