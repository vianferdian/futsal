<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\FutsalMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->query('status');

        $query = FutsalMatch::whereHas('supervisors', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });

        $stats = [
            'total_assigned' => (clone $query)->count(),
            'today_assigned' => (clone $query)->whereDate('match_date', today())->count(),
            'upcoming' => (clone $query)->where('match_date', '>', today())->count(),
            'finished' => (clone $query)->whereIn('status', ['finished', 'locked'])->count(),
        ];

        $nextAssignment = FutsalMatch::with(['homeTeam', 'awayTeam', 'venue'])
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->whereIn('status', ['draft', 'waiting_lineup', 'lineup_submitted', 'ready', 'first_half', 'halftime', 'second_half'])
            ->orderBy('match_date')
            ->orderBy('kickoff_time')
            ->first();

        $assignmentsQuery = FutsalMatch::with(['homeTeam', 'awayTeam', 'venue'])
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });

        if ($statusFilter === 'finished') {
            $assignmentsQuery->whereIn('status', ['finished', 'locked']);
        }

        $assignments = $assignmentsQuery->orderBy('match_date', 'desc')
            ->orderBy('kickoff_time', 'desc')
            ->paginate(10);

        return view('pages.supervisor.dashboard', compact('stats', 'nextAssignment', 'assignments'));
    }
}
