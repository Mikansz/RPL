<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Checking User Permissions for Department Management\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Get all users
$users = User::with(['roles.permissions', 'employee'])->get();

echo "📋 All Users and Their Department Permissions:\n";
echo "-" . str_repeat("-", 60) . "\n";

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->toArray();
    $hasViewPerm = $user->hasPermission('departments.view');
    $hasCreatePerm = $user->hasPermission('departments.create');
    $hasEditPerm = $user->hasPermission('departments.edit');
    $hasDeletePerm = $user->hasPermission('departments.delete');
    
    echo "👤 User: {$user->full_name} ({$user->username})\n";
    echo "   Employee ID: {$user->employee_id}\n";
    echo "   Status: {$user->status}\n";
    echo "   Roles: " . (empty($roles) ? 'No roles assigned' : implode(', ', $roles)) . "\n";
    echo "   Department Permissions:\n";
    echo "     - View: " . ($hasViewPerm ? '✅ Yes' : '❌ No') . "\n";
    echo "     - Create: " . ($hasCreatePerm ? '✅ Yes' : '❌ No') . "\n";
    echo "     - Edit: " . ($hasEditPerm ? '✅ Yes' : '❌ No') . "\n";
    echo "     - Delete: " . ($hasDeletePerm ? '✅ Yes' : '❌ No') . "\n";
    echo "\n";
}

echo "\n🔑 Available Roles and Their Department Permissions:\n";
echo "-" . str_repeat("-", 60) . "\n";

$roles = Role::with('permissions')->get();
foreach ($roles as $role) {
    $deptPermissions = $role->permissions->where('module', 'departments')->pluck('name')->toArray();
    
    echo "🏷️  Role: {$role->display_name} ({$role->name})\n";
    echo "   Status: " . ($role->is_active ? 'Active' : 'Inactive') . "\n";
    echo "   Department Permissions: " . (empty($deptPermissions) ? 'None' : implode(', ', $deptPermissions)) . "\n";
    echo "\n";
}

echo "\n📊 Department Permission Summary:\n";
echo "-" . str_repeat("-", 60) . "\n";

$deptPermissions = Permission::where('module', 'departments')->get();
foreach ($deptPermissions as $permission) {
    $rolesWithPerm = Role::whereHas('permissions', function($query) use ($permission) {
        $query->where('permissions.id', $permission->id);
    })->pluck('name')->toArray();
    
    echo "🔐 {$permission->display_name} ({$permission->name})\n";
    echo "   Assigned to roles: " . (empty($rolesWithPerm) ? 'None' : implode(', ', $rolesWithPerm)) . "\n";
    echo "\n";
}

echo "\n💡 Troubleshooting Tips:\n";
echo "-" . str_repeat("-", 60) . "\n";
echo "1. Make sure your user has an active role assigned\n";
echo "2. Check if your role has 'departments.create' permission\n";
echo "3. Verify your user status is 'active'\n";
echo "4. Admin and CEO roles should have all department permissions\n";
echo "5. If you're an admin but don't have permissions, run the department permission seeder\n";

echo "\n🔧 Quick Fix Commands:\n";
echo "-" . str_repeat("-", 60) . "\n";
echo "To run department permission seeder:\n";
echo "php artisan db:seed --class=DepartmentPermissionSeeder\n\n";
echo "To assign admin role to a user:\n";
echo "php artisan tinker\n";
echo "\$user = User::where('username', 'your_username')->first();\n";
echo "\$adminRole = Role::where('name', 'admin')->first();\n";
echo "\$user->roles()->attach(\$adminRole->id, ['assigned_at' => now(), 'is_active' => true]);\n";

echo "\n" . str_repeat("=", 70) . "\n";
