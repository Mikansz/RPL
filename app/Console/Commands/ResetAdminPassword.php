<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {password?}';
    protected $description = 'Reset admin user password';

    public function handle()
    {
        $admin = User::where('username', 'admin')->first();

        if (!$admin) {
            $this->error('Admin user not found!');
            return 1;
        }

        $password = $this->argument('password');
        
        if (!$password) {
            $password = $this->secret('Enter new password for admin');
        }

        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters long!');
            return 1;
        }

        $admin->update([
            'password' => Hash::make($password),
            'force_password_change' => false,
        ]);

        $this->info('Admin password has been reset successfully!');
        $this->line('Username: ' . $admin->username);
        $this->line('New Password: ' . $password);
        
        return 0;
    }
}
