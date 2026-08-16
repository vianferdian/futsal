<?php

namespace Tests\Feature;

use App\Enums\MatchStatus;
use App\Enums\UserRole;
use App\Models\Competition;
use App\Models\FutsalMatch;
use App\Models\MatchEvent;
use App\Models\MatchLineup;
use App\Models\MatchLineupPlayer;
use App\Models\Player;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchEventTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected FutsalMatch $match;
    protected Team $homeTeam;
    protected Team $awayTeam;
    protected Player $homePlayer;
    protected Player $awayPlayer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::create([
            'name'     => 'Supervisor Test',
            'email'    => 'spv@test.com',
            'username' => 'spv_test',
            'password' => bcrypt('password'),
            'role'     => UserRole::SUPERVISOR,
            'status'   => 'active',
        ]);

        $competition = Competition::create([
            'name'       => 'Liga Futsal Test',
            'short_name' => 'LFT',
            'season'     => '2026',
            'start_date' => '2026-08-01',
            'end_date'   => '2026-12-31',
            'status'     => 'active',
        ]);

        $this->homeTeam = Team::create([
            'competition_id'  => $competition->id,
            'name'            => 'Tim Home',
            'short_name'      => 'HME',
            'city'            => 'Jakarta',
            'primary_color'   => '#0000ff',
            'secondary_color' => '#ffffff',
            'status'          => 'active',
        ]);

        $this->awayTeam = Team::create([
            'competition_id'  => $competition->id,
            'name'            => 'Tim Away',
            'short_name'      => 'AWY',
            'city'            => 'Bandung',
            'primary_color'   => '#ff0000',
            'secondary_color' => '#ffffff',
            'status'          => 'active',
        ]);

        $venue = Venue::create([
            'name'     => 'GOR Test',
            'city'     => 'Yogyakarta',
            'address'  => 'Jl. Test',
            'capacity' => 2000,
            'status'   => 'active',
        ]);

        $this->match = FutsalMatch::create([
            'competition_id'  => $competition->id,
            'match_number'    => 'M-001',
            'round'           => 'Penyisihan',
            'home_team_id'    => $this->homeTeam->id,
            'away_team_id'    => $this->awayTeam->id,
            'venue_id'        => $venue->id,
            'match_date'      => '2026-09-01',
            'kickoff_time'    => '14:00',
            'status'          => MatchStatus::FIRST_HALF,
            'current_period'  => 'first_half',
            'timer_status'    => 'running',
            'elapsed_seconds' => 120,
            'home_score'      => 0,
            'away_score'      => 0,
        ]);

        // Assign supervisor
        $this->match->supervisors()->attach($this->supervisor->id, [
            'assignment_type' => 'supervisor',
        ]);

        // Create players
        $this->homePlayer = Player::create([
            'team_id'      => $this->homeTeam->id,
            'name'         => 'Home Player 1',
            'shirt_number' => 1,
            'position'     => 'pivot',
            'status'       => 'active',
        ]);

        $this->awayPlayer = Player::create([
            'team_id'      => $this->awayTeam->id,
            'name'         => 'Away Player 1',
            'shirt_number' => 1,
            'position'     => 'pivot',
            'status'       => 'active',
        ]);

        // Create lineups with players
        $homeLineup = MatchLineup::create([
            'match_id'  => $this->match->id,
            'team_id'   => $this->homeTeam->id,
            'status'    => 'verified',
        ]);
        MatchLineupPlayer::create([
            'match_lineup_id' => $homeLineup->id,
            'player_id'       => $this->homePlayer->id,
            'playing_status'  => 'playing',
            'position'        => 'pivot',
            'is_goalkeeper'   => false,
            'is_captain'      => true,
        ]);

        $awayLineup = MatchLineup::create([
            'match_id'  => $this->match->id,
            'team_id'   => $this->awayTeam->id,
            'status'    => 'verified',
        ]);
        MatchLineupPlayer::create([
            'match_lineup_id' => $awayLineup->id,
            'player_id'       => $this->awayPlayer->id,
            'playing_status'  => 'playing',
            'position'        => 'pivot',
            'is_goalkeeper'   => true,
            'is_captain'      => false,
        ]);
    }

    // ======================================================
    // GOAL TESTS
    // ======================================================

    public function test_normal_goal_increments_home_score(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'goal',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 5,
                'second'     => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->match->refresh();
        $this->assertEquals(1, $this->match->home_score);
        $this->assertEquals(0, $this->match->away_score);
    }

    public function test_normal_goal_increments_away_score(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->awayTeam->id,
                'event_type' => 'goal',
                'player_id'  => $this->awayPlayer->id,
                'minute'     => 7,
                'second'     => 0,
            ]);

        $response->assertRedirect();
        $this->match->refresh();
        $this->assertEquals(0, $this->match->home_score);
        $this->assertEquals(1, $this->match->away_score);
    }

    public function test_own_goal_credits_opponent(): void
    {
        // Home team commits own goal → away score +1
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'own_goal',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 10,
                'second'     => 0,
            ]);

        $response->assertRedirect();
        $this->match->refresh();
        $this->assertEquals(0, $this->match->home_score);
        $this->assertEquals(1, $this->match->away_score);
    }

    public function test_penalty_miss_does_not_change_score(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'penalty_miss',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 12,
                'second'     => 0,
            ]);

        $response->assertRedirect();
        $this->match->refresh();
        // Penalty miss should NOT affect scores
        $this->assertEquals(0, $this->match->home_score);
        $this->assertEquals(0, $this->match->away_score);

        // But event should be recorded
        $this->assertDatabaseHas('match_events', [
            'match_id'   => $this->match->id,
            'event_type' => 'penalty_miss',
        ]);
    }

    public function test_goal_event_persisted_in_database(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'goal',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 8,
                'second'     => 45,
            ]);

        $this->assertDatabaseHas('match_events', [
            'match_id'   => $this->match->id,
            'team_id'    => $this->homeTeam->id,
            'event_type' => 'goal',
            'minute'     => 8,
            'second'     => 45,
            'period'     => 'first_half',
        ]);
    }

    // ======================================================
    // CARD TESTS
    // ======================================================

    public function test_yellow_card_stored_correctly(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.card', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'yellow_card',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 3,
                'second'     => 15,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('match_events', [
            'match_id'   => $this->match->id,
            'event_type' => 'yellow_card',
            'player_id'  => $this->homePlayer->id,
        ]);

        // Cards don't affect score
        $this->match->refresh();
        $this->assertEquals(0, $this->match->home_score);
    }

    public function test_card_for_player_not_in_lineup_rejected(): void
    {
        $outsider = Player::create([
            'team_id'      => $this->homeTeam->id,
            'name'         => 'Not In Lineup',
            'shirt_number' => 99,
            'position'     => 'pivot',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.card', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'yellow_card',
                'player_id'  => $outsider->id,
                'minute'     => 5,
                'second'     => 0,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ======================================================
    // FOUL TESTS
    // ======================================================

    public function test_foul_increments_home_foul_count(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.foul', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('match_events', [
            'match_id'   => $this->match->id,
            'team_id'    => $this->homeTeam->id,
            'event_type' => 'foul',
            'period'     => 'first_half',
        ]);
    }

    public function test_foul_does_not_change_score(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.foul', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        $this->match->refresh();
        $this->assertEquals(0, $this->match->home_score);
        $this->assertEquals(0, $this->match->away_score);
    }

    // ======================================================
    // TIMEOUT TESTS
    // ======================================================

    public function test_timeout_stored_within_quota(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.timeout', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('match_events', [
            'match_id'   => $this->match->id,
            'team_id'    => $this->homeTeam->id,
            'event_type' => 'timeout',
        ]);
    }

    public function test_timeout_blocked_after_quota_exceeded(): void
    {
        // Use up the single allowed timeout
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.timeout', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        // Second timeout in same period should fail
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.timeout', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Only 1 timeout in database
        $this->assertEquals(
            1,
            MatchEvent::where('match_id', $this->match->id)
                ->where('team_id', $this->homeTeam->id)
                ->where('event_type', 'timeout')
                ->count()
        );
    }

    // ======================================================
    // AUTHORIZATION TESTS
    // ======================================================

    public function test_non_supervisor_cannot_record_goal(): void
    {
        $teamAdmin = User::create([
            'name'     => 'Team Admin',
            'email'    => 'ta@test.com',
            'username' => 'ta_test',
            'password' => bcrypt('password'),
            'role'     => UserRole::TEAM_ADMIN,
            'status'   => 'active',
        ]);

        $response = $this->actingAs($teamAdmin)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'goal',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 1,
                'second'     => 0,
            ]);

        // Middleware should reject
        $response->assertStatus(403);
    }

    public function test_event_blocked_when_match_not_active(): void
    {
        // Set match to HALFTIME
        $this->match->update(['status' => MatchStatus::HALFTIME]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.foul', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        $response->assertStatus(422);
    }

    // ======================================================
    // SCORE RECALCULATION ON MULTIPLE GOALS
    // ======================================================

    public function test_multiple_goals_accumulated_correctly(): void
    {
        // Home scores 2 goals
        foreach ([4, 8] as $minute) {
            $this->actingAs($this->supervisor)
                ->post(route('supervisor.matches.events.goal', $this->match->id), [
                    'team_id'    => $this->homeTeam->id,
                    'event_type' => 'goal',
                    'player_id'  => $this->homePlayer->id,
                    'minute'     => $minute,
                    'second'     => 0,
                ]);
        }

        // Away scores 1 goal
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->awayTeam->id,
                'event_type' => 'goal',
                'player_id'  => $this->awayPlayer->id,
                'minute'     => 12,
                'second'     => 0,
            ]);

        $this->match->refresh();
        $this->assertEquals(2, $this->match->home_score);
        $this->assertEquals(1, $this->match->away_score);
    }

    // ======================================================
    // UNDO EVENT TESTS
    // ======================================================

    public function test_undo_goal_removes_event_and_recalculates_score(): void
    {
        // Record a goal
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.goal', $this->match->id), [
                'team_id'    => $this->homeTeam->id,
                'event_type' => 'goal',
                'player_id'  => $this->homePlayer->id,
                'minute'     => 5,
                'second'     => 0,
            ]);

        $this->match->refresh();
        $this->assertEquals(1, $this->match->home_score);

        $event = MatchEvent::where('match_id', $this->match->id)->latest()->first();

        // Undo the goal
        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.matches.events.destroy', [$this->match->id, $event->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Score should be recalculated back to 0
        $this->match->refresh();
        $this->assertEquals(0, $this->match->home_score);

        // Event should be soft-deleted
        $this->assertSoftDeleted('match_events', ['id' => $event->id]);
    }

    public function test_undo_non_goal_does_not_change_score(): void
    {
        // Record a foul
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.events.foul', $this->match->id), [
                'team_id' => $this->homeTeam->id,
            ]);

        $foulEvent = MatchEvent::where('match_id', $this->match->id)
            ->where('event_type', 'foul')
            ->latest()->first();

        // Undo foul
        $this->actingAs($this->supervisor)
            ->delete(route('supervisor.matches.events.destroy', [$this->match->id, $foulEvent->id]));

        // Score unchanged
        $this->match->refresh();
        $this->assertEquals(0, $this->match->home_score);
        $this->assertEquals(0, $this->match->away_score);

        // Event soft-deleted
        $this->assertSoftDeleted('match_events', ['id' => $foulEvent->id]);
    }

    public function test_undo_does_not_allow_wrong_match_event(): void
    {
        // Create a second match and an event in it
        $match2 = FutsalMatch::create([
            'competition_id'  => $this->match->competition_id,
            'match_number'    => 'M-002',
            'round'           => 'Penyisihan',
            'home_team_id'    => $this->match->home_team_id,
            'away_team_id'    => $this->match->away_team_id,
            'venue_id'        => $this->match->venue_id,
            'match_date'      => '2026-09-02',
            'kickoff_time'    => '15:00',
            'status'          => MatchStatus::FIRST_HALF,
            'current_period'  => 'first_half',
            'elapsed_seconds' => 0,
            'home_score'      => 0,
            'away_score'      => 0,
        ]);

        $event = MatchEvent::create([
            'match_id'   => $match2->id,
            'team_id'    => $this->homeTeam->id,
            'event_type' => 'foul',
            'period'     => 'first_half',
            'minute'     => 1,
            'second'     => 0,
            'created_by' => $this->supervisor->id,
        ]);

        // Try to undo it via match 1's route — should be 403
        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.matches.events.destroy', [$this->match->id, $event->id]));

        $response->assertStatus(403);
    }
}

