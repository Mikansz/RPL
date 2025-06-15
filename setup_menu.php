#!/usr/bin/env php
<?php

// Simple script to setup menu permissions
echo "Setting up menu permissions...\n";

// Check if we're in Laravel directory
if (!file_exists('artisan')) {
    echo "Error: Please run this script from Laravel root directory\n";
    exit(1);
}

// Run the migration
echo "Running migration...\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output . "\n";

// Run the seeder
echo "Running schedule permissions seeder...\n";
$output = shell_exec('php artisan db:seed --class=SchedulePermissionsSeeder --force 2>&1');
echo $output . "\n";

// Clear cache
echo "Clearing cache...\n";
$output = shell_exec('php artisan cache:clear 2>&1');
echo $output . "\n";

$output = shell_exec('php artisan config:clear 2>&1');
echo $output . "\n";

echo "✅ Setup completed!\n";
echo "📝 Please refresh your browser to see the menu changes.\n";
echo "\n";
echo "🔍 Menu items that should now be visible:\n";
echo "   - Jadwal Kerja (Schedule Management)\n";
echo "   - Shift Kerja (Shift Management)\n";
echo "   - Kantor (Office Management)\n";
echo "\n";
echo "💡 If you still don't see the menus, please check:\n";
echo "   1. Your user role and permissions\n";
echo "   2. Clear browser cache\n";
echo "   3. Log out and log back in\n";
