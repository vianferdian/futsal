<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\FutsalMatch;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $teamId = $user->team_id;

        if (!$teamId) {
            return view('pages.team.no_team');
        }

        $team = Team::with('competition')->find($teamId);

        $nextMatch = FutsalMatch::with(['homeTeam', 'awayTeam', 'venue'])
            ->where(function ($q) use ($teamId) {
                $q->where('home_team_id', $teamId)
                  ->orWhere('away_team_id', $teamId);
            })
            ->whereIn('status', ['draft', 'waiting_lineup', 'lineup_submitted', 'ready', 'first_half', 'halftime', 'second_half'])
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->first();

        $lineupStatus = 'Not Started';
        if ($nextMatch) {
            $lineup = $nextMatch->lineups()->where('team_id', $teamId)->first();
            if ($lineup) {
                $lineupStatus = ucfirst($lineup->status);
            }
        }

        $matches = FutsalMatch::with(['homeTeam', 'awayTeam', 'venue'])
            ->where(function ($q) use ($teamId) {
                $q->where('home_team_id', $teamId)
                  ->orWhere('away_team_id', $teamId);
            })
            ->orderBy('match_date', 'desc')
            ->orderBy('kickoff_time', 'desc')
            ->paginate(10);

        return view('pages.team.dashboard', compact('team', 'nextMatch', 'lineupStatus', 'matches'));
    }
}
