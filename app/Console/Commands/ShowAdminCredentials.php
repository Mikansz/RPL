<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ShowAdminCredentials extends Command
{
    protected $signature = 'admin:show';
    protected $description = 'Show admin user credentials';

    public function handle()
    {
        $admin = User::where('username', 'admin')
                    ->with('roles', 'employee.department', 'employee.position')
                    ->first();

        if (!$admin) {
            $this->error('Admin user not found!');
            return 1;
        }

        $this->info('=== ADMIN USER CREDENTIALS ===');
        $this->line('');
        $this->line('Username: ' . $admin->username);
        $this->line('Password: admin123');
        $this->line('Email: ' . $admin->email);
        $this->line('Full Name: ' . $admin->full_name);
        $this->line('Employee ID: ' . $admin->employee_id);
        
        if ($admin->roles->isNotEmpty()) {
            $this->line('Role: ' . $admin->roles->first()->display_name);
        }
        
        if ($admin->employee) {
            $this->line('Department: ' . $admin->employee->department->name);
            $this->line('Position: ' . $admin->employee->position->name);
        }
        
        $this->line('');
        $this->info('You can now login to the system using these credentials.');
        
        return 0;
    }
}
