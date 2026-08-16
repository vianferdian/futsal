<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FutsalMatch;
use App\Models\MatchJersey;
use App\Models\MatchLineup;
use App\Models\TeamOfficial;
use Illuminate\Http\Request;

use App\Models\MatchEvent;
use App\Models\MatchReport;
use App\Services\ScoreService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function startList(FutsalMatch $match)
    {
        $match->load(['homeTeam', 'awayTeam', 'venue', 'competition', 'supervisors']);

        $homeLineup = MatchLineup::with('players.player')
            ->where('match_id', $match->id)
            ->where('team_id', $match->home_team_id)
            ->first();

        $awayLineup = MatchLineup::with('players.player')
            ->where('match_id', $match->id)
            ->where('team_id', $match->away_team_id)
            ->first();

        $homeJersey = MatchJersey::where('match_id', $match->id)
            ->where('team_id', $match->home_team_id)
            ->first();

        $awayJersey = MatchJersey::where('match_id', $match->id)
            ->where('team_id', $match->away_team_id)
            ->first();

        $homeOfficials = TeamOfficial::where('team_id', $match->home_team_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $awayOfficials = TeamOfficial::where('team_id', $match->away_team_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pages.admin.reports.start_list', compact(
            'match',
            'homeLineup',
            'awayLineup',
            'homeJersey',
            'awayJersey',
            'homeOfficials',
            'awayOfficials'
        ));
    }

    public function matchSummary(FutsalMatch $match)
    {
        $data = $this->getMatchSummaryData($match);
        return view('pages.admin.reports.match_summary', $data);
    }

    public function matchSummaryPdf(FutsalMatch $match)
    {
        $data = $this->getMatchSummaryData($match);
        
        $pdf = Pdf::loadView('pages.admin.reports.match_summary_pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('Match_Summary_' . $match->match_number . '.pdf');
    }

    private function getMatchSummaryData(FutsalMatch $match): array
    {
        $match->load(['homeTeam', 'awayTeam', 'venue', 'competition', 'supervisors']);

        $report = MatchReport::with('submittedByUser')->where('match_id', $match->id)->first();

        $homeLineup = MatchLineup::with('players.player')
            ->where('match_id', $match->id)
            ->where('team_id', $match->home_team_id)
            ->first();

        $awayLineup = MatchLineup::with('players.player')
            ->where('match_id', $match->id)
            ->where('team_id', $match->away_team_id)
            ->first();

        $homeJersey = MatchJersey::where('match_id', $match->id)
            ->where('team_id', $match->home_team_id)
            ->first();

        $awayJersey = MatchJersey::where('match_id', $match->id)
            ->where('team_id', $match->away_team_id)
            ->first();

        $homeOfficials = TeamOfficial::where('team_id', $match->home_team_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $awayOfficials = TeamOfficial::where('team_id', $match->away_team_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $events = MatchEvent::with(['team', 'player', 'relatedPlayer', 'official'])
            ->where('match_id', $match->id)
            ->whereNull('deleted_at')
            ->orderBy('period')
            ->orderBy('minute')
            ->orderBy('second')
            ->get();

        $scoreService = app(ScoreService::class);
        $homeFoulsB1 = $scoreService->foulCount($match, $match->home_team_id, 'first_half');
        $homeFoulsB2 = $scoreService->foulCount($match, $match->home_team_id, 'second_half');
        $awayFoulsB1 = $scoreService->foulCount($match, $match->away_team_id, 'first_half');
        $awayFoulsB2 = $scoreService->foulCount($match, $match->away_team_id, 'second_half');

        $homeTimeoutsB1 = $scoreService->timeoutCount($match, $match->home_team_id, 'first_half');
        $homeTimeoutsB2 = $scoreService->timeoutCount($match, $match->home_team_id, 'second_half');
        $awayTimeoutsB1 = $scoreService->timeoutCount($match, $match->away_team_id, 'first_half');
        $awayTimeoutsB2 = $scoreService->timeoutCount($match, $match->away_team_id, 'second_half');

        return compact(
            'match',
            'report',
            'homeLineup',
            'awayLineup',
            'homeJersey',
            'awayJersey',
            'homeOfficials',
            'awayOfficials',
            'events',
            'homeFoulsB1',
            'homeFoulsB2',
            'awayFoulsB1',
            'awayFoulsB2',
            'homeTimeoutsB1',
            'homeTimeoutsB2',
            'awayTimeoutsB1',
            'awayTimeoutsB2'
        );
    }
}
