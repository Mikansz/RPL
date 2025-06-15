<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class UserEmailAutoGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test role
        Role::create([
            'name' => 'karyawan',
            'display_name' => 'Karyawan',
            'description' => 'Regular employee role',
            'is_active' => true,
        ]);
    }

    public function test_user_creation_with_auto_generated_email()
    {
        $role = Role::where('name', 'karyawan')->first();
        
        $userData = [
            'employee_id' => 'EMP001',
            'username' => 'john.doe',
            'email' => 'john.doe@rhi.com', // This should be auto-generated from username
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '081234567890',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'address' => 'Test Address',
            'role_id' => $role->id,
        ];

        $response = $this->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User berhasil dibuat.');

        // Verify user was created with correct email format
        $user = User::where('username', 'john.doe')->first();
        $this->assertNotNull($user);
        $this->assertEquals('john.doe@rhi.com', $user->email);
        $this->assertEquals('john.doe', $user->username);
    }

    public function test_user_update_with_username_change_updates_email()
    {
        $role = Role::where('name', 'karyawan')->first();
        
        // Create a user first
        $user = User::create([
            'employee_id' => 'EMP002',
            'username' => 'jane.smith',
            'email' => 'jane.smith@rhi.com',
            'password' => bcrypt('password123'),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '081234567891',
            'gender' => 'female',
            'birth_date' => '1992-05-15',
            'address' => 'Test Address',
            'status' => 'active',
        ]);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Update user with new username
        $updateData = [
            'employee_id' => 'EMP002',
            'username' => 'jane.doe', // Changed username
            'email' => 'jane.doe@rhi.com', // Should be updated to match new username
            'first_name' => 'Jane',
            'last_name' => 'Doe', // Changed last name
            'phone' => '081234567891',
            'gender' => 'female',
            'birth_date' => '1992-05-15',
            'address' => 'Test Address',
            'status' => 'active',
        ];

        $response = $this->put(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User berhasil diperbarui.');

        // Verify user was updated with correct email format
        $user->refresh();
        $this->assertEquals('jane.doe@rhi.com', $user->email);
        $this->assertEquals('jane.doe', $user->username);
    }

    public function test_email_validation_accepts_rhi_domain()
    {
        $role = Role::where('name', 'karyawan')->first();
        
        $userData = [
            'employee_id' => 'EMP003',
            'username' => 'test.user',
            'email' => 'test.user@rhi.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '081234567892',
            'gender' => 'male',
            'birth_date' => '1988-12-25',
            'address' => 'Test Address',
            'role_id' => $role->id,
        ];

        $response = $this->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'test.user',
            'email' => 'test.user@rhi.com'
        ]);
    }

    public function test_username_with_special_characters_generates_clean_email()
    {
        $role = Role::where('name', 'karyawan')->first();
        
        $userData = [
            'employee_id' => 'EMP004',
            'username' => 'user_name',
            'email' => 'user_name@rhi.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'User',
            'last_name' => 'Name',
            'phone' => '081234567893',
            'gender' => 'female',
            'birth_date' => '1995-03-10',
            'address' => 'Test Address',
            'role_id' => $role->id,
        ];

        $response = $this->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'));
        
        $user = User::where('username', 'user_name')->first();
        $this->assertNotNull($user);
        $this->assertEquals('user_name@rhi.com', $user->email);
    }
}
