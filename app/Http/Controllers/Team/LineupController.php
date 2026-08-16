<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FutsalMatch;
use App\Models\MatchJersey;
use App\Models\MatchLineup;
use App\Models\MatchLineupPlayer;
use App\Models\Player;
use App\Enums\MatchPlayingStatus;
use App\Enums\PlayerPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LineupController extends Controller
{
    public function showForm(FutsalMatch $match)
    {
        $teamId = auth()->user()->team_id;

        if (!$teamId || ($match->home_team_id !== $teamId && $match->away_team_id !== $teamId)) {
            abort(403, 'Anda tidak diizinkan untuk mengelola lineup tim ini.');
        }

        $players = Player::where('team_id', $teamId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $lineup = MatchLineup::firstOrCreate(
            ['match_id' => $match->id, 'team_id' => $teamId],
            ['status' => 'draft']
        );

        $lineupPlayers = MatchLineupPlayer::where('match_lineup_id', $lineup->id)
            ->get()
            ->keyBy('player_id');

        $jersey = MatchJersey::firstOrCreate(
            ['match_id' => $match->id, 'team_id' => $teamId],
            [
                'player_jersey_color' => '#ffffff',
                'player_short_color' => '#000000',
                'player_socks_color' => '#ffffff',
                'goalkeeper_jersey_color' => '#ffff00',
                'goalkeeper_short_color' => '#000000',
                'goalkeeper_socks_color' => '#ffff00',
            ]
        );

        return view('pages.team.lineup.form', compact('match', 'players', 'lineup', 'lineupPlayers', 'jersey'));
    }

    public function saveLineup(Request $request, FutsalMatch $match)
    {
        $teamId = auth()->user()->team_id;

        if (!$teamId || ($match->home_team_id !== $teamId && $match->away_team_id !== $teamId)) {
            abort(403);
        }

        $lineup = MatchLineup::where('match_id', $match->id)
            ->where('team_id', $teamId)
            ->first();

        // Lock check: if submitted or verified, cannot update unless unlocked
        if ($lineup && in_array($lineup->status, ['submitted', 'verified'])) {
            return back()->with('error', 'Lineup sudah dikunci dan tidak dapat diubah.');
        }

        $action = $request->input('action', 'draft'); // draft or submit

        $request->validate([
            'player_jersey_color' => 'required|string|max:50',
            'player_short_color' => 'required|string|max:50',
            'player_socks_color' => 'required|string|max:50',
            'goalkeeper_jersey_color' => 'required|string|max:50',
            'goalkeeper_short_color' => 'required|string|max:50',
            'goalkeeper_socks_color' => 'required|string|max:50',
            'players' => 'required|array',
            'players.*.playing_status' => 'required|string|in:playing,substitute,non_playing',
            'players.*.is_goalkeeper' => 'nullable|boolean',
            'players.*.is_captain' => 'nullable|boolean',
        ]);

        $submittedPlayers = $request->input('players', []);

        // Count starters, goalkeepers, captains
        $startersCount = 0;
        $goalkeeperCount = 0;
        $captainCount = 0;
        $totalRegistered = 0;

        foreach ($submittedPlayers as $playerId => $pData) {
            $playingStatus = $pData['playing_status'];
            $isGoalkeeper = isset($pData['is_goalkeeper']) && $pData['is_goalkeeper'];
            $isCaptain = isset($pData['is_captain']) && $pData['is_captain'];

            if ($playingStatus === 'playing') {
                $startersCount++;
                if ($isGoalkeeper) {
                    $goalkeeperCount++;
                }
            }

            if ($playingStatus === 'playing' || $playingStatus === 'substitute') {
                $totalRegistered++;
                if ($isCaptain) {
                    $captainCount++;
                }
            }
        }

        if ($action === 'submit') {
            if ($startersCount !== 5) {
                return back()->withInput()->with('error', 'Starting 5 (pemain utama) harus berjumlah tepat 5 pemain.');
            }

            if ($goalkeeperCount !== 1) {
                return back()->withInput()->with('error', 'Harus ada tepat 1 penjaga gawang (kiper) di antara Starting 5.');
            }

            if ($captainCount !== 1) {
                return back()->withInput()->with('error', 'Harus ada tepat 1 kapten di antara seluruh squad yang didaftarkan.');
            }

            if ($totalRegistered < 5 || $totalRegistered > 14) {
                return back()->withInput()->with('error', 'Jumlah total skuad (starter + cadangan) harus berjumlah minimal 5 dan maksimal 14 pemain.');
            }
        }

        DB::transaction(function () use ($request, $match, $teamId, $submittedPlayers, $action) {
            // 1. Update/create Lineup status
            $lineup = MatchLineup::updateOrCreate(
                ['match_id' => $match->id, 'team_id' => $teamId],
                [
                    'status' => $action === 'submit' ? 'submitted' : 'draft',
                    'submitted_by' => $action === 'submit' ? auth()->id() : null,
                    'submitted_at' => $action === 'submit' ? now() : null,
                ]
            );

            // 2. Clear old lineup players
            MatchLineupPlayer::where('match_lineup_id', $lineup->id)->delete();

            // 3. Create new lineup players
            foreach ($submittedPlayers as $playerId => $pData) {
                $playingStatus = $pData['playing_status'];
                if ($playingStatus === 'non_playing') {
                    continue;
                }

                $player = Player::findOrFail($playerId);

                MatchLineupPlayer::create([
                    'match_lineup_id' => $lineup->id,
                    'player_id' => $player->id,
                    'playing_status' => $playingStatus,
                    'position' => $player->position,
                    'is_goalkeeper' => isset($pData['is_goalkeeper']) && $pData['is_goalkeeper'],
                    'is_captain' => isset($pData['is_captain']) && $pData['is_captain'],
                ]);
            }



            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => ($action === 'submit' ? 'Mengirim' : 'Menyimpan draft') . ' susunan pemain untuk pertandingan #' . $match->match_number,
                'subject_type' => 'MatchLineup',
                'subject_id' => $lineup->id,
            ]);
        });

        $msg = $action === 'submit' ? 'Susunan pemain berhasil dikirim.' : 'Draft susunan pemain berhasil disimpan.';
        return redirect()->route('team.dashboard')->with('success', $msg);
    }
}
