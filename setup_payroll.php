<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalaryComponent;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Setting up Payroll System\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Check admin user
$admin = User::whereHas('roles', function($q) { 
    $q->where('name', 'admin'); 
})->first();

if (!$admin) {
    echo "❌ Admin user not found. Please run UserSeeder first.\n";
    exit(1);
}

echo "✅ Admin user found: {$admin->full_name}\n";

// 2. Create payroll period
$period = PayrollPeriod::firstOrCreate([
    'name' => 'Gaji Bulan ' . Carbon::now()->format('F Y'),
    'start_date' => Carbon::now()->startOfMonth(),
], [
    'end_date' => Carbon::now()->endOfMonth(),
    'pay_date' => Carbon::now()->endOfMonth()->addDays(5),
    'description' => 'Periode gaji bulan ' . Carbon::now()->format('F Y'),
    'status' => 'draft',
    'created_by' => $admin->id,
]);

echo "✅ Payroll period created: {$period->name}\n";

// 3. Check employees
$employees = Employee::where('employment_status', 'active')->with('user')->get();
echo "✅ Found {$employees->count()} active employees\n";

if ($employees->count() === 0) {
    echo "❌ No active employees found. Please create employees first.\n";
    exit(1);
}

// 4. Update employees with basic salary if not set
$updated = 0;
foreach ($employees as $employee) {
    if (!$employee->basic_salary || $employee->basic_salary == 0) {
        $employee->update(['basic_salary' => 5000000]); // Default 5 juta
        $updated++;
    }
    echo "- {$employee->user->full_name}: Rp " . number_format($employee->basic_salary, 0, ',', '.') . "\n";
}

if ($updated > 0) {
    echo "✅ Updated {$updated} employees with default basic salary\n";
}

// 5. Check salary components
$components = SalaryComponent::where('is_active', true)->count();
echo "✅ Found {$components} active salary components\n";

if ($components === 0) {
    echo "ℹ️  No salary components found. Creating default components...\n";
    
    $defaultComponents = [
        [
            'name' => 'Tunjangan Transport',
            'code' => 'TRANSPORT',
            'type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_amount' => 500000,
            'is_taxable' => false,
            'is_active' => true,
            'sort_order' => 1,
        ],
        [
            'name' => 'Tunjangan Makan',
            'code' => 'MEAL',
            'type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_amount' => 300000,
            'is_taxable' => false,
            'is_active' => true,
            'sort_order' => 2,
        ],
        [
            'name' => 'Potongan BPJS',
            'code' => 'BPJS',
            'type' => 'deduction',
            'calculation_type' => 'percentage',
            'default_amount' => 0,
            'percentage' => 2,
            'is_taxable' => false,
            'is_active' => true,
            'sort_order' => 3,
        ],
    ];
    
    foreach ($defaultComponents as $componentData) {
        try {
            SalaryComponent::firstOrCreate(
                ['code' => $componentData['code']],
                $componentData
            );
            echo "  ✅ Created component: {$componentData['name']}\n";
        } catch (Exception $e) {
            echo "  ⚠️  Component {$componentData['name']} already exists\n";
        }
    }
}

echo "\n🎉 Payroll system setup completed!\n";
echo "\nNext steps:\n";
echo "1. Go to 'Penggajian' menu in the web interface\n";
echo "2. Click 'Kelola Periode' to manage payroll periods\n";
echo "3. Click 'Calculate' on a draft period to process payroll\n";
echo "4. Assign salary components to employees in 'Data Karyawan' > 'Salary'\n";
