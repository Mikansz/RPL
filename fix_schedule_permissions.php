<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

echo "🔧 Fixing Schedule Permissions\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Create schedule permissions if they don't exist
    $permissions = [
        'schedules.view' => 'View Schedules',
        'schedules.create' => 'Create Schedules', 
        'schedules.edit' => 'Edit Schedules',
        'schedules.delete' => 'Delete Schedules',
        'schedules.approve' => 'Approve Schedules'
    ];
    
    echo "📝 Creating/Checking Permissions:\n";
    foreach ($permissions as $name => $displayName) {
        $permission = Permission::firstOrCreate([
            'name' => $name
        ], [
            'display_name' => $displayName,
            'module' => 'schedules',
            'description' => 'Permission to ' . strtolower($displayName)
        ]);
        
        echo "✅ {$name}\n";
    }
    
    // Assign permissions to roles
    echo "\n👥 Assigning Permissions to Roles:\n";
    $roleNames = ['Admin', 'HRD', 'HR', 'Manager'];
    
    foreach ($roleNames as $roleName) {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $permissionObjects = Permission::whereIn('name', array_keys($permissions))->get();
            $role->permissions()->syncWithoutDetaching($permissionObjects);
            echo "✅ {$roleName}\n";
        } else {
            echo "⚠️  {$roleName} role not found\n";
        }
    }
    
    // Check if there are users without roles and assign HRD role
    echo "\n👤 Checking Users Without Roles:\n";
    $usersWithoutRoles = User::whereDoesntHave('roles')->get();
    
    if ($usersWithoutRoles->count() > 0) {
        $hrdRole = Role::where('name', 'HRD')->first();
        if ($hrdRole) {
            foreach ($usersWithoutRoles as $user) {
                $user->roles()->attach($hrdRole->id, [
                    'assigned_at' => now(),
                    'is_active' => true
                ]);
                echo "✅ Assigned HRD role to: {$user->full_name}\n";
            }
        }
    } else {
        echo "✅ All users have roles assigned\n";
    }
    
    echo "\n🎉 Schedule permissions fixed successfully!\n";
    echo "You can now access /schedules/create\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
