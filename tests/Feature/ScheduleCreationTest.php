<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Office;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic roles and permissions
        $this->createRolesAndPermissions();
        
        // Create test data
        $this->createTestData();
    }

    private function createRolesAndPermissions()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator']);
        $hrRole = Role::create(['name' => 'hrd', 'display_name' => 'HRD']);
        $karyawanRole = Role::create(['name' => 'karyawan', 'display_name' => 'Karyawan']);

        // Create permissions
        $permissions = [
            'schedules.view' => 'View Schedules',
            'schedules.create' => 'Create Schedules',
            'schedules.edit' => 'Edit Schedules',
            'schedules.delete' => 'Delete Schedules',
        ];

        foreach ($permissions as $name => $description) {
            $permission = Permission::create([
                'name' => $name,
                'display_name' => $description,
                'module' => 'schedules',
                'description' => $description
            ]);

            // Assign all permissions to admin and hrd
            $adminRole->permissions()->attach($permission);
            $hrRole->permissions()->attach($permission);
            
            // Only view permission to karyawan
            if ($name === 'schedules.view') {
                $karyawanRole->permissions()->attach($permission);
            }
        }
    }

    private function createTestData()
    {
        // Create office
        Office::create([
            'name' => 'Main Office',
            'address' => 'Test Address',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
            'is_active' => true
        ]);

        // Create shift
        Shift::create([
            'name' => 'Regular Shift',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true
        ]);
    }

    public function test_admin_can_access_schedule_creation_page()
    {
        $admin = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole);

        $response = $this->actingAs($admin)->get('/schedules/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('schedules.create');
    }

    public function test_hrd_can_access_schedule_creation_page()
    {
        $hrd = User::factory()->create();
        $hrdRole = Role::where('name', 'hrd')->first();
        $hrd->roles()->attach($hrdRole);

        $response = $this->actingAs($hrd)->get('/schedules/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('schedules.create');
    }

    public function test_karyawan_cannot_access_schedule_creation_page()
    {
        $karyawan = User::factory()->create();
        $karyawanRole = Role::where('name', 'karyawan')->first();
        $karyawan->roles()->attach($karyawanRole);

        $response = $this->actingAs($karyawan)->get('/schedules/create');
        
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/schedules/create');
        
        $response->assertRedirect('/login');
    }

    public function test_schedule_creation_button_visible_for_authorized_users()
    {
        $admin = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole);

        $response = $this->actingAs($admin)->get('/schedules');
        
        $response->assertStatus(200);
        $response->assertSee('Tambah Jadwal');
        $response->assertSee(route('schedules.create'));
    }

    public function test_schedule_creation_button_hidden_for_unauthorized_users()
    {
        $karyawan = User::factory()->create();
        $karyawanRole = Role::where('name', 'karyawan')->first();
        $karyawan->roles()->attach($karyawanRole);

        $response = $this->actingAs($karyawan)->get('/schedules');
        
        $response->assertStatus(200);
        $response->assertDontSee('Tambah Jadwal');
    }
}
