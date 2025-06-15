<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Fixing Department Permissions Issue\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Step 1: Check if department permissions exist
    echo "1️⃣ Checking department permissions...\n";
    
    $departmentPermissions = [
        ['name' => 'departments.view', 'display_name' => 'View Departments', 'module' => 'departments'],
        ['name' => 'departments.create', 'display_name' => 'Create Departments', 'module' => 'departments'],
        ['name' => 'departments.edit', 'display_name' => 'Edit Departments', 'module' => 'departments'],
        ['name' => 'departments.delete', 'display_name' => 'Delete Departments', 'module' => 'departments'],
    ];
    
    foreach ($departmentPermissions as $permData) {
        $permission = Permission::firstOrCreate(
            ['name' => $permData['name']],
            [
                'display_name' => $permData['display_name'],
                'module' => $permData['module'],
                'description' => 'Permission to ' . strtolower($permData['display_name'])
            ]
        );
        echo "   ✅ {$permission->display_name} permission ready\n";
    }
    
    // Step 2: Ensure admin and CEO roles exist and have permissions
    echo "\n2️⃣ Setting up admin roles...\n";
    
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin'],
        [
            'display_name' => 'Administrator',
            'description' => 'Full system access',
            'is_active' => true
        ]
    );
    
    $ceoRole = Role::firstOrCreate(
        ['name' => 'ceo'],
        [
            'display_name' => 'CEO',
            'description' => 'Chief Executive Officer',
            'is_active' => true
        ]
    );
    
    // Get all department permissions
    $deptPermissions = Permission::whereIn('name', [
        'departments.view', 'departments.create', 'departments.edit', 'departments.delete'
    ])->get();
    
    // Assign permissions to admin and CEO roles
    $adminRole->permissions()->syncWithoutDetaching($deptPermissions->pluck('id'));
    $ceoRole->permissions()->syncWithoutDetaching($deptPermissions->pluck('id'));
    
    echo "   ✅ Admin role permissions updated\n";
    echo "   ✅ CEO role permissions updated\n";
    
    // Step 3: Check current users and their permissions
    echo "\n3️⃣ Checking users and their permissions...\n";
    
    $users = User::with('roles')->get();
    
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->toArray();
        $hasCreatePerm = $user->hasPermission('departments.create');
        
        echo "   👤 {$user->full_name} ({$user->username})\n";
        echo "      Roles: " . (empty($roles) ? 'None' : implode(', ', $roles)) . "\n";
        echo "      Can create departments: " . ($hasCreatePerm ? '✅ Yes' : '❌ No') . "\n";
        
        // If user has no roles, assign admin role
        if (empty($roles)) {
            echo "      🔧 Assigning admin role...\n";
            $user->roles()->attach($adminRole->id, [
                'assigned_at' => now(),
                'is_active' => true
            ]);
            echo "      ✅ Admin role assigned\n";
        }
        echo "\n";
    }
    
    echo "4️⃣ Final verification...\n";
    
    // Check if any user can create departments now
    $usersWithCreatePerm = User::whereHas('roles.permissions', function($query) {
        $query->where('name', 'departments.create');
    })->get();
    
    echo "   Users who can create departments: {$usersWithCreatePerm->count()}\n";
    
    foreach ($usersWithCreatePerm as $user) {
        echo "   ✅ {$user->full_name} ({$user->username})\n";
    }
    
    echo "\n🎉 Department permissions fix completed!\n";
    echo "\nNext steps:\n";
    echo "1. Log out and log back in to refresh your session\n";
    echo "2. Try accessing the departments page again\n";
    echo "3. If still having issues, check the browser console for JavaScript errors\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
