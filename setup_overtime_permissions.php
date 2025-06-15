<?php

require_once 'vendor/autoload.php';

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 Setting up Overtime Permissions for HRD and Admin...\n\n";

try {
    // Create overtime permissions if they don't exist
    $permissions = [
        'overtime.view' => 'View overtime requests',
        'overtime.create' => 'Create overtime requests',
        'overtime.edit' => 'Edit overtime requests',
        'overtime.delete' => 'Delete overtime requests',
        'overtime.approve' => 'Approve/reject overtime requests',
        'overtime.reports' => 'View overtime reports',
        'overtime.view_all' => 'View all overtime requests',
    ];

    echo "📋 Creating/Updating Permissions:\n";
    foreach ($permissions as $name => $description) {
        $permission = Permission::firstOrCreate(
            ['name' => $name],
            ['description' => $description, 'module' => 'overtime']
        );
        
        $status = $permission->wasRecentlyCreated ? '✅ Created' : '🔄 Updated';
        echo "   {$status}: {$name}\n";
    }

    // Ensure Admin role exists and has all permissions
    echo "\n👑 Setting up Admin Role:\n";
    $adminRole = Role::firstOrCreate(
        ['name' => 'Admin'],
        ['display_name' => 'Administrator', 'description' => 'Full system access', 'is_active' => true]
    );

    $allPermissions = Permission::whereIn('name', array_keys($permissions))->get();
    $adminRole->permissions()->syncWithoutDetaching($allPermissions->pluck('id'));
    echo "   ✅ Admin role granted all overtime permissions\n";

    // Ensure HRD role exists and has approval permissions
    echo "\n👥 Setting up HRD Role:\n";
    $hrdRole = Role::firstOrCreate(
        ['name' => 'HRD'],
        ['display_name' => 'Human Resources Department', 'description' => 'HR management access', 'is_active' => true]
    );

    $hrdPermissions = Permission::whereIn('name', [
        'overtime.view', 'overtime.view_all', 'overtime.approve', 'overtime.reports'
    ])->get();
    $hrdRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
    echo "   ✅ HRD role granted overtime approval permissions\n";

    // Also check for HR role (alternative naming)
    $hrRole = Role::where('name', 'HR')->first();
    if ($hrRole) {
        $hrRole->permissions()->syncWithoutDetaching($hrdPermissions->pluck('id'));
        echo "   ✅ HR role granted overtime approval permissions\n";
    }

    // Ensure Manager role has approval permissions
    echo "\n👔 Setting up Manager Role:\n";
    $managerRole = Role::firstOrCreate(
        ['name' => 'Manager'],
        ['display_name' => 'Manager', 'description' => 'Department management access', 'is_active' => true]
    );

    $managerPermissions = Permission::whereIn('name', [
        'overtime.view', 'overtime.approve'
    ])->get();
    $managerRole->permissions()->syncWithoutDetaching($managerPermissions->pluck('id'));
    echo "   ✅ Manager role granted overtime approval permissions\n";

    // Ensure Employee role has basic permissions
    echo "\n👤 Setting up Employee Role:\n";
    $employeeRole = Role::firstOrCreate(
        ['name' => 'Employee'],
        ['display_name' => 'Employee', 'description' => 'Basic employee access', 'is_active' => true]
    );

    $employeePermissions = Permission::whereIn('name', [
        'overtime.view', 'overtime.create', 'overtime.edit'
    ])->get();
    $employeeRole->permissions()->syncWithoutDetaching($employeePermissions->pluck('id'));
    echo "   ✅ Employee role granted basic overtime permissions\n";

    // Show current users with approval permissions
    echo "\n📊 Users with Overtime Approval Access:\n";
    $approvalUsers = User::whereHas('roles', function($query) {
        $query->whereIn('name', ['Admin', 'HRD', 'HR', 'Manager']);
    })->with('roles')->get();

    if ($approvalUsers->count() > 0) {
        foreach ($approvalUsers as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            echo "   👤 {$user->full_name} ({$user->username}) - Roles: {$roles}\n";
        }
    } else {
        echo "   ⚠️  No users found with approval roles\n";
    }

    echo "\n✅ Overtime permissions setup completed successfully!\n";
    echo "\n📝 Next steps:\n";
    echo "   1. Assign Admin/HRD/Manager roles to appropriate users\n";
    echo "   2. Access pending overtime requests at: /permits/overtime/pending\n";
    echo "   3. Test approval functionality\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
