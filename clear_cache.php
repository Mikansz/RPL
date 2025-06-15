#!/usr/bin/env php
<?php

echo "🧹 Clearing Laravel cache...\n";

// Check if we're in Laravel directory
if (!file_exists('artisan')) {
    echo "Error: Please run this script from Laravel root directory\n";
    exit(1);
}

// Clear various caches
$commands = [
    'php artisan cache:clear',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan optimize:clear'
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    $output = shell_exec("$command 2>&1");
    echo $output . "\n";
}

echo "✅ Cache cleared successfully!\n";
echo "📝 Changes should now be visible.\n";
echo "\n";
echo "🔗 Available URLs to test:\n";
echo "   - /debug/permissions - Debug permission information\n";
echo "   - /schedules - Schedule management\n";
echo "   - /shifts - Shift management\n";
echo "   - /offices - Office management\n";
echo "\n";
echo "💡 Menu items should now be visible in the sidebar:\n";
echo "   - Jadwal Kerja\n";
echo "   - Shift Kerja\n";
echo "   - Kantor\n";
echo "   - Debug Permissions\n";
