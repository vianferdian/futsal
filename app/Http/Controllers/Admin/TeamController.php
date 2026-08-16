<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\Team;
use App\Models\FutsalMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Team::with('competition');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
        }

        $teams = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('pages.admin.teams.index', compact('teams', 'search'));
    }

    public function create()
    {
        $competitions = Competition::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.teams.create', compact('competitions'));
    }

    public function store(StoreTeamRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('teams', 'public');
                $data['logo'] = $path;
            }

            $team = Team::create($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat tim baru: ' . $team->name,
                'subject_type' => 'Team',
                'subject_id' => $team->id,
                'new_values' => $team->only(['name', 'short_name', 'city', 'primary_color', 'secondary_color', 'competition_id', 'status']),
            ]);
        });

        return redirect()->route('admin.teams.index')
            ->with('success', 'Tim berhasil ditambahkan.');
    }

    public function show(Team $team, Request $request)
    {
        $tab = $request->input('tab', 'overview');

        $team->load(['competition', 'players', 'officials']);

        $matches = [];
        if ($tab === 'matches') {
            $matches = FutsalMatch::with(['homeTeam', 'awayTeam', 'venue'])
                ->where(function ($query) use ($team) {
                    $query->where('home_team_id', $team->id)
                          ->orWhere('away_team_id', $team->id);
                })
                ->orderBy('match_date', 'desc')
                ->orderBy('kickoff_time', 'desc')
                ->paginate(10)->withQueryString();
        }

        return view('pages.admin.teams.show', compact('team', 'tab', 'matches'));
    }

    public function edit(Team $team)
    {
        $competitions = Competition::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.teams.edit', compact('team', 'competitions'));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        DB::transaction(function () use ($request, $team) {
            $data = $request->validated();
            $oldValues = $team->only(['name', 'short_name', 'city', 'primary_color', 'secondary_color', 'competition_id', 'logo', 'status']);

            if ($request->hasFile('logo')) {
                if ($team->logo) {
                    Storage::disk('public')->delete($team->logo);
                }
                $path = $request->file('logo')->store('teams', 'public');
                $data['logo'] = $path;
            }

            $team->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah data tim: ' . $team->name,
                'subject_type' => 'Team',
                'subject_id' => $team->id,
                'old_values' => $oldValues,
                'new_values' => $team->only(['name', 'short_name', 'city', 'primary_color', 'secondary_color', 'competition_id', 'logo', 'status']),
            ]);
        });

        return redirect()->route('admin.teams.index')
            ->with('success', 'Data tim berhasil diubah.');
    }

    public function destroy(Team $team)
    {
        // Check if team has matches
        $hasMatches = FutsalMatch::where('home_team_id', $team->id)
            ->orWhere('away_team_id', $team->id)
            ->count() > 0;

        if ($hasMatches) {
            return back()->with('error', 'Tim tidak dapat dihapus karena sudah memiliki histori pertandingan.');
        }

        DB::transaction(function () use ($team) {
            $oldValues = $team->only(['name', 'short_name', 'city']);

            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }

            // Also hard/soft delete related players and officials? 
            // In foreign keys we set cascade/restrict, but soft delete players is safer.
            $team->players()->delete();
            $team->officials()->delete();
            
            $team->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus tim: ' . $team->name,
                'subject_type' => 'Team',
                'subject_id' => $team->id,
                'old_values' => $oldValues,
            ]);
        });

        return redirect()->route('admin.teams.index')
            ->with('success', 'Tim berhasil dihapus.');
    }
}
