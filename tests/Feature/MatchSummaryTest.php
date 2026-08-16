<?php

namespace Tests\Feature;

use App\Enums\MatchStatus;
use App\Enums\UserRole;
use App\Models\Competition;
use App\Models\FutsalMatch;
use App\Models\MatchReport;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected FutsalMatch $match;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin User',
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
            'name' => 'Liga Futsal Indonesia',
            'short_name' => 'LFI',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $homeTeam = Team::create([
            'competition_id' => $competition->id,
            'name' => 'Home Team FC',
            'short_name' => 'HTF',
            'city' => 'Jakarta',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'status' => 'active',
        ]);

        $awayTeam = Team::create([
            'competition_id' => $competition->id,
            'name' => 'Away Team FC',
            'short_name' => 'ATF',
            'city' => 'Bandung',
            'primary_color' => '#ff0000',
            'secondary_color' => '#000000',
            'status' => 'active',
        ]);

        $venue = Venue::create([
            'name' => 'GOR Futsal Utama',
            'city' => 'Jakarta',
            'address' => 'Sudirman Street',
            'capacity' => 3000,
            'status' => 'active',
        ]);

        $this->match = FutsalMatch::create([
            'competition_id' => $competition->id,
            'match_number' => 'M-101',
            'round' => 'Penyisihan',
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'venue_id' => $venue->id,
            'match_date' => '2026-09-01',
            'kickoff_time' => '15:00',
            'status' => MatchStatus::LOCKED,
            'home_score' => 3,
            'away_score' => 2,
            'home_first_half_score' => 1,
            'away_first_half_score' => 1,
        ]);

        // Add a mock report
        MatchReport::create([
            'match_id' => $this->match->id,
            'attendance' => 1200,
            'match_condition' => 'normal',
            'violation_potential' => false,
            'supervisor_notes' => 'Match was competitive and peaceful.',
            'submitted_by' => $this->supervisorUser->id,
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);
    }

    public function test_authenticated_user_can_view_match_summary_web_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('matches.summary', $this->match->id));

        $response->assertOk();
        $response->assertSee('Ringkasan Pertandingan #M-101');
        $response->assertSee('Home Team FC');
        $response->assertSee('Away Team FC');
        $response->assertSee('3 - 2');
        $response->assertSee('1,200 orang');
    }

    public function test_unauthenticated_user_cannot_view_match_summary_web_page(): void
    {
        $response = $this->get(route('matches.summary', $this->match->id));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_match_summary_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('matches.summary.pdf', $this->match->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
