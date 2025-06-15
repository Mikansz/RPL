<?php
// Simple database test script
require_once '../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'flo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Test database connection
    $pdo = $capsule->getConnection()->getPdo();
    echo "<h2>Database Connection: SUCCESS</h2>";
    
    // Check tables
    $tables = ['users', 'offices', 'shifts', 'schedules', 'attendances'];
    
    foreach ($tables as $table) {
        try {
            $count = $capsule->table($table)->count();
            echo "<p><strong>{$table}:</strong> {$count} records</p>";
        } catch (Exception $e) {
            echo "<p><strong>{$table}:</strong> ERROR - {$e->getMessage()}</p>";
        }
    }
    
    // Check today's schedules
    $today = date('Y-m-d');
    $todaySchedules = $capsule->table('schedules')
        ->where('schedule_date', $today)
        ->count();
    echo "<p><strong>Today's Schedules ({$today}):</strong> {$todaySchedules} records</p>";
    
    // Check recent schedules
    $recentSchedules = $capsule->table('schedules')
        ->where('schedule_date', '>=', date('Y-m-d', strtotime('-7 days')))
        ->orderBy('schedule_date', 'desc')
        ->limit(10)
        ->get();
    
    echo "<h3>Recent Schedules:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Date</th><th>Work Type</th><th>Status</th></tr>";
    
    foreach ($recentSchedules as $schedule) {
        echo "<tr>";
        echo "<td>{$schedule->id}</td>";
        echo "<td>{$schedule->user_id}</td>";
        echo "<td>{$schedule->schedule_date}</td>";
        echo "<td>{$schedule->work_type}</td>";
        echo "<td>{$schedule->status}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h2>Database Connection: FAILED</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
