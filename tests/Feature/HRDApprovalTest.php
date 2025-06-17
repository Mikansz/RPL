<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\OvertimeRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class HRDApprovalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $hrdUser;
    protected $employeeUser;
    protected $overtimeRequest;
    protected $leaveRequest;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create HRD role and permissions
        $hrdRole = Role::create([
            'name' => 'HRD',
            'display_name' => 'Human Resources Department',
            'description' => 'HR management access',
            'is_active' => true
        ]);

        $employeeRole = Role::create([
            'name' => 'Employee',
            'display_name' => 'Employee',
            'description' => 'Basic employee access',
            'is_active' => true
        ]);

        // Create permissions
        $overtimePermissions = [
            'overtime.view', 'overtime.approve', 'overtime.view_all'
        ];
        
        $leavePermissions = [
            'leave.view', 'leave.approve', 'leave.view_all'
        ];

        foreach (array_merge($overtimePermissions, $leavePermissions) as $permissionName) {
            $permission = Permission::create([
                'name' => $permissionName,
                'display_name' => ucfirst(str_replace('.', ' ', $permissionName)),
                'description' => 'Permission for ' . $permissionName
            ]);
            
            $hrdRole->permissions()->attach($permission->id);
        }

        // Create HRD user
        $this->hrdUser = User::factory()->create([
            'first_name' => 'HRD',
            'last_name' => 'Manager',
            'email' => 'hrd@test.com',
        ]);
        $this->hrdUser->roles()->attach($hrdRole->id);

        // Create employee user
        $this->employeeUser = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'employee@test.com',
        ]);
        $this->employeeUser->roles()->attach($employeeRole->id);

        // Create leave type
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'max_days_per_year' => 12,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        // Create test overtime request
        $this->overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->employeeUser->id,
            'overtime_date' => now()->addDay(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'planned_hours' => 2,
            'work_description' => 'Test overtime work',
            'reason' => 'Urgent project deadline',
            'status' => 'pending',
        ]);

        // Create test leave request
        $this->leaveRequest = LeaveRequest::create([
            'user_id' => $this->employeeUser->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(9),
            'total_days' => 3,
            'reason' => 'Family vacation',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function hrd_can_approve_overtime_requests()
    {
        $this->actingAs($this->hrdUser);

        $response = $this->post(route('permits.overtime.approve', $this->overtimeRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->overtimeRequest->refresh();
        $this->assertEquals('approved', $this->overtimeRequest->status);
        $this->assertEquals($this->hrdUser->id, $this->overtimeRequest->approved_by);
    }

    /** @test */
    public function hrd_can_reject_overtime_requests()
    {
        $this->actingAs($this->hrdUser);

        $response = $this->post(route('permits.overtime.reject', $this->overtimeRequest), [
            'rejection_reason' => 'Not enough justification'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->overtimeRequest->refresh();
        $this->assertEquals('rejected', $this->overtimeRequest->status);
        $this->assertEquals($this->hrdUser->id, $this->overtimeRequest->approved_by);
    }

    /** @test */
    public function hrd_can_approve_leave_requests()
    {
        $this->actingAs($this->hrdUser);

        $response = $this->post(route('permits.leave.approve', $this->leaveRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->leaveRequest->refresh();
        $this->assertEquals('approved', $this->leaveRequest->status);
        $this->assertEquals($this->hrdUser->id, $this->leaveRequest->approved_by);
    }

    /** @test */
    public function hrd_can_reject_leave_requests()
    {
        $this->actingAs($this->hrdUser);

        $response = $this->post(route('permits.leave.reject', $this->leaveRequest), [
            'rejection_reason' => 'Insufficient leave balance'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->leaveRequest->refresh();
        $this->assertEquals('rejected', $this->leaveRequest->status);
        $this->assertEquals($this->hrdUser->id, $this->leaveRequest->approved_by);
    }

    /** @test */
    public function hrd_can_view_pending_overtime_requests()
    {
        $this->actingAs($this->hrdUser);

        $response = $this->get(route('permits.overtime.pending'));

        $response->assertStatus(200);
        $response->assertSee($this->overtimeRequest->work_description);
    }

    /** @test */
    public function hrd_can_view_pending_leave_requests()
    {
        $this->actingAs($this->hrdUser);

        $response = $this->get(route('permits.leave.pending'));

        $response->assertStatus(200);
        $response->assertSee($this->leaveRequest->reason);
    }

    /** @test */
    public function employee_cannot_approve_requests()
    {
        $this->actingAs($this->employeeUser);

        // Test overtime approval
        $response = $this->post(route('permits.overtime.approve', $this->overtimeRequest));
        $response->assertStatus(403);

        // Test leave approval
        $response = $this->post(route('permits.leave.approve', $this->leaveRequest));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_cannot_approve_own_requests()
    {
        $this->actingAs($this->hrdUser);

        // Create request by HRD user
        $hrdOvertimeRequest = OvertimeRequest::create([
            'user_id' => $this->hrdUser->id,
            'overtime_date' => now()->addDay(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'planned_hours' => 2,
            'work_description' => 'HRD overtime work',
            'reason' => 'Administrative tasks',
            'status' => 'pending',
        ]);

        $response = $this->post(route('permits.overtime.approve', $hrdOvertimeRequest));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $hrdOvertimeRequest->refresh();
        $this->assertEquals('pending', $hrdOvertimeRequest->status);
    }
}
