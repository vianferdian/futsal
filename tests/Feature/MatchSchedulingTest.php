<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\MatchStatus;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use App\Models\FutsalMatch;
use App\Models\MatchAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected Competition $competition;
    protected Team $homeTeam;
    protected Team $awayTeam;
    protected Venue $venue;

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

        $this->homeTeam = Team::create([
            'competition_id' => $this->competition->id,
            'name' => 'Bintang Timur',
            'short_name' => 'BTS',
            'city' => 'Surabaya',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'status' => 'active',
        ]);

        $this->awayTeam = Team::create([
            'competition_id' => $this->competition->id,
            'name' => 'Black Steel',
            'short_name' => 'BSP',
            'city' => 'Papua',
            'primary_color' => '#ff0000',
            'secondary_color' => '#000000',
            'status' => 'active',
        ]);

        $this->venue = Venue::create([
            'name' => 'GOR UNY',
            'city' => 'Yogyakarta',
            'address' => 'Jl. Colombo No. 1',
            'capacity' => 4000,
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_manage_matches(): void
    {
        $this->actingAs($this->supervisorUser)
            ->get(route('admin.matches.index'))
            ->assertStatus(403);
    }

    public function test_admin_can_schedule_match(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.matches.store'), [
                'competition_id' => $this->competition->id,
                'round' => 'Penyisihan Grup',
                'group_name' => 'Grup A',
                'home_team_id' => $this->homeTeam->id,
                'away_team_id' => $this->awayTeam->id,
                'venue_id' => $this->venue->id,
                'match_date' => '2026-09-01',
                'kickoff_time' => '14:00',
                'status' => MatchStatus::WAITING_LINEUP->value,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matches', [
            'competition_id' => $this->competition->id,
            'home_team_id' => $this->homeTeam->id,
            'away_team_id' => $this->awayTeam->id,
            'venue_id' => $this->venue->id,
            'match_date' => '2026-09-01 00:00:00',
            'kickoff_time' => '14:00',
        ]);
    }

    public function test_cannot_schedule_with_same_home_and_away_team(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.matches.store'), [
                'competition_id' => $this->competition->id,
                'round' => 'Penyisihan Grup',
                'home_team_id' => $this->homeTeam->id,
                'away_team_id' => $this->homeTeam->id, // SAME!
                'venue_id' => $this->venue->id,
                'match_date' => '2026-09-01',
                'kickoff_time' => '14:00',
                'status' => MatchStatus::WAITING_LINEUP->value,
            ]);

        $response->assertInvalid(['away_team_id']);
    }

    public function test_cannot_schedule_with_venue_time_clash(): void
    {
        // 1. Create original match
        FutsalMatch::create([
            'competition_id' => $this->competition->id,
            'match_number' => 'M-001',
            'round' => 'Penyisihan Grup',
            'home_team_id' => $this->homeTeam->id,
            'away_team_id' => $this->awayTeam->id,
            'venue_id' => $this->venue->id,
            'match_date' => '2026-09-01',
            'kickoff_time' => '14:00',
            'status' => MatchStatus::WAITING_LINEUP,
        ]);

        // 2. Try creating another match on the SAME venue, date and time
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.matches.store'), [
                'competition_id' => $this->competition->id,
                'round' => 'Penyisihan Grup',
                'home_team_id' => $this->homeTeam->id,
                'away_team_id' => $this->awayTeam->id,
                'venue_id' => $this->venue->id,
                'match_date' => '2026-09-01',
                'kickoff_time' => '14:00',
                'status' => MatchStatus::WAITING_LINEUP->value,
            ]);

        $response->assertInvalid(['kickoff_time']);
    }

    public function test_admin_can_assign_and_unassign_supervisor(): void
    {
        $match = FutsalMatch::create([
            'competition_id' => $this->competition->id,
            'match_number' => 'M-001',
            'round' => 'Penyisihan Grup',
            'home_team_id' => $this->homeTeam->id,
            'away_team_id' => $this->awayTeam->id,
            'venue_id' => $this->venue->id,
            'match_date' => '2026-09-01',
            'kickoff_time' => '14:00',
            'status' => MatchStatus::WAITING_LINEUP,
        ]);

        // 1. Assign
        $this->actingAs($this->adminUser)
            ->post(route('admin.matches.assign-supervisor', $match->id), [
                'user_id' => $this->supervisorUser->id,
            ])
            ->assertRedirect(route('admin.matches.show', $match->id));

        $this->assertDatabaseHas('match_assignments', [
            'match_id' => $match->id,
            'user_id' => $this->supervisorUser->id,
        ]);

        // Verify dashboard list for supervisor
        $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.dashboard'))
            ->assertStatus(200)
            ->assertSee($match->match_number);

        // 2. Unassign
        $this->actingAs($this->adminUser)
            ->delete(route('admin.matches.unassign-supervisor', [$match->id, $this->supervisorUser->id]))
            ->assertRedirect(route('admin.matches.show', $match->id));

        $this->assertDatabaseMissing('match_assignments', [
            'match_id' => $match->id,
            'user_id' => $this->supervisorUser->id,
        ]);
    }
}
