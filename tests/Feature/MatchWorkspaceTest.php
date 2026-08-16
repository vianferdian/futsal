<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\MatchStatus;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use App\Models\FutsalMatch;
use App\Models\MatchLineup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected User $anotherSupervisor;
    protected Competition $competition;
    protected Team $homeTeam;
    protected Team $awayTeam;
    protected Venue $venue;
    protected FutsalMatch $match;

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

        $this->anotherSupervisor = User::create([
            'name' => 'Another Supervisor',
            'email' => 'spv2@test.com',
            'username' => 'spv_test2',
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
            'address' => 'UNY Street',
            'capacity' => 4000,
            'status' => 'active',
        ]);

        $this->match = FutsalMatch::create([
            'competition_id' => $this->competition->id,
            'match_number' => 'M-001',
            'round' => 'Penyisihan',
            'home_team_id' => $this->homeTeam->id,
            'away_team_id' => $this->awayTeam->id,
            'venue_id' => $this->venue->id,
            'match_date' => '2026-09-01',
            'kickoff_time' => '14:00',
            'status' => MatchStatus::WAITING_LINEUP,
        ]);

        // Assign supervisorUser
        $this->match->supervisors()->attach($this->supervisorUser->id, [
            'assignment_type' => 'supervisor',
        ]);
    }

    public function test_non_assigned_supervisor_cannot_access_workspace(): void
    {
        $this->actingAs($this->anotherSupervisor)
            ->get(route('supervisor.matches.workspace', $this->match->id))
            ->assertStatus(403);
    }

    public function test_assigned_supervisor_can_access_workspace(): void
    {
        $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.matches.workspace', $this->match->id))
            ->assertStatus(200)
            ->assertSee($this->match->match_number);
    }

    public function test_cannot_start_match_if_not_ready(): void
    {
        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.start', $this->match->id));

        $response->assertSessionHas('error');
        $this->assertEquals(MatchStatus::WAITING_LINEUP, $this->match->fresh()->status);
    }

    public function test_can_start_match_when_ready(): void
    {
        // 1. Ready the match
        $this->match->update(['status' => MatchStatus::READY]);

        // 2. Start
        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.start', $this->match->id));

        $response->assertRedirect();
        
        $freshMatch = $this->match->fresh();
        $this->assertEquals(MatchStatus::FIRST_HALF, $freshMatch->status);
        $this->assertEquals('first_half', $freshMatch->current_period);
        $this->assertEquals('running', $freshMatch->timer_status);
        $this->assertNotNull($freshMatch->timer_started_at);
        $this->assertNotNull($freshMatch->started_at);
    }

    public function test_can_access_start_list_report_dsp(): void
    {
        $this->actingAs($this->supervisorUser)
            ->get(route('matches.start-list', $this->match->id))
            ->assertStatus(200)
            ->assertSee('Daftar Susunan Pemain');
    }
}
