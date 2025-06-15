<?php

echo "🔍 Simple Department Issue Diagnosis\n";
echo "=" . str_repeat("=", 40) . "\n\n";

// Check basic PHP and file structure
echo "1️⃣ Basic Environment Check:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Current Directory: " . getcwd() . "\n";
echo "   Laravel App Directory: " . (is_dir('app') ? '✅ Exists' : '❌ Missing') . "\n";
echo "   Vendor Directory: " . (is_dir('vendor') ? '✅ Exists' : '❌ Missing') . "\n";
echo "   .env File: " . (file_exists('.env') ? '✅ Exists' : '❌ Missing') . "\n\n";

// Check database connection
echo "2️⃣ Database Connection Check:\n";
try {
    $env = file_get_contents('.env');
    preg_match('/DB_HOST=(.*)/', $env, $host);
    preg_match('/DB_DATABASE=(.*)/', $env, $database);
    preg_match('/DB_USERNAME=(.*)/', $env, $username);
    preg_match('/DB_PASSWORD=(.*)/', $env, $password);
    
    $host = isset($host[1]) ? trim($host[1]) : '127.0.0.1';
    $database = isset($database[1]) ? trim($database[1]) : '';
    $username = isset($username[1]) ? trim($username[1]) : 'root';
    $password = isset($password[1]) ? trim($password[1]) : '';
    
    echo "   Host: $host\n";
    echo "   Database: $database\n";
    echo "   Username: $username\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    echo "   Connection: ✅ Success\n";
    
    // Check if tables exist
    $tables = ['users', 'roles', 'permissions', 'departments'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "   Table '$table': " . ($exists ? '✅ Exists' : '❌ Missing') . "\n";
    }
    
} catch (Exception $e) {
    echo "   Connection: ❌ Failed - " . $e->getMessage() . "\n";
}

echo "\n3️⃣ File Permissions Check:\n";
$directories = ['storage', 'bootstrap/cache', 'storage/logs', 'storage/app', 'storage/framework'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "   $dir: " . ($writable ? '✅ Writable' : '❌ Not writable') . "\n";
    } else {
        echo "   $dir: ❌ Missing\n";
    }
}

echo "\n4️⃣ Key Laravel Files Check:\n";
$files = [
    'app/Http/Controllers/DepartmentController.php',
    'app/Models/Department.php',
    'app/Models/User.php',
    'app/Models/Role.php',
    'app/Models/Permission.php',
    'resources/views/departments/create.blade.php',
    'routes/web.php'
];

foreach ($files as $file) {
    echo "   $file: " . (file_exists($file) ? '✅ Exists' : '❌ Missing') . "\n";
}

echo "\n💡 Recommendations:\n";
echo "=" . str_repeat("=", 40) . "\n";

if (!is_dir('vendor')) {
    echo "❌ Run: composer install\n";
}

if (!file_exists('.env')) {
    echo "❌ Copy .env.example to .env and configure database\n";
}

echo "✅ Try these commands in order:\n";
echo "   1. composer install\n";
echo "   2. php artisan key:generate\n";
echo "   3. php artisan migrate\n";
echo "   4. php artisan db:seed\n";
echo "   5. php artisan serve\n";

echo "\n📋 Manual Department Permission Fix:\n";
echo "If Laravel commands don't work, you can manually run SQL:\n\n";

echo "-- Insert department permissions\n";
echo "INSERT IGNORE INTO permissions (name, display_name, module, description, created_at, updated_at) VALUES\n";
echo "('departments.view', 'View Departments', 'departments', 'Permission to view departments', NOW(), NOW()),\n";
echo "('departments.create', 'Create Departments', 'departments', 'Permission to create departments', NOW(), NOW()),\n";
echo "('departments.edit', 'Edit Departments', 'departments', 'Permission to edit departments', NOW(), NOW()),\n";
echo "('departments.delete', 'Delete Departments', 'departments', 'Permission to delete departments', NOW(), NOW());\n\n";

echo "-- Assign permissions to admin role (assuming role ID 1 is admin)\n";
echo "INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at, updated_at)\n";
echo "SELECT 1, id, NOW(), NOW() FROM permissions WHERE module = 'departments';\n\n";

echo "-- Assign admin role to user (replace 1 with your user ID)\n";
echo "INSERT IGNORE INTO user_roles (user_id, role_id, assigned_at, is_active, created_at, updated_at)\n";
echo "VALUES (1, 1, NOW(), 1, NOW(), NOW());\n\n";

echo str_repeat("=", 50) . "\n";
