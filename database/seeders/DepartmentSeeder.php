<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            [
                'code' => 'BOD',
                'name' => 'Board of Directors',
                'description' => 'Dewan Direksi dan Komisaris',
                'is_active' => true,
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance',
                'description' => 'Departemen Keuangan dan Akuntansi',
                'is_active' => true,
            ],
            [
                'code' => 'HR',
                'name' => 'Human Resources',
                'description' => 'Departemen Sumber Daya Manusia',
                'is_active' => true,
            ],
            [
                'code' => 'IT',
                'name' => 'Information Technology',
                'description' => 'Departemen Teknologi Informasi',
                'is_active' => true,
            ],
            [
                'code' => 'OPS',
                'name' => 'Operations',
                'description' => 'Departemen Operasional',
                'is_active' => true,
            ],
            [
                'code' => 'MKT',
                'name' => 'Marketing',
                'description' => 'Departemen Pemasaran',
                'is_active' => true,
            ],
            [
                'code' => 'SALES',
                'name' => 'Sales',
                'description' => 'Departemen Penjualan',
                'is_active' => true,
            ],
            [
                'code' => 'ADM',
                'name' => 'Administration',
                'description' => 'Departemen Administrasi Umum',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
