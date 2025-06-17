# 📅 Panduan Penggunaan Fitur Jadwal Kerja

## 🎉 **Masalah 404 Telah Diperbaiki!**

Fitur pembuatan jadwal kerja sekarang dapat digunakan dengan normal. Berikut panduan lengkap penggunaannya.

## 🚀 **Cara Menggunakan Fitur Jadwal Kerja**

### **1. Mengakses Halaman Jadwal**

#### **Melalui Menu Sidebar:**
1. Login ke sistem
2. Klik menu **"Jadwal Kerja"** di sidebar kiri
3. Halaman daftar jadwal akan terbuka

#### **Melalui URL Langsung:**
```
http://localhost/gax/schedules
```

### **2. Membuat Jadwal Baru**

#### **Syarat:**
- User harus memiliki role: **Admin**, **HRD**, **HR**, atau **Manager**
- Role **Karyawan** hanya dapat melihat jadwal, tidak dapat membuat

#### **Langkah-langkah:**
1. Di halaman jadwal, klik tombol **"Tambah Jadwal"** (biru)
2. Isi form dengan data berikut:
   - **Karyawan**: Pilih karyawan yang akan dijadwalkan
   - **Tanggal Jadwal**: Pilih tanggal (minimal hari ini)
   - **Shift**: Pilih shift kerja (Pagi/Siang/Malam/Fleksibel)
   - **Tipe Kerja**: 
     - **WFO** (Work From Office) - Kerja di kantor
     - **WFA** (Work From Anywhere) - Kerja remote
   - **Kantor**: Pilih kantor (hanya untuk WFO)
   - **Catatan**: Tambahkan catatan jika diperlukan
3. Klik **"Simpan Jadwal"**

### **3. Melihat Jadwal**

#### **Tampilan List:**
- Menampilkan jadwal dalam bentuk tabel
- Filter berdasarkan tanggal, karyawan, tipe kerja, status
- Pagination untuk navigasi data

#### **Tampilan Kalender:**
- Klik tombol **"Kalender"** untuk melihat jadwal dalam format kalender
- Navigasi per bulan
- Jadwal ditampilkan per tanggal

### **4. Mengedit Jadwal**

#### **Edit Individual:**
1. Di halaman daftar jadwal, klik tombol **Edit** (ikon pensil)
2. Ubah data yang diperlukan
3. Klik **"Update Jadwal"**

#### **Bulk Edit (Edit Massal):**
1. Centang jadwal yang ingin diedit
2. Klik **"Bulk Edit"**
3. Pilih aksi yang ingin dilakukan:
   - Update Shift
   - Update Tipe Kerja
   - Update Status
   - Update Kantor
4. Klik **"Update"**

### **5. Menghapus Jadwal**

1. Di halaman daftar jadwal, klik tombol **Delete** (ikon tempat sampah)
2. Konfirmasi penghapusan
3. Jadwal akan dihapus

## 🔐 **Hak Akses Berdasarkan Role**

| Role | Lihat Jadwal | Buat Jadwal | Edit Jadwal | Hapus Jadwal |
|------|-------------|-------------|-------------|--------------|
| **Admin** | ✅ Semua | ✅ Ya | ✅ Ya | ✅ Ya |
| **HRD** | ✅ Semua | ✅ Ya | ✅ Ya | ✅ Ya |
| **HR** | ✅ Semua | ✅ Ya | ✅ Ya | ✅ Ya |
| **Manager** | ✅ Tim | ✅ Ya | ✅ Ya | ❌ Tidak |
| **Karyawan** | ✅ Sendiri | ❌ Tidak | ❌ Tidak | ❌ Tidak |

## 📋 **Jenis Shift yang Tersedia**

1. **Shift Pagi**: 08:00 - 17:00
2. **Shift Siang**: 13:00 - 22:00
3. **Shift Malam**: 22:00 - 07:00
4. **Shift Fleksibel**: 09:00 - 18:00

## 🏢 **Kantor yang Tersedia**

1. **Kantor Pusat Jakarta**
2. **Kantor Cabang Bandung**
3. **Kantor Cabang Surabaya**

## ⚠️ **Hal Penting yang Perlu Diperhatikan**

### **Aturan Pembuatan Jadwal:**
- Satu karyawan hanya boleh memiliki satu jadwal per tanggal
- Jadwal tidak dapat dibuat untuk tanggal yang sudah lewat
- Untuk tipe kerja **WFO**, kantor wajib dipilih
- Untuk tipe kerja **WFA**, kantor tidak perlu dipilih

### **Status Jadwal:**
- **Approved**: Jadwal aktif dan berlaku
- **Cancelled**: Jadwal dibatalkan

## 🛠️ **Troubleshooting**

### **Jika Tombol "Tambah Jadwal" Tidak Muncul:**
1. Pastikan Anda login dengan role yang tepat (Admin/HRD/HR/Manager)
2. Hubungi administrator untuk mengecek permission

### **Jika Mendapat Error 404:**
1. Pastikan URL benar: `/schedules` atau `/schedules/create`
2. Cek permission user dengan mengakses: `/test/schedule-final`
3. Hubungi administrator jika masalah berlanjut

### **Jika Form Kosong (Tidak Ada Data Shift/Kantor):**
1. Hubungi administrator untuk menjalankan seeder data
2. Pastikan ada data shift dan kantor yang aktif

## 📞 **Bantuan Teknis**

Jika mengalami masalah, Anda dapat:

1. **Test Sistem**: Akses `/test/schedule-final` untuk cek status sistem
2. **Hubungi Administrator**: Untuk bantuan permission dan data
3. **Cek Dokumentasi**: Baca file `SCHEDULE_CREATION_FIX.md` untuk detail teknis

---

## 🎯 **Kesimpulan**

Fitur jadwal kerja sekarang berfungsi dengan baik dan dapat digunakan sesuai dengan hak akses masing-masing role. Masalah 404 yang sebelumnya terjadi telah berhasil diperbaiki.

**Selamat menggunakan fitur jadwal kerja!** 🎉

---

*Panduan ini dibuat pada: 15 Juni 2025*  
*Versi sistem: Production Ready*
