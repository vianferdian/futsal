<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FutsalMatch;
use App\Models\MatchJersey;
use App\Models\MatchLineup;
use App\Models\Team;
use App\Enums\MatchStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LineupVerificationController extends Controller
{
    private function authorizeSupervisor(FutsalMatch $match)
    {
        $assigned = $match->supervisors()->where('users.id', auth()->id())->exists();
        if (!$assigned) {
            abort(403, 'Anda tidak ditugaskan sebagai pengawas di pertandingan ini.');
        }
    }

    public function verifyForm(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);

        $match->load(['homeTeam', 'awayTeam', 'venue']);

        $homeLineup = MatchLineup::with(['players.player', 'submittedByUser', 'verifiedByUser'])
            ->where('match_id', $match->id)
            ->where('team_id', $match->home_team_id)
            ->first();

        $awayLineup = MatchLineup::with(['players.player', 'submittedByUser', 'verifiedByUser'])
            ->where('match_id', $match->id)
            ->where('team_id', $match->away_team_id)
            ->first();

        $homeJersey = MatchJersey::where('match_id', $match->id)
            ->where('team_id', $match->home_team_id)
            ->first();

        $awayJersey = MatchJersey::where('match_id', $match->id)
            ->where('team_id', $match->away_team_id)
            ->first();

        return view('pages.supervisor.lineup.verify', compact('match', 'homeLineup', 'awayLineup', 'homeJersey', 'awayJersey'));
    }

    public function approveLineup(FutsalMatch $match, Team $team)
    {
        $this->authorizeSupervisor($match);

        $lineup = MatchLineup::where('match_id', $match->id)
            ->where('team_id', $team->id)
            ->first();

        if (!$lineup || $lineup->status !== 'submitted') {
            return back()->with('error', 'Lineup belum dikirim atau sudah diproses.');
        }

        DB::transaction(function () use ($match, $team, $lineup) {
            $lineup->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Check if BOTH lineups are verified
            $opponentTeamId = $match->home_team_id === $team->id ? $match->away_team_id : $match->home_team_id;
            $opponentVerified = MatchLineup::where('match_id', $match->id)
                ->where('team_id', $opponentTeamId)
                ->where('status', 'verified')
                ->exists();

            if ($opponentVerified) {
                $match->update(['status' => MatchStatus::READY]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Memverifikasi lineup tim ' . $team->name . ' untuk pertandingan #' . $match->match_number,
                'subject_type' => 'MatchLineup',
                'subject_id' => $lineup->id,
            ]);
        });

        return redirect()->route('supervisor.matches.verify-lineup', $match->id)
            ->with('success', 'Lineup tim ' . $team->name . ' berhasil diverifikasi.');
    }

    public function unlockLineup(Request $request, FutsalMatch $match, Team $team)
    {
        $this->authorizeSupervisor($match);

        $request->validate([
            'unlock_reason' => 'required|string|min:10|max:1000',
        ]);

        $lineup = MatchLineup::where('match_id', $match->id)
            ->where('team_id', $team->id)
            ->first();

        if (!$lineup || !in_array($lineup->status, ['submitted', 'verified'])) {
            return back()->with('error', 'Lineup tidak dalam posisi terkunci.');
        }

        DB::transaction(function () use ($request, $match, $team, $lineup) {
            $lineup->update([
                'status' => 'draft',
                'unlock_reason' => $request->input('unlock_reason'),
                'submitted_by' => null,
                'submitted_at' => null,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            // Demote match status if it was ready
            if ($match->status === MatchStatus::READY) {
                $match->update(['status' => MatchStatus::WAITING_LINEUP]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuka kunci (unlock) lineup tim ' . $team->name . ' untuk pertandingan #' . $match->match_number . '. Alasan: ' . $request->input('unlock_reason'),
                'subject_type' => 'MatchLineup',
                'subject_id' => $lineup->id,
            ]);
        });

        return redirect()->route('supervisor.matches.verify-lineup', $match->id)
            ->with('success', 'Kunci lineup tim ' . $team->name . ' berhasil dibuka.');
    }

    public function updateJersey(Request $request, FutsalMatch $match, Team $team)
    {
        $this->authorizeSupervisor($match);

        $request->validate([
            'player_jersey_color' => 'required|string',
            'player_short_color' => 'required|string',
            'player_socks_color' => 'required|string',
            'goalkeeper_jersey_color' => 'required|string',
            'goalkeeper_short_color' => 'required|string',
            'goalkeeper_socks_color' => 'required|string',
        ]);

        MatchJersey::updateOrCreate(
            ['match_id' => $match->id, 'team_id' => $team->id],
            [
                'player_jersey_color' => $request->input('player_jersey_color'),
                'player_short_color' => $request->input('player_short_color'),
                'player_socks_color' => $request->input('player_socks_color'),
                'goalkeeper_jersey_color' => $request->input('goalkeeper_jersey_color'),
                'goalkeeper_short_color' => $request->input('goalkeeper_short_color'),
                'goalkeeper_socks_color' => $request->input('goalkeeper_socks_color'),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Mengupdate warna jersey tim ' . $team->name . ' untuk pertandingan #' . $match->match_number,
            'subject_type' => 'FutsalMatch',
            'subject_id' => $match->id,
        ]);

        return redirect()->route('supervisor.matches.verify-lineup', $match->id)
            ->with('success', 'Warna jersey tim ' . $team->name . ' berhasil diperbarui.');
    }
}
