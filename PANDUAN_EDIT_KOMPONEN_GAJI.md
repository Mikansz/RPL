# 📖 Panduan Lengkap Edit Komponen Gaji

## 🎯 Tujuan
Panduan ini menjelaskan cara menggunakan fitur edit komponen gaji untuk HRD dan CFO.

## 👥 Siapa yang Bisa Menggunakan?

### ✅ **HRD (Human Resources Department)**
- ✅ Melihat semua komponen gaji
- ✅ Membuat komponen gaji baru
- ✅ Mengedit komponen gaji
- ✅ Mengaktifkan/nonaktifkan komponen

### ✅ **CFO (Chief Financial Officer)**
- ✅ Melihat semua komponen gaji
- ✅ Mengedit komponen gaji
- ✅ Mengaktifkan/nonaktifkan komponen
- ❌ Tidak dapat membuat komponen baru

### ✅ **Admin**
- ✅ Akses penuh ke semua fitur

## 🚀 Cara Mengakses Menu Komponen Gaji

### **Langkah 1: Login**
1. Login ke sistem dengan akun HRD atau CFO
2. Pastikan Anda memiliki role yang sesuai

### **Langkah 2: Navigasi Menu**
1. Dari dashboard, klik menu **"Penggajian"** di sidebar kiri
2. Pilih submenu **"Komponen Gaji"**
3. Atau akses langsung melalui URL: `/salary-components`

## ✏️ Cara Edit Komponen Gaji

### **Langkah 1: Pilih Komponen**
1. Di halaman "Manajemen Komponen Gaji", Anda akan melihat daftar komponen
2. Cari komponen yang ingin diedit
3. Klik tombol **"Edit"** (ikon pensil) di kolom "Aksi"

### **Langkah 2: Form Edit**
Form edit akan menampilkan field berikut:

#### **📝 Field yang Bisa Diedit:**

1. **Nama Komponen** *(Wajib)*
   - Nama tampilan komponen gaji
   - Contoh: "Tunjangan Transport", "Potongan BPJS"
   - Maksimal 100 karakter

2. **Kode Komponen** *(Wajib, Unik)*
   - Kode unik untuk identifikasi
   - Contoh: "TRANSPORT", "BPJS_HEALTH"
   - Maksimal 20 karakter
   - Tidak boleh sama dengan komponen lain

3. **Tipe Komponen** *(Wajib)*
   - **Tunjangan (Allowance)**: Menambah gaji
   - **Potongan (Deduction)**: Mengurangi gaji

4. **Tipe Perhitungan** *(Wajib)*
   - **Fixed (Tetap)**: Nominal tetap
   - **Percentage (Persentase)**: Persentase dari gaji pokok

5. **Nilai Default** *(Wajib)*
   - Untuk Fixed: Masukkan nominal (contoh: 500000)
   - Untuk Percentage: Masukkan angka persentase (contoh: 10)

6. **Persentase** *(Jika tipe = Percentage)*
   - Persentase dari gaji pokok
   - Contoh: 10 untuk 10%

7. **Kena Pajak**
   - Centang jika komponen ini kena pajak PPh 21
   - Tidak centang jika tidak kena pajak

8. **Status Aktif**
   - Centang jika komponen aktif digunakan
   - Tidak centang untuk menonaktifkan

9. **Urutan**
   - Urutan tampilan di slip gaji
   - Angka lebih kecil = tampil lebih atas

10. **Deskripsi** *(Opsional)*
    - Keterangan tambahan tentang komponen

### **Langkah 3: Simpan Perubahan**
1. Setelah mengisi semua field yang diperlukan
2. Klik tombol **"Update"** untuk menyimpan
3. Sistem akan memvalidasi data dan menampilkan pesan sukses

## 🔄 Fitur Toggle Status

### **Mengaktifkan/Nonaktifkan Komponen:**
1. Di halaman daftar komponen gaji
2. Klik tombol toggle status (warna hijau = aktif, abu-abu = nonaktif)
3. Komponen nonaktif tidak akan muncul di perhitungan payroll baru

### **Kapan Menggunakan Toggle:**
- ✅ **Aktifkan**: Komponen digunakan dalam perhitungan gaji
- ❌ **Nonaktifkan**: Komponen tidak digunakan (lebih aman daripada hapus)

## ⚠️ Validasi dan Batasan

### **Validasi Form:**
- ✅ Nama komponen wajib diisi
- ✅ Kode komponen wajib diisi dan harus unik
- ✅ Tipe komponen wajib dipilih
- ✅ Nilai default wajib diisi dan harus berupa angka
- ✅ Jika tipe percentage, field persentase wajib diisi

### **Batasan Penghapusan:**
- ❌ Komponen yang digunakan karyawan tidak bisa dihapus
- ❌ Komponen yang ada di record payroll tidak bisa dihapus
- ✅ Alternatif: nonaktifkan komponen

## 📊 Contoh Komponen Gaji Standar

### **Tunjangan (Allowances):**
- **Tunjangan Transport**: Fixed Rp 500.000
- **Tunjangan Makan**: Fixed Rp 300.000
- **Tunjangan Kinerja**: Percentage 10% dari gaji pokok
- **Tunjangan Kehadiran**: Fixed Rp 200.000

### **Potongan (Deductions):**
- **BPJS Kesehatan**: Percentage 1% dari gaji pokok
- **BPJS Ketenagakerjaan**: Percentage 2% dari gaji pokok
- **Potongan Keterlambatan**: Fixed Rp 50.000
- **Potongan Alpha**: Fixed Rp 100.000

## 🛡️ Tips Keamanan

### **Best Practices:**
1. **Backup Data**: Selalu backup sebelum edit massal
2. **Test Kecil**: Test edit pada 1-2 komponen dulu
3. **Verifikasi**: Cek hasil edit di slip gaji test
4. **Dokumentasi**: Catat perubahan yang dilakukan

### **Hindari:**
- ❌ Mengubah kode komponen yang sudah digunakan
- ❌ Menghapus komponen yang masih aktif digunakan
- ❌ Mengubah tipe perhitungan tanpa pertimbangan matang

## 🔗 URL dan Route Penting

### **Menu Utama:**
- `/salary-components` - Daftar komponen gaji
- `/salary-components/create` - Buat komponen baru (HRD only)
- `/salary-components/{id}/edit` - Edit komponen
- `/salary-components/{id}` - Detail komponen

### **Testing:**
- `/test/salary-components-edit` - Test functionality
- `/test/edit-salary-component/{code}` - Test edit specific component

## 🆘 Troubleshooting

### **Masalah Umum:**

1. **"Anda tidak memiliki akses"**
   - Pastikan login dengan akun HRD/CFO/Admin
   - Hubungi admin untuk cek permission

2. **"Kode komponen sudah ada"**
   - Gunakan kode yang unik
   - Cek daftar komponen yang sudah ada

3. **"Komponen tidak bisa dihapus"**
   - Komponen sedang digunakan
   - Gunakan toggle status untuk nonaktifkan

4. **Form tidak tersimpan**
   - Cek validasi form (field merah)
   - Pastikan semua field wajib diisi

## 📞 Bantuan

Jika mengalami kesulitan:
1. Cek panduan ini kembali
2. Test menggunakan route testing yang tersedia
3. Hubungi administrator sistem
4. Dokumentasikan error yang terjadi

---

**✅ Sistem edit komponen gaji siap digunakan dengan aman dan mudah!**
