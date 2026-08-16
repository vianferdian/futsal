<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Player;
use App\Models\FutsalMatch;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_competitions' => Competition::count(),
            'total_teams' => Team::count(),
            'total_players' => Player::count(),
            'total_matches' => FutsalMatch::count(),
            'matches_today' => FutsalMatch::whereDate('match_date', today())->count(),
            'live_matches' => FutsalMatch::whereIn('status', ['first_half', 'halftime', 'second_half'])->count(),
            'finished_matches' => FutsalMatch::whereIn('status', ['finished', 'locked'])->count(),
        ];

        $todayMatches = FutsalMatch::with(['homeTeam', 'awayTeam', 'venue', 'supervisors'])
            ->whereDate('match_date', today())
            ->orderBy('kickoff_time')
            ->get();

        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('pages.admin.dashboard', compact('stats', 'todayMatches', 'recentActivities'));
    }
}
