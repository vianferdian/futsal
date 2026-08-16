<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\MatchStatus;
use App\Enums\PlayerPosition;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use App\Models\Player;
use App\Models\FutsalMatch;
use App\Models\MatchLineup;
use App\Models\MatchJersey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LineupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected User $homeTeamAdmin;
    protected User $awayTeamAdmin;
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

        $this->homeTeamAdmin = User::create([
            'name' => 'Home Admin',
            'email' => 'home@admin.com',
            'username' => 'home_admin',
            'password' => bcrypt('password'),
            'role' => UserRole::TEAM_ADMIN,
            'team_id' => $this->homeTeam->id,
            'status' => 'active',
        ]);

        $this->awayTeamAdmin = User::create([
            'name' => 'Away Admin',
            'email' => 'away@admin.com',
            'username' => 'away_admin',
            'password' => bcrypt('password'),
            'role' => UserRole::TEAM_ADMIN,
            'team_id' => $this->awayTeam->id,
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

        // Assign supervisor
        $this->match->supervisors()->attach($this->supervisorUser->id, [
            'assignment_type' => 'supervisor',
        ]);

        // Create 14 players for home team
        for ($i = 1; $i <= 14; $i++) {
            Player::create([
                'team_id' => $this->homeTeam->id,
                'name' => 'Home Player ' . $i,
                'shirt_number' => $i,
                'position' => PlayerPosition::ALA,
                'status' => 'active',
            ]);
        }

        // Create 14 players for away team
        for ($i = 1; $i <= 14; $i++) {
            Player::create([
                'team_id' => $this->awayTeam->id,
                'name' => 'Away Player ' . $i,
                'shirt_number' => $i,
                'position' => PlayerPosition::ALA,
                'status' => 'active',
            ]);
        }
    }

    public function test_team_admin_cannot_access_other_team_lineup_page(): void
    {
        // Try accessing away lineup using homeTeamAdmin (match has both teams, but user must belong to that team context - wait, the controller only checks if they belong to either home or away team. BUT wait, can they submit for the opponent? Let's check showForm:
        // it checks: ($match->home_team_id !== $teamId && $match->away_team_id !== $teamId).
        // Since homeTeamAdmin has team_id = homeTeam->id, which is indeed in the match, they can view/edit their own team lineup.
        // What if they try to access a match where their team is NOT playing?
        $anotherMatch = FutsalMatch::create([
            'competition_id' => $this->competition->id,
            'match_number' => 'M-002',
            'round' => 'Penyisihan',
            'home_team_id' => $this->awayTeam->id,
            'away_team_id' => $this->awayTeam->id, // just dummy
            'venue_id' => $this->venue->id,
            'match_date' => '2026-09-02',
            'kickoff_time' => '14:00',
            'status' => MatchStatus::WAITING_LINEUP,
        ]);

        $this->actingAs($this->homeTeamAdmin)
            ->get(route('team.matches.lineup', $anotherMatch->id))
            ->assertStatus(403);
    }

    public function test_can_save_draft_without_strict_validation(): void
    {
        $players = Player::where('team_id', $this->homeTeam->id)->take(3)->get();
        
        $postData = [
            'action' => 'draft',
            'player_jersey_color' => '#ffffff',
            'player_short_color' => '#000000',
            'player_socks_color' => '#ffffff',
            'goalkeeper_jersey_color' => '#ffff00',
            'goalkeeper_short_color' => '#000000',
            'goalkeeper_socks_color' => '#ffff00',
            'players' => [],
        ];

        foreach ($players as $p) {
            $postData['players'][$p->id] = [
                'playing_status' => 'playing',
            ];
        }

        $response = $this->actingAs($this->homeTeamAdmin)
            ->post(route('team.matches.lineup.save', $this->match->id), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('match_lineups', [
            'match_id' => $this->match->id,
            'team_id' => $this->homeTeam->id,
            'status' => 'draft',
        ]);
    }

    public function test_cannot_submit_incomplete_lineup(): void
    {
        // 1. Submit with only 3 starters
        $players = Player::where('team_id', $this->homeTeam->id)->take(3)->get();
        $postData = [
            'action' => 'submit',
            'player_jersey_color' => '#ffffff',
            'player_short_color' => '#000000',
            'player_socks_color' => '#ffffff',
            'goalkeeper_jersey_color' => '#ffff00',
            'goalkeeper_short_color' => '#000000',
            'goalkeeper_socks_color' => '#ffff00',
            'players' => [],
        ];

        foreach ($players as $p) {
            $postData['players'][$p->id] = [
                'playing_status' => 'playing',
            ];
        }

        $response = $this->actingAs($this->homeTeamAdmin)
            ->post(route('team.matches.lineup.save', $this->match->id), $postData);

        $response->assertSessionHas('error');
    }

    public function test_can_submit_complete_lineup_and_gets_locked(): void
    {
        $players = Player::where('team_id', $this->homeTeam->id)->get();
        
        $postData = [
            'action' => 'submit',
            'player_jersey_color' => '#ffffff',
            'player_short_color' => '#000000',
            'player_socks_color' => '#ffffff',
            'goalkeeper_jersey_color' => '#ffff00',
            'goalkeeper_short_color' => '#000000',
            'goalkeeper_socks_color' => '#ffff00',
            'players' => [],
        ];

        // 5 starters (1 is goalkeeper, 1 is captain)
        for ($i = 0; $i < 5; $i++) {
            $p = $players[$i];
            $postData['players'][$p->id] = [
                'playing_status' => 'playing',
                'is_goalkeeper' => $i === 0 ? '1' : '0',
                'is_captain' => $i === 1 ? '1' : '0',
            ];
        }

        // 3 substitutes
        for ($i = 5; $i < 8; $i++) {
            $p = $players[$i];
            $postData['players'][$p->id] = [
                'playing_status' => 'substitute',
            ];
        }

        $response = $this->actingAs($this->homeTeamAdmin)
            ->post(route('team.matches.lineup.save', $this->match->id), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('match_lineups', [
            'match_id' => $this->match->id,
            'team_id' => $this->homeTeam->id,
            'status' => 'submitted',
        ]);

        // Attempting to resubmit locked lineup should FAIL
        $resubmitResponse = $this->actingAs($this->homeTeamAdmin)
            ->post(route('team.matches.lineup.save', $this->match->id), $postData);

        $resubmitResponse->assertSessionHas('error');
    }

    public function test_supervisor_can_verify_lineup_and_sets_ready_when_both_verified(): void
    {
        // 1. Create submitted lineups for BOTH home and away teams
        $homeLineup = MatchLineup::create([
            'match_id' => $this->match->id,
            'team_id' => $this->homeTeam->id,
            'status' => 'submitted',
        ]);

        $awayLineup = MatchLineup::create([
            'match_id' => $this->match->id,
            'team_id' => $this->awayTeam->id,
            'status' => 'submitted',
        ]);

        // 2. Approve Home team lineup
        $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.verify-lineup.approve', [$this->match->id, $this->homeTeam->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('match_lineups', [
            'id' => $homeLineup->id,
            'status' => 'verified',
        ]);

        // Match status should still be WAITING_LINEUP
        $this->assertEquals(MatchStatus::WAITING_LINEUP, $this->match->fresh()->status);

        // 3. Approve Away team lineup
        $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.verify-lineup.approve', [$this->match->id, $this->awayTeam->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('match_lineups', [
            'id' => $awayLineup->id,
            'status' => 'verified',
        ]);

        // Match status should now be READY!
        $this->assertEquals(MatchStatus::READY, $this->match->fresh()->status);
    }

    public function test_supervisor_can_unlock_lineup_with_reason(): void
    {
        // 1. Create verified lineup
        $lineup = MatchLineup::create([
            'match_id' => $this->match->id,
            'team_id' => $this->homeTeam->id,
            'status' => 'verified',
        ]);

        $this->match->update(['status' => MatchStatus::READY]);

        // 2. Unlock
        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.verify-lineup.unlock', [$this->match->id, $this->homeTeam->id]), [
                'unlock_reason' => 'Jersey home and away colors clash. Please choose another jersey.',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('match_lineups', [
            'id' => $lineup->id,
            'status' => 'draft',
            'unlock_reason' => 'Jersey home and away colors clash. Please choose another jersey.',
        ]);

        // Match status demoted back to WAITING_LINEUP
        $this->assertEquals(MatchStatus::WAITING_LINEUP, $this->match->fresh()->status);
    }

    public function test_supervisor_can_update_jersey_colors(): void
    {
        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.matches.verify-lineup.jersey', [$this->match->id, $this->homeTeam->id]), [
                'player_jersey_color' => '#112233',
                'player_short_color' => '#445566',
                'player_socks_color' => '#778899',
                'goalkeeper_jersey_color' => '#aabbcc',
                'goalkeeper_short_color' => '#ddeeff',
                'goalkeeper_socks_color' => '#001122',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('match_jerseys', [
            'match_id' => $this->match->id,
            'team_id' => $this->homeTeam->id,
            'player_jersey_color' => '#112233',
            'player_short_color' => '#445566',
            'player_socks_color' => '#778899',
            'goalkeeper_jersey_color' => '#aabbcc',
            'goalkeeper_short_color' => '#ddeeff',
            'goalkeeper_socks_color' => '#001122',
        ]);
    }
}
