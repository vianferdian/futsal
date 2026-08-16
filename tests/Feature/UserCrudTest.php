<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use App\Models\Competition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected Team $testTeam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'status' => 'active',
        ]);

        $this->supervisorUser = User::create([
            'name' => 'Supervisor Test',
            'email' => 'spv@test.com',
            'username' => 'spv_test',
            'password' => bcrypt('password'),
            'role' => UserRole::SUPERVISOR,
            'status' => 'active',
        ]);

        $competition = Competition::create([
            'name' => 'Liga Test',
            'short_name' => 'LT',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $this->testTeam = Team::create([
            'competition_id' => $competition->id,
            'name' => 'Tim Test',
            'short_name' => 'TT',
            'city' => 'Jakarta',
            'primary_color' => '#ffffff',
            'secondary_color' => '#000000',
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $this->actingAs($this->supervisorUser)
            ->get(route('admin.users.supervisors.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_view_user_management_lists(): void
    {
        $this->actingAs($this->adminUser)
            ->get(route('admin.users.supervisors.index'))
            ->assertStatus(200)
            ->assertSee($this->supervisorUser->name);

        $this->actingAs($this->adminUser)
            ->get(route('admin.users.team-admins.index'))
            ->assertStatus(200);

        $this->actingAs($this->adminUser)
            ->get(route('admin.users.admins.index'))
            ->assertStatus(200)
            ->assertSee($this->adminUser->name);
    }

    public function test_admin_can_create_supervisor(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.supervisors.store'), [
                'name' => 'New Supervisor',
                'username' => 'new_spv',
                'email' => 'new_spv@test.com',
                'password' => 'password123',
                'role' => 'supervisor',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.users.supervisors.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'new_spv',
            'role' => 'supervisor',
        ]);
    }

    public function test_admin_can_create_team_admin_with_associated_team(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.users.team-admins.store'), [
                'name' => 'New Team Admin',
                'username' => 'new_ta',
                'email' => 'new_ta@test.com',
                'password' => 'password123',
                'role' => 'team_admin',
                'team_id' => $this->testTeam->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.users.team-admins.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'new_ta',
            'role' => 'team_admin',
            'team_id' => $this->testTeam->id,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.users.supervisors.update', $this->supervisorUser->id), [
                'name' => 'Updated Supervisor Name',
                'username' => 'spv_test', // keeping same
                'email' => 'spv_updated@test.com', // changed
                'role' => 'supervisor',
                'status' => 'inactive', // changed
            ]);

        $response->assertRedirect(route('admin.users.supervisors.index'));
        $this->assertDatabaseHas('users', [
            'id' => $this->supervisorUser->id,
            'name' => 'Updated Supervisor Name',
            'email' => 'spv_updated@test.com',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.users.admins.destroy', $this->adminUser->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.users.supervisors.destroy', $this->supervisorUser->id));

        $response->assertRedirect(route('admin.users.supervisors.index'));
        $this->assertSoftDeleted('users', [
            'id' => $this->supervisorUser->id,
        ]);
    }
}
