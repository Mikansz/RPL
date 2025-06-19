<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'System Administrator',
                'description' => 'Super admin dengan akses penuh ke semua sistem dan konfigurasi',
                'is_active' => true,
            ],
            [
                'name' => 'ceo',
                'display_name' => 'Chief Executive Officer',
                'description' => 'Akses ke semua laporan dan dashboard monitoring',
                'is_active' => true,
            ],
            [
                'name' => 'cfo',
                'display_name' => 'Chief Financial Officer',
                'description' => 'Akses ke laporan keuangan, approval gaji, dan budget',
                'is_active' => true,
            ],
            [
                'name' => 'hrd',
                'display_name' => 'Human Resource Development',
                'description' => 'Manajemen karyawan, absensi, dan penggajian',
                'is_active' => true,
            ],
            [
                'name' => 'personalia',
                'display_name' => 'Personalia',
                'description' => 'Input data karyawan dan monitoring absensi',
                'is_active' => true,
            ],
            [
                'name' => 'karyawan',
                'display_name' => 'Karyawan',
                'description' => 'Akses terbatas untuk melihat slip gaji dan absensi pribadi',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']], 
                $role
            );
        }
    }
}
