<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboards(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/supervisor/dashboard')->assertRedirect('/login');
        $this->get('/team/dashboard')->assertRedirect('/login');
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'admin_test',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::create([
            'name' => 'Inactive Spv',
            'email' => 'spv@test.com',
            'username' => 'spv_test',
            'password' => bcrypt('password'),
            'role' => UserRole::SUPERVISOR,
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'login' => 'spv_test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_redirected_to_admin_dashboard(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_supervisor_redirected_to_supervisor_dashboard(): void
    {
        $user = User::create([
            'name' => 'Test Spv',
            'email' => 'spv@test.com',
            'username' => 'spv_test',
            'password' => bcrypt('password'),
            'role' => UserRole::SUPERVISOR,
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'spv_test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/supervisor/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
