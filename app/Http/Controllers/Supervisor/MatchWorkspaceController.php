<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Enums\MatchStatus;
use App\Models\AuditLog;
use App\Models\FutsalMatch;
use App\Models\MatchEvent;
use App\Models\MatchJersey;
use App\Models\MatchLineup;
use App\Models\TeamOfficial;
use App\Services\ScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchWorkspaceController extends Controller
{
    public function __construct(private ScoreService $scoreService) {}

    private function authorizeSupervisor(FutsalMatch $match)
    {
        $assigned = $match->supervisors()->where('users.id', auth()->id())->exists();
        if (!$assigned) {
            abort(403, 'Anda tidak ditugaskan sebagai pengawas di pertandingan ini.');
        }
    }

    public function showWorkspace(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);

        $match->load(['homeTeam', 'awayTeam', 'venue', 'competition']);

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

        $homeOfficials = TeamOfficial::where('team_id', $match->home_team_id)->where('status', 'active')->get();
        $awayOfficials = TeamOfficial::where('team_id', $match->away_team_id)->where('status', 'active')->get();

        // Foul and timeout counts for current period
        $period = $match->current_period ?? 'first_half';
        $homeFouls   = $this->scoreService->foulCount($match, $match->home_team_id, $period);
        $awayFouls   = $this->scoreService->foulCount($match, $match->away_team_id, $period);
        $homeTimeout = $this->scoreService->timeoutCount($match, $match->home_team_id, $period);
        $awayTimeout = $this->scoreService->timeoutCount($match, $match->away_team_id, $period);

        // Timeline events — all non-deleted, newest-first
        $events = MatchEvent::with(['team', 'player', 'relatedPlayer', 'official', 'createdByUser'])
            ->where('match_id', $match->id)
            ->orderByDesc('period')   // second_half > first_half alphabetically
            ->orderByDesc('minute')
            ->orderByDesc('second')
            ->orderByDesc('id')
            ->get();

        return view('pages.supervisor.workspace.dashboard', compact(
            'match', 'homeLineup', 'awayLineup', 'homeJersey', 'awayJersey',
            'homeOfficials', 'awayOfficials',
            'homeFouls', 'awayFouls', 'homeTimeout', 'awayTimeout',
            'events'
        ));
    }

    public function startMatch(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);

        if ($match->status !== MatchStatus::READY) {
            return back()->with('error', 'Pertandingan belum siap dimulai. Pastikan kedua lineup tim telah diverifikasi.');
        }

        DB::transaction(function () use ($match) {
            $match->update([
                'status' => MatchStatus::FIRST_HALF,
                'current_period' => 'first_half',
                'timer_status' => 'running',
                'timer_started_at' => now(),
                'started_at' => now(),
                'elapsed_seconds' => 0,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Memulai babak pertama pertandingan #' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('supervisor.matches.workspace', $match->id)
            ->with('success', 'Pertandingan babak pertama berhasil dimulai.');
    }

    public function pauseTimer(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        if ($match->timer_status !== 'running') {
            return back()->with('error', 'Timer tidak sedang berjalan.');
        }

        DB::transaction(function () use ($match) {
            $startedAt = $match->timer_started_at;
            $secondsDiff = $startedAt ? abs(now()->diffInSeconds($startedAt)) : 0;
            $newElapsed = $match->elapsed_seconds + $secondsDiff;

            $match->update([
                'timer_status' => 'paused',
                'timer_paused_at' => now(),
                'elapsed_seconds' => $newElapsed,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghentikan sementara (pause) timer pertandingan #' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('supervisor.matches.workspace', $match->id)
            ->with('success', 'Timer berhasil dihentikan sementara.');
    }

    public function resumeTimer(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        if ($match->timer_status !== 'paused') {
            return back()->with('error', 'Timer tidak dalam posisi pause.');
        }

        DB::transaction(function () use ($match) {
            $match->update([
                'timer_status' => 'running',
                'timer_started_at' => now(),
                'timer_paused_at' => null,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Melanjutkan (resume) timer pertandingan #' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('supervisor.matches.workspace', $match->id)
            ->with('success', 'Timer berhasil dilanjutkan.');
    }

    public function endFirstHalf(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        if ($match->status !== MatchStatus::FIRST_HALF) {
            return back()->with('error', 'Pertandingan tidak berada di babak pertama.');
        }

        DB::transaction(function () use ($match) {
            $elapsed = $match->elapsed_seconds;
            if ($match->timer_status === 'running' && $match->timer_started_at) {
                $elapsed += abs(now()->diffInSeconds($match->timer_started_at));
            }

            $match->update([
                'status' => MatchStatus::HALFTIME,
                'current_period' => 'halftime',
                'timer_status' => 'paused',
                'timer_paused_at' => now(),
                'elapsed_seconds' => $elapsed,
                'home_first_half_score' => $match->home_score,
                'away_first_half_score' => $match->away_score,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengakhiri babak pertama pertandingan #' . $match->match_number . '. Skor HT: ' . $match->home_score . '-' . $match->away_score,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('supervisor.matches.workspace', $match->id)
            ->with('success', 'Babak pertama berakhir.');
    }

    public function startSecondHalf(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        if ($match->status !== MatchStatus::HALFTIME) {
            return back()->with('error', 'Pertandingan tidak berada dalam jeda babak.');
        }

        DB::transaction(function () use ($match) {
            $match->update([
                'status' => MatchStatus::SECOND_HALF,
                'current_period' => 'second_half',
                'timer_status' => 'running',
                'timer_started_at' => now(),
                'timer_paused_at' => null,
                'elapsed_seconds' => 0,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Memulai babak kedua pertandingan #' . $match->match_number,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('supervisor.matches.workspace', $match->id)
            ->with('success', 'Babak kedua berhasil dimulai.');
    }

    public function finishMatch(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        if (!in_array($match->status->value, ['first_half', 'halftime', 'second_half'])) {
            return back()->with('error', 'Pertandingan tidak berada dalam babak aktif.');
        }

        DB::transaction(function () use ($match) {
            $match->update([
                'status' => MatchStatus::FINISHED,
                'current_period' => 'finished',
                'timer_status' => 'finished',
                'finished_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengakhiri pertandingan #' . $match->match_number . '. Skor akhir: ' . $match->home_score . '-' . $match->away_score,
                'subject_type' => 'FutsalMatch',
                'subject_id' => $match->id,
            ]);
        });

        return redirect()->route('supervisor.matches.workspace', $match->id)
            ->with('success', 'Pertandingan telah selesai.');
    }
}
