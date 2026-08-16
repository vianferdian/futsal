<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatchRequest;
use App\Http\Requests\UpdateMatchRequest;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\FutsalMatch;
use App\Models\MatchAssignment;
use App\Models\Team;
use App\Models\Venue;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $competitionId = $request->input('competition_id');
        $date = $request->input('date');
        $status = $request->input('status');

        $query = FutsalMatch::with(['competition', 'homeTeam', 'awayTeam', 'venue']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('match_number', 'like', "%{$search}%")
                  ->orWhere('round', 'like', "%{$search}%")
                  ->orWhereHas('homeTeam', fn($t) => $t->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('awayTeam', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        if ($competitionId) {
            $query->where('competition_id', $competitionId);
        }

        if ($date) {
            $query->whereDate('match_date', $date);
        }

        if ($status === 'finished') {
            $query->whereIn('status', [\App\Enums\MatchStatus::FINISHED, \App\Enums\MatchStatus::LOCKED]);
        }

        $matches = $query->orderBy('match_date', 'asc')
            ->orderBy('kickoff_time', 'asc')
            ->paginate(15)->withQueryString();

        $competitions = Competition::orderBy('name')->get();

        return view('pages.admin.matches.index', compact('matches', 'competitions', 'search', 'competitionId', 'date'));
    }

    public function create(Request $request)
    {
        $selectedCompetitionId = $request->input('competition_id');
        $competitions = Competition::where('status', 'active')->orderBy('name')->get();
        $venues = Venue::where('status', 'active')->orderBy('name')->get();
        
        $teams = [];
        if ($selectedCompetitionId) {
            $teams = Team::where('competition_id', $selectedCompetitionId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $teams = Team::where('status', 'active')->orderBy('name')->get();
        }

        return view('pages.admin.matches.create', compact('competitions', 'venues', 'teams', 'selectedCompetitionId'));
    }

    public function store(StoreMatchRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Auto-generate match_number if not provided (though not in form, let's keep database happy)
            $comp = Competition::findOrFail($data['competition_id']);
            $count = FutsalMatch::where('competition_id', $data['competition_id'])->count() + 1;
            $data['match_number'] = strtoupper($comp->short_name) . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            $defaultDuration = (int) \App\Models\Setting::getByKey('default_half_duration', 20);
            $data['first_half_duration'] = $defaultDuration;
            $data['second_half_duration'] = $defaultDuration;
            $data['created_by'] = auth()->id();

            $match = FutsalMatch::create($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat jadwal pertandingan baru: ' . $match->match_number . ' (' . $match->homeTeam->name . ' vs ' . $match->awayTeam->name . ')',
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
                'new_values' => $match->only(['match_number', 'competition_id', 'home_team_id', 'away_team_id', 'venue_id', 'match_date', 'kickoff_time', 'status']),
            ]);
        });

        return redirect()->route('admin.matches.index', ['competition_id' => $request->input('competition_id')])
            ->with('success', 'Jadwal pertandingan berhasil ditambahkan.');
    }

    public function show(FutsalMatch $match)
    {
        $match->load(['competition', 'homeTeam', 'awayTeam', 'venue', 'supervisors']);

        // Get all active supervisors that are NOT already assigned to this match
        $assignedSupervisorIds = $match->supervisors->pluck('id')->toArray();
        $availableSupervisors = User::where('role', UserRole::SUPERVISOR)
            ->where('status', 'active')
            ->whereNotIn('id', $assignedSupervisorIds)
            ->orderBy('name')
            ->get();

        return view('pages.admin.matches.show', compact('match', 'availableSupervisors'));
    }

    public function edit(FutsalMatch $match)
    {
        $competitions = Competition::where('status', 'active')->orderBy('name')->get();
        $venues = Venue::where('status', 'active')->orderBy('name')->get();
        
        $teams = Team::where('competition_id', $match->competition_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pages.admin.matches.edit', compact('match', 'competitions', 'venues', 'teams'));
    }

    public function update(UpdateMatchRequest $request, FutsalMatch $match)
    {
        DB::transaction(function () use ($request, $match) {
            $data = $request->validated();
            $oldValues = $match->only(['competition_id', 'round', 'group_name', 'home_team_id', 'away_team_id', 'venue_id', 'match_date', 'kickoff_time', 'status']);

            $match->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah jadwal pertandingan: ' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
                'old_values' => $oldValues,
                'new_values' => $match->only(['competition_id', 'round', 'group_name', 'home_team_id', 'away_team_id', 'venue_id', 'match_date', 'kickoff_time', 'status']),
            ]);
        });

        return redirect()->route('admin.matches.index', ['competition_id' => $match->competition_id])
            ->with('success', 'Jadwal pertandingan berhasil diubah.');
    }

    public function destroy(FutsalMatch $match)
    {
        // Prevent deletion if match is ongoing/finished
        if (in_array($match->status->value, ['ongoing', 'finished'])) {
            return back()->with('error', 'Pertandingan yang sedang berjalan atau sudah selesai tidak dapat dihapus.');
        }

        DB::transaction(function () use ($match) {
            $oldValues = $match->only(['match_number', 'home_team_id', 'away_team_id']);
            
            // Delete assignments first
            $match->assignments()->delete();
            $match->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus jadwal pertandingan: ' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
                'old_values' => $oldValues,
            ]);
        });

        return redirect()->route('admin.matches.index')
            ->with('success', 'Jadwal pertandingan berhasil dihapus.');
    }

    public function assignSupervisor(Request $request, FutsalMatch $match)
    {
        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if ($user && ($user->role !== UserRole::SUPERVISOR || !$user->isActive())) {
                        $fail('User yang dipilih harus merupakan pengawas pertandingan aktif.');
                    }
                }
            ]
        ]);

        DB::transaction(function () use ($request, $match) {
            $userId = $request->input('user_id');

            // Check if already assigned
            $alreadyAssigned = MatchAssignment::where('match_id', $match->id)
                ->where('user_id', $userId)
                ->exists();

            if (!$alreadyAssigned) {
                MatchAssignment::create([
                    'match_id' => $match->id,
                    'user_id' => $userId,
                    'assignment_type' => 'supervisor',
                ]);

                $user = User::find($userId);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Menugaskan pengawas ' . $user->name . ' ke pertandingan: ' . $match->match_number,
                    'subject_type' => 'FutsalMatch',
                    'subject_id' => $match->id,
                ]);
            }
        });

        return redirect()->route('admin.matches.show', $match->id)
            ->with('success', 'Pengawas pertandingan berhasil ditugaskan.');
    }

    public function unassignSupervisor(FutsalMatch $match, User $user)
    {
        DB::transaction(function () use ($match, $user) {
            MatchAssignment::where('match_id', $match->id)
                ->where('user_id', $user->id)
                ->where('assignment_type', 'supervisor')
                ->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membatalkan penugasan pengawas ' . $user->name . ' dari pertandingan: ' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('admin.matches.show', $match->id)
            ->with('success', 'Penugasan pengawas berhasil dibatalkan.');
    }
}
