<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\MatchStatus;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use App\Models\FutsalMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MatchTimerTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisorUser;
    protected Competition $competition;
    protected Team $homeTeam;
    protected Team $awayTeam;
    protected Venue $venue;
    protected FutsalMatch $match;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time at fixed point
        Carbon::setTestNow(Carbon::parse('2026-08-16 15:00:00'));

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
            'status' => MatchStatus::FIRST_HALF,
            'current_period' => 'first_half',
            'timer_status' => 'running',
            'timer_started_at' => Carbon::now()->subSeconds(30), // 14:59:30
            'elapsed_seconds' => 10,
        ]);

        // Assign supervisorUser
        $this->match->supervisors()->attach($this->supervisorUser->id, [
            'assignment_type' => 'supervisor',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // release freeze
        parent::tearDown();
    }

    public function test_can_pause_running_timer(): void
    {
        // Advance time to 15:00:30 (meaning diff is 60 seconds from timer_started_at 14:59:30)
        Carbon::setTestNow(Carbon::parse('2026-08-16 15:00:30'));

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.pause', $this->match->id));

        $response->assertRedirect();
        
        $freshMatch = $this->match->fresh();
        $this->assertEquals('paused', $freshMatch->timer_status);
        $this->assertNotNull($freshMatch->timer_paused_at);
        // Elapsed should be 10 (base) + 60 (seconds diff) = 70 seconds
        $this->assertEquals(70, $freshMatch->elapsed_seconds);
    }

    public function test_can_resume_paused_timer(): void
    {
        $this->match->update([
            'timer_status' => 'paused',
            'timer_paused_at' => now(),
            'elapsed_seconds' => 40,
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.resume', $this->match->id));

        $response->assertRedirect();

        $freshMatch = $this->match->fresh();
        $this->assertEquals('running', $freshMatch->timer_status);
        $this->assertNull($freshMatch->timer_paused_at);
        $this->assertNotNull($freshMatch->timer_started_at);
        $this->assertEquals(40, $freshMatch->elapsed_seconds);
    }

    public function test_can_end_first_half(): void
    {
        $this->match->update([
            'home_score' => 2,
            'away_score' => 1,
        ]);

        // Advance time to 15:00:30
        Carbon::setTestNow(Carbon::parse('2026-08-16 15:00:30'));

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.end-first-half', $this->match->id));

        $response->assertRedirect();

        $freshMatch = $this->match->fresh();
        $this->assertEquals(MatchStatus::HALFTIME, $freshMatch->status);
        $this->assertEquals('halftime', $freshMatch->current_period);
        $this->assertEquals(2, $freshMatch->home_first_half_score);
        $this->assertEquals(1, $freshMatch->away_first_half_score);
        // Elapsed: 10 base + 60 diff = 70
        $this->assertEquals(70, $freshMatch->elapsed_seconds);
    }

    public function test_can_start_second_half(): void
    {
        $this->match->update([
            'status' => MatchStatus::HALFTIME,
            'current_period' => 'halftime',
            'timer_status' => 'paused',
            'elapsed_seconds' => 1200,
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.start-second-half', $this->match->id));

        $response->assertRedirect();

        $freshMatch = $this->match->fresh();
        $this->assertEquals(MatchStatus::SECOND_HALF, $freshMatch->status);
        $this->assertEquals('second_half', $freshMatch->current_period);
        $this->assertEquals('running', $freshMatch->timer_status);
        // Reset for second half
        $this->assertEquals(0, $freshMatch->elapsed_seconds);
        $this->assertNotNull($freshMatch->timer_started_at);
    }

    public function test_can_finish_match(): void
    {
        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.finish', $this->match->id));

        $response->assertRedirect();

        $freshMatch = $this->match->fresh();
        $this->assertEquals(MatchStatus::FINISHED, $freshMatch->status);
        $this->assertEquals('finished', $freshMatch->current_period);
        $this->assertEquals('finished', $freshMatch->timer_status);
        $this->assertNotNull($freshMatch->finished_at);
    }
}
