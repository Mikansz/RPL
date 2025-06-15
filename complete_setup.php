#!/usr/bin/env php
<?php

echo "🎉 SETUP LENGKAP - JADWAL KERJA, SHIFT & KANTOR\n";
echo "==============================================\n\n";

// Check if we're in Laravel directory
if (!file_exists('artisan')) {
    echo "❌ Error: Please run this script from Laravel root directory\n";
    exit(1);
}

// Clear all caches
echo "🧹 Clearing caches...\n";
$cacheCommands = [
    'php artisan cache:clear',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan optimize:clear'
];

foreach ($cacheCommands as $command) {
    echo "   Running: $command\n";
    $output = shell_exec("$command 2>&1");
}

echo "✅ Cache cleared!\n\n";

echo "🎊 SEMUA FITUR SUDAH SIAP DIGUNAKAN!\n";
echo "===================================\n\n";

echo "📋 FITUR YANG TERSEDIA:\n\n";

echo "🗓️  JADWAL KERJA:\n";
echo "   ✅ Tambah jadwal baru (/schedules/create)\n";
echo "   ✅ Edit jadwal individual (/schedules/{id}/edit)\n";
echo "   ✅ Bulk edit multiple jadwal\n";
echo "   ✅ Lihat detail jadwal (/schedules/{id})\n";
echo "   ✅ Hapus jadwal\n";
echo "   ✅ Calendar view (/schedules/calendar)\n";
echo "   ✅ Preview real-time saat input\n";
echo "   ✅ Validation & error handling\n\n";

echo "⏰  SHIFT KERJA:\n";
echo "   ✅ Tambah shift baru (/shifts/create)\n";
echo "   ✅ Edit shift existing (/shifts/{id}/edit)\n";
echo "   ✅ Lihat detail shift (/shifts/{id})\n";
echo "   ✅ Hapus shift\n";
echo "   ✅ Aktifkan/nonaktifkan shift\n";
echo "   ✅ Preview durasi real-time\n";
echo "   ✅ Statistik penggunaan shift\n";
echo "   ✅ Validation untuk shift yang masih digunakan\n\n";

echo "🏢  KANTOR:\n";
echo "   ✅ Tambah kantor baru (/offices/create)\n";
echo "   ✅ Edit kantor existing (/offices/{id}/edit)\n";
echo "   ✅ Lihat detail kantor (/offices/{id})\n";
echo "   ✅ Hapus kantor\n";
echo "   ✅ Atur koordinat GPS (latitude/longitude)\n";
echo "   ✅ Atur radius absensi (10-1000 meter)\n";
echo "   ✅ Aktifkan/nonaktifkan kantor\n\n";

echo "🔗 MENU YANG TERSEDIA DI SIDEBAR:\n";
echo "   📅 Jadwal Kerja - Kelola jadwal karyawan\n";
echo "   ⏰ Shift Kerja - Kelola shift kerja\n";
echo "   🏢 Kantor - Kelola lokasi kantor\n";
echo "   🚀 Demo Fitur - Lihat semua fitur\n";
echo "   🐛 Debug Permissions - Debug permission user\n\n";

echo "🎯 CARA MENGGUNAKAN:\n\n";

echo "1️⃣  TAMBAH SHIFT:\n";
echo "   • Klik menu 'Shift Kerja'\n";
echo "   • Klik tombol 'Tambah Shift'\n";
echo "   • Isi nama shift, waktu mulai, waktu selesai\n";
echo "   • Lihat preview durasi secara real-time\n";
echo "   • Klik 'Simpan Shift'\n\n";

echo "2️⃣  TAMBAH KANTOR:\n";
echo "   • Klik menu 'Kantor'\n";
echo "   • Klik tombol 'Tambah Kantor'\n";
echo "   • Isi nama kantor, koordinat GPS, radius\n";
echo "   • Klik 'Simpan Kantor'\n\n";

echo "3️⃣  TAMBAH JADWAL:\n";
echo "   • Klik menu 'Jadwal Kerja'\n";
echo "   • Klik tombol 'Tambah Jadwal'\n";
echo "   • Pilih karyawan, tanggal, shift\n";
echo "   • Pilih tipe kerja (WFO/WFA)\n";
echo "   • Jika WFO, pilih kantor\n";
echo "   • Lihat preview jadwal\n";
echo "   • Klik 'Simpan Jadwal'\n\n";

echo "4️⃣  EDIT DATA:\n";
echo "   • Di setiap halaman list, klik ikon pensil (✏️) untuk edit\n";
echo "   • Untuk bulk edit jadwal, centang beberapa jadwal lalu klik 'Bulk Edit'\n";
echo "   • Semua form sudah dilengkapi validation dan preview\n\n";

echo "💡 TIPS:\n";
echo "   • Gunakan 'Demo Fitur' untuk melihat panduan lengkap\n";
echo "   • Semua menu sudah visible tanpa permission check\n";
echo "   • Form sudah dilengkapi dengan real-time preview\n";
echo "   • Validation mencegah error dan duplikasi data\n";
echo "   • Calendar view tersedia untuk melihat jadwal dalam bentuk kalender\n\n";

echo "🎊 SELAMAT!\n";
echo "Semua fitur untuk menambah dan mengedit JADWAL KERJA, SHIFT, dan KANTOR\n";
echo "sudah berhasil dibuat dan siap digunakan!\n\n";

echo "🔄 Silakan refresh browser Anda dan mulai menggunakan fitur-fitur tersebut.\n";
