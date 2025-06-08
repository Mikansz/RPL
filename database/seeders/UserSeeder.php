<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'employee_id' => 'EMP001',
                'username' => 'ceo.stea',
                'email' => 'ceo@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'phone' => '081234567890',
                'gender' => 'male',
                'birth_date' => '1975-05-15',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'status' => 'active',
                'role' => 'ceo',
            ],
            [
                'employee_id' => 'EMP002',
                'username' => 'cfo.stea',
                'email' => 'cfo@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Sari',
                'last_name' => 'Wijaya',
                'phone' => '081234567891',
                'gender' => 'female',
                'birth_date' => '1980-08-20',
                'address' => 'Jl. Thamrin No. 456, Jakarta Pusat',
                'status' => 'active',
                'role' => 'cfo',
            ],
            [
                'employee_id' => 'EMP003',
                'username' => 'hrd.stea',
                'email' => 'hrd@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Andi',
                'last_name' => 'Pratama',
                'phone' => '081234567892',
                'gender' => 'male',
                'birth_date' => '1985-03-10',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta Selatan',
                'status' => 'active',
                'role' => 'hrd',
            ],
            [
                'employee_id' => 'EMP004',
                'username' => 'personalia.stea',
                'email' => 'personalia@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Maya',
                'last_name' => 'Sari',
                'phone' => '081234567893',
                'gender' => 'female',
                'birth_date' => '1990-12-05',
                'address' => 'Jl. Kuningan No. 321, Jakarta Selatan',
                'status' => 'active',
                'role' => 'personalia',
            ],
            [
                'employee_id' => 'EMP005',
                'username' => 'john.doe',
                'email' => 'john.doe@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '081234567894',
                'gender' => 'male',
                'birth_date' => '1992-07-18',
                'address' => 'Jl. Kemang No. 654, Jakarta Selatan',
                'status' => 'active',
                'role' => 'karyawan',
            ],
            [
                'employee_id' => 'EMP006',
                'username' => 'jane.smith',
                'email' => 'jane.smith@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '081234567895',
                'gender' => 'female',
                'birth_date' => '1988-11-25',
                'address' => 'Jl. Pondok Indah No. 987, Jakarta Selatan',
                'status' => 'active',
                'role' => 'karyawan',
            ],
            [
                'employee_id' => 'EMP007',
                'username' => 'ahmad.rizki',
                'email' => 'ahmad.rizki@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Ahmad',
                'last_name' => 'Rizki',
                'phone' => '081234567896',
                'gender' => 'male',
                'birth_date' => '1991-04-12',
                'address' => 'Jl. Senayan No. 147, Jakarta Pusat',
                'status' => 'active',
                'role' => 'karyawan',
            ],
            [
                'employee_id' => 'EMP008',
                'username' => 'lisa.amanda',
                'email' => 'lisa.amanda@stea.co.id',
                'password' => Hash::make('password123'),
                'first_name' => 'Lisa',
                'last_name' => 'Amanda',
                'phone' => '081234567897',
                'gender' => 'female',
                'birth_date' => '1993-09-30',
                'address' => 'Jl. Menteng No. 258, Jakarta Pusat',
                'status' => 'active',
                'role' => 'karyawan',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            unset($userData['role']);
            
            $user = User::create($userData);
            $user->roles()->attach($role->id, [
                'assigned_at' => now(),
                'is_active' => true,
            ]);
        }
    }
}
