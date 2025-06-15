<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;

class CFOPayrollApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $cfoUser;
    protected $payroll;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary roles and permissions
        $this->createRolesAndPermissions();
        
        // Create CFO user
        $this->cfoUser = $this->createCFOUser();
        
        // Create test payroll
        $this->payroll = $this->createTestPayroll();
    }

    private function createRolesAndPermissions()
    {
        // Create CFO role
        $cfoRole = Role::create([
            'name' => 'cfo',
            'display_name' => 'Chief Financial Officer',
            'description' => 'CFO role with payroll approval permissions',
            'is_active' => true,
        ]);

        // Create payroll.approve permission
        $approvePermission = Permission::create([
            'name' => 'payroll.approve',
            'display_name' => 'Approve Payroll',
            'module' => 'payroll',
            'description' => 'Permission to approve payroll'
        ]);

        // Create payroll.view_all permission
        $viewPermission = Permission::create([
            'name' => 'payroll.view_all',
            'display_name' => 'View All Payroll',
            'module' => 'payroll',
            'description' => 'Permission to view all payroll'
        ]);

        // Assign permissions to CFO role
        $cfoRole->permissions()->attach([$approvePermission->id, $viewPermission->id]);
    }

    private function createCFOUser()
    {
        $user = User::create([
            'employee_id' => 'CFO001',
            'username' => 'cfo_test',
            'email' => 'cfo.test@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Test',
            'last_name' => 'CFO',
            'status' => 'active',
        ]);

        // Assign CFO role
        $cfoRole = Role::where('name', 'cfo')->first();
        $user->roles()->attach($cfoRole->id, [
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return $user;
    }

    private function createTestPayroll()
    {
        // Create a test employee
        $employee = User::create([
            'employee_id' => 'EMP001',
            'username' => 'employee_test',
            'email' => 'employee.test@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'status' => 'active',
        ]);

        // Create payroll period
        $period = PayrollPeriod::create([
            'name' => 'Test Period',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'calculated',
            'created_by' => $this->cfoUser->id,
        ]);

        // Create payroll
        return Payroll::create([
            'payroll_period_id' => $period->id,
            'user_id' => $employee->id,
            'basic_salary' => 5000000,
            'total_allowances' => 1000000,
            'total_deductions' => 500000,
            'gross_salary' => 6000000,
            'tax_amount' => 300000,
            'net_salary' => 5700000,
            'status' => 'pending',
        ]);
    }

    public function test_cfo_has_payroll_approve_permission()
    {
        $this->assertTrue($this->cfoUser->hasPermission('payroll.approve'));
    }

    public function test_cfo_can_approve_individual_payroll()
    {
        $this->actingAs($this->cfoUser);

        $response = $this->post(route('payroll.approve', $this->payroll->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Payroll berhasil disetujui.');

        $this->payroll->refresh();
        $this->assertEquals('approved', $this->payroll->status);
        $this->assertEquals($this->cfoUser->id, $this->payroll->approved_by);
        $this->assertNotNull($this->payroll->approved_at);
    }

    public function test_cfo_can_bulk_approve_payrolls()
    {
        // Create another payroll
        $payroll2 = Payroll::create([
            'payroll_period_id' => $this->payroll->payroll_period_id,
            'user_id' => $this->payroll->user_id,
            'basic_salary' => 4000000,
            'total_allowances' => 800000,
            'total_deductions' => 400000,
            'gross_salary' => 4800000,
            'tax_amount' => 240000,
            'net_salary' => 4560000,
            'status' => 'draft',
        ]);

        $this->actingAs($this->cfoUser);

        $response = $this->post(route('payroll.bulk.approve'), [
            'payroll_ids' => [$this->payroll->id, $payroll2->id]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Berhasil menyetujui 2 payroll.');

        $this->payroll->refresh();
        $payroll2->refresh();

        $this->assertEquals('approved', $this->payroll->status);
        $this->assertEquals('approved', $payroll2->status);
        $this->assertEquals($this->cfoUser->id, $this->payroll->approved_by);
        $this->assertEquals($this->cfoUser->id, $payroll2->approved_by);
    }

    public function test_non_cfo_cannot_approve_payroll()
    {
        // Create regular employee user
        $regularUser = User::create([
            'employee_id' => 'REG001',
            'username' => 'regular_user',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Regular',
            'last_name' => 'User',
            'status' => 'active',
        ]);

        $this->actingAs($regularUser);

        $response = $this->post(route('payroll.approve', $this->payroll->id));

        $response->assertStatus(403);
    }
}
