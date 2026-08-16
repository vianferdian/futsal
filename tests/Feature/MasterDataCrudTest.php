<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\PlayerPosition;
use App\Enums\TeamOfficialPosition;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Player;
use App\Models\TeamOfficial;
use App\Models\Venue;
use App\Models\User;
use App\Models\FutsalMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected Competition $competition;
    protected Team $team;

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

        $this->competition = Competition::create([
            'name' => 'Liga Futsal Indonesia',
            'short_name' => 'LFI',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $this->team = Team::create([
            'competition_id' => $this->competition->id,
            'name' => 'Bintang Timur',
            'short_name' => 'BTS',
            'city' => 'Surabaya',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_access_master_data(): void
    {
        $this->actingAs($this->supervisorUser)
            ->get(route('admin.competitions.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_manage_competitions(): void
    {
        // 1. List
        $this->actingAs($this->adminUser)
            ->get(route('admin.competitions.index'))
            ->assertStatus(200)
            ->assertSee($this->competition->name);

        // 2. Create
        $this->actingAs($this->adminUser)
            ->post(route('admin.competitions.store'), [
                'name' => 'Liga Futsal Baru',
                'short_name' => 'LFB',
                'season' => '2027',
                'start_date' => '2027-01-01',
                'end_date' => '2027-06-30',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.competitions.index'));

        $this->assertDatabaseHas('competitions', ['name' => 'Liga Futsal Baru']);
    }

    public function test_admin_can_manage_teams(): void
    {
        // 1. Create
        $this->actingAs($this->adminUser)
            ->post(route('admin.teams.store'), [
                'competition_id' => $this->competition->id,
                'name' => 'Black Steel',
                'short_name' => 'BSP',
                'city' => 'Papua',
                'primary_color' => '#ff0000',
                'secondary_color' => '#000000',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.teams.index'));

        $this->assertDatabaseHas('teams', ['name' => 'Black Steel']);
    }

    public function test_player_shirt_number_unique_validation(): void
    {
        // Create active player with shirt number 10
        Player::create([
            'team_id' => $this->team->id,
            'name' => 'Player Ten',
            'shirt_number' => 10,
            'position' => PlayerPosition::PIVOT->value,
            'status' => 'active',
        ]);

        // 1. Store player with same shirt number 10 inside SAME team (status active) should FAIL
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.players.store'), [
                'team_id' => $this->team->id,
                'name' => 'Another Ten',
                'shirt_number' => 10,
                'position' => PlayerPosition::ALA->value,
                'status' => 'active',
            ]);
        
        $response->assertInvalid(['shirt_number']);

        // 2. Store player with same shirt number 10 inside SAME team but status INACTIVE should PASS
        $responseInactive = $this->actingAs($this->adminUser)
            ->post(route('admin.players.store'), [
                'team_id' => $this->team->id,
                'name' => 'Inactive Ten',
                'shirt_number' => 10,
                'position' => PlayerPosition::ALA->value,
                'status' => 'inactive',
            ]);
        
        $responseInactive->assertRedirect(route('admin.players.index', ['team_id' => $this->team->id]));
        $this->assertDatabaseHas('players', ['name' => 'Inactive Ten', 'status' => 'inactive']);
    }

    public function test_admin_can_manage_officials(): void
    {
        $this->actingAs($this->adminUser)
            ->post(route('admin.officials.store'), [
                'team_id' => $this->team->id,
                'name' => 'Coach Budi',
                'position' => TeamOfficialPosition::HEAD_COACH->value,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.officials.index', ['team_id' => $this->team->id]));

        $this->assertDatabaseHas('team_officials', ['name' => 'Coach Budi']);
    }

    public function test_admin_can_manage_venues(): void
    {
        $this->actingAs($this->adminUser)
            ->post(route('admin.venues.store'), [
                'name' => 'GOR UNY',
                'city' => 'Yogyakarta',
                'address' => 'Jl. Colombo No. 1',
                'capacity' => 4000,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.venues.index'));

        $this->assertDatabaseHas('venues', ['name' => 'GOR UNY']);
    }

    public function test_cannot_delete_competition_with_teams(): void
    {
        // $this->competition already has $this->team in setUp
        $this->actingAs($this->adminUser)
            ->delete(route('admin.competitions.destroy', $this->competition->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('competitions', ['id' => $this->competition->id]);
    }
}
