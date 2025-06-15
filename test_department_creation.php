<?php

echo "🧪 Testing Department Creation\n";
echo "=" . str_repeat("=", 40) . "\n\n";

// Test database connection
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
    
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n\n";
    
    // Check if departments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'departments'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Departments table exists\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE departments");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Table structure:\n";
        foreach ($columns as $column) {
            echo "   - {$column['Field']} ({$column['Type']})\n";
        }
        echo "\n";
        
        // Test inserting a department
        echo "🧪 Testing department insertion...\n";
        
        $testCode = 'TEST' . rand(100, 999);
        $testName = 'Test Department ' . rand(100, 999);
        
        $stmt = $pdo->prepare("
            INSERT INTO departments (code, name, description, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([$testCode, $testName, 'Test department for debugging', 1]);
        
        if ($result) {
            $departmentId = $pdo->lastInsertId();
            echo "✅ Test department created successfully (ID: $departmentId)\n";
            
            // Verify the department was created
            $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
            $stmt->execute([$departmentId]);
            $department = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($department) {
                echo "✅ Department verified in database:\n";
                echo "   Code: {$department['code']}\n";
                echo "   Name: {$department['name']}\n";
                echo "   Active: " . ($department['is_active'] ? 'Yes' : 'No') . "\n";
                
                // Clean up - delete test department
                $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
                $stmt->execute([$departmentId]);
                echo "🧹 Test department cleaned up\n\n";
            }
        } else {
            echo "❌ Failed to create test department\n";
        }
        
        // Check existing departments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "📊 Current departments in database: $count\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT code, name, is_active FROM departments ORDER BY created_at DESC LIMIT 5");
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "📋 Recent departments:\n";
            foreach ($departments as $dept) {
                $status = $dept['is_active'] ? '✅' : '❌';
                echo "   $status {$dept['code']} - {$dept['name']}\n";
            }
        }
        
    } else {
        echo "❌ Departments table does not exist\n";
        echo "💡 Run: php artisan migrate\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 Troubleshooting Steps:\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Check Laravel logs: storage/logs/laravel.log\n";
echo "3. Try creating department with simple data\n";
echo "4. Verify CSRF token is being sent\n";
echo "5. Check if form action URL is correct\n";
echo "\n🔧 Debug URLs to test:\n";
echo "- GET /departments (list)\n";
echo "- GET /departments/create (form)\n";
echo "- POST /departments (store)\n";
echo "\n📝 Test this in browser console:\n";
echo "fetch('/departments', {method: 'GET'}).then(r => console.log(r.status))\n";
echo str_repeat("=", 50) . "\n";
