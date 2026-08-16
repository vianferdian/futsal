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

class MatchReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected FutsalMatch $match;

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
            'name' => 'Liga Test', 'short_name' => 'LT', 'season' => '2026',
            'start_date' => '2026-08-01', 'end_date' => '2026-12-31', 'status' => 'active',
        ]);

        $homeTeam = Team::create([
            'competition_id' => $competition->id, 'name' => 'Home', 'short_name' => 'HME',
            'city' => 'Jkt', 'primary_color' => '#000', 'secondary_color' => '#fff', 'status' => 'active',
        ]);
        $awayTeam = Team::create([
            'competition_id' => $competition->id, 'name' => 'Away', 'short_name' => 'AWY',
            'city' => 'Bdg', 'primary_color' => '#f00', 'secondary_color' => '#fff', 'status' => 'active',
        ]);
        $venue = Venue::create([
            'name' => 'GOR', 'city' => 'Jkt', 'address' => 'Jl.', 'capacity' => 1000, 'status' => 'active',
        ]);

        $this->match = FutsalMatch::create([
            'competition_id'     => $competition->id,
            'match_number'       => 'M-001',
            'round'              => 'Penyisihan',
            'home_team_id'       => $homeTeam->id,
            'away_team_id'       => $awayTeam->id,
            'venue_id'           => $venue->id,
            'match_date'         => '2026-09-01',
            'kickoff_time'       => '14:00',
            'status'             => MatchStatus::FINISHED,
            'current_period'     => 'second_half',
            'elapsed_seconds'    => 0,
            'home_score'         => 2,
            'away_score'         => 1,
            'home_first_half_score' => 1,
            'away_first_half_score' => 0,
            'finished_at'        => now(),
        ]);

        $this->match->supervisors()->attach($this->supervisor->id, ['assignment_type' => 'supervisor']);
    }

    public function test_supervisor_can_view_report_page(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.matches.report', $this->match->id));

        $response->assertOk();
        $response->assertSee('Laporan Pascapertandingan');
        $response->assertSee($this->match->match_number);
    }

    public function test_report_page_blocked_when_match_not_finished(): void
    {
        $this->match->update(['status' => MatchStatus::FIRST_HALF]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.matches.report', $this->match->id));

        $response->assertRedirect(route('supervisor.matches.workspace', $this->match->id));
        $response->assertSessionHas('error');
    }

    public function test_can_save_report_draft(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.save', $this->match->id), [
                'match_condition'     => 'normal',
                'attendance'          => 1500,
                'violation_potential' => '0',
                'supervisor_notes'    => 'Pertandingan berjalan lancar.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('match_reports', [
            'match_id'         => $this->match->id,
            'match_condition'  => 'normal',
            'attendance'       => 1500,
            'supervisor_notes' => 'Pertandingan berjalan lancar.',
        ]);
    }

    public function test_saving_report_twice_upserts_correctly(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.save', $this->match->id), [
                'match_condition' => 'normal',
                'attendance'      => 1000,
            ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.save', $this->match->id), [
                'match_condition' => 'delayed',
                'attendance'      => 2000,
            ]);

        // Should still be 1 report, updated
        $this->assertEquals(1, MatchReport::where('match_id', $this->match->id)->count());

        $this->assertDatabaseHas('match_reports', [
            'match_id'        => $this->match->id,
            'match_condition' => 'delayed',
            'attendance'      => 2000,
        ]);
    }

    public function test_can_submit_and_lock_report(): void
    {
        // First save draft
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.save', $this->match->id), [
                'match_condition' => 'normal',
                'attendance'      => 800,
            ]);

        // Then submit
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.submit', $this->match->id));

        $response->assertRedirect(route('supervisor.matches.report', $this->match->id));
        $response->assertSessionHas('success');

        // Report locked
        $report = MatchReport::where('match_id', $this->match->id)->first();
        $this->assertNotNull($report->locked_at);
        $this->assertEquals($this->supervisor->id, $report->submitted_by);

        // Match status → LOCKED
        $this->match->refresh();
        $this->assertEquals(MatchStatus::LOCKED, $this->match->status);
    }

    public function test_cannot_edit_locked_report(): void
    {
        // Create and lock a report
        MatchReport::create([
            'match_id'        => $this->match->id,
            'match_condition' => 'normal',
            'submitted_by'    => $this->supervisor->id,
            'submitted_at'    => now(),
            'locked_at'       => now(),
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.save', $this->match->id), [
                'match_condition' => 'delayed',
                'attendance'      => 9999,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Data should not have changed
        $this->assertDatabaseMissing('match_reports', ['attendance' => 9999]);
    }

    public function test_cannot_submit_without_saved_report(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.submit', $this->match->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_non_assigned_supervisor_cannot_access_report(): void
    {
        $other = User::create([
            'name' => 'Other', 'email' => 'other@test.com', 'username' => 'other_spv',
            'password' => bcrypt('password'), 'role' => UserRole::SUPERVISOR, 'status' => 'active',
        ]);

        $response = $this->actingAs($other)
            ->get(route('supervisor.matches.report', $this->match->id));

        $response->assertStatus(403);
    }

    public function test_report_with_violation_flag_stored_correctly(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.matches.report.save', $this->match->id), [
                'match_condition'     => 'interrupted',
                'attendance'          => 500,
                'violation_potential' => '1',
                'violation_notes'     => 'Ada keributan di tribun penonton menit ke-35.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('match_reports', [
            'match_id'            => $this->match->id,
            'violation_potential' => 1,
            'violation_notes'     => 'Ada keributan di tribun penonton menit ke-35.',
        ]);
    }
}
