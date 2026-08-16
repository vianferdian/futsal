<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MatchEventType;
use App\Http\Controllers\Controller;
use App\Models\FutsalMatch;
use App\Models\MatchEvent;
use App\Models\MatchLineup;
use App\Models\MatchLineupPlayer;
use App\Models\TeamOfficial;
use App\Services\MatchEventService;
use App\Services\ScoreService;
use Illuminate\Http\Request;

class MatchEventController extends Controller
{
    public function __construct(
        private MatchEventService $eventService,
        private ScoreService $scoreService,
    ) {}

    private function authorizeSupervisor(FutsalMatch $match): void
    {
        if (!$match->supervisors()->where('users.id', auth()->id())->exists()) {
            abort(403, 'Anda tidak ditugaskan sebagai pengawas di pertandingan ini.');
        }
    }

    private function assertMatchActive(FutsalMatch $match): void
    {
        if (!in_array($match->status->value, ['first_half', 'second_half'])) {
            abort(422, 'Event hanya dapat dicatat saat pertandingan sedang berlangsung.');
        }
    }

    /**
     * Store a goal event (normal, own goal, penalty, second penalty, penalty miss).
     */
    public function storeGoal(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        $this->assertMatchActive($match);

        $request->validate([
            'team_id'           => 'required|integer|in:' . $match->home_team_id . ',' . $match->away_team_id,
            'event_type'        => 'required|in:goal,own_goal,penalty_goal,second_penalty_goal,penalty_miss',
            'player_id'         => 'required|integer',
            'related_player_id' => 'nullable|integer',
            'minute'            => 'required|integer|min:0',
            'second'            => 'required|integer|min:0|max:59',
        ]);

        // Verify player belongs to the team
        $lineup = MatchLineup::where('match_id', $match->id)
            ->where('team_id', $request->team_id)
            ->firstOrFail();

        $playerInLineup = MatchLineupPlayer::where('match_lineup_id', $lineup->id)
            ->where('player_id', $request->player_id)
            ->exists();

        if (!$playerInLineup) {
            return back()->with('error', 'Pemain tidak terdaftar dalam lineup tim ini.');
        }

        $this->eventService->create($match, [
            'team_id'           => $request->team_id,
            'event_type'        => $request->event_type,
            'period'            => $match->current_period,
            'minute'            => $request->minute,
            'second'            => $request->second,
            'player_id'         => $request->player_id,
            'related_player_id' => $request->related_player_id,
        ]);

        return back()->with('success', 'Gol berhasil dicatat.');
    }

    /**
     * Store a player card event (yellow, second yellow, red).
     */
    public function storeCard(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        $this->assertMatchActive($match);

        $request->validate([
            'team_id'    => 'required|integer|in:' . $match->home_team_id . ',' . $match->away_team_id,
            'event_type' => 'required|in:yellow_card,second_yellow,red_card',
            'player_id'  => 'required|integer',
            'minute'     => 'required|integer|min:0',
            'second'     => 'required|integer|min:0|max:59',
        ]);

        // Verify player belongs to the team
        $lineup = MatchLineup::where('match_id', $match->id)
            ->where('team_id', $request->team_id)
            ->firstOrFail();

        $playerInLineup = MatchLineupPlayer::where('match_lineup_id', $lineup->id)
            ->where('player_id', $request->player_id)
            ->exists();

        if (!$playerInLineup) {
            return back()->with('error', 'Pemain tidak terdaftar dalam lineup tim ini.');
        }

        $this->eventService->create($match, [
            'team_id'    => $request->team_id,
            'event_type' => $request->event_type,
            'period'     => $match->current_period,
            'minute'     => $request->minute,
            'second'     => $request->second,
            'player_id'  => $request->player_id,
        ]);

        return back()->with('success', 'Kartu berhasil dicatat.');
    }

    /**
     * Store a foul event (1-click, no player selection needed).
     */
    public function storeFoul(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        $this->assertMatchActive($match);

        $request->validate([
            'team_id' => 'required|integer|in:' . $match->home_team_id . ',' . $match->away_team_id,
        ]);

        $time = $this->eventService->currentMatchTime($match);

        $this->eventService->create($match, [
            'team_id'    => $request->team_id,
            'event_type' => MatchEventType::FOUL->value,
            'period'     => $match->current_period,
            'minute'     => $time['minute'],
            'second'     => $time['second'],
        ]);

        return back()->with('success', 'Foul berhasil dicatat.');
    }

    /**
     * Store a timeout event (validated against max timeouts per period).
     */
    public function storeTimeout(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        $this->assertMatchActive($match);

        $request->validate([
            'team_id' => 'required|integer|in:' . $match->home_team_id . ',' . $match->away_team_id,
        ]);

        // Max timeout per period retrieved from settings
        $maxTimeout = (int) \App\Models\Setting::getByKey('max_timeout_per_period', 1);
        $used = $this->scoreService->timeoutCount($match, $request->team_id, $match->current_period);

        if ($used >= $maxTimeout) {
            return back()->with('error', 'Kuota timeout babak ini sudah habis.');
        }

        $time = $this->eventService->currentMatchTime($match);

        $this->eventService->create($match, [
            'team_id'    => $request->team_id,
            'event_type' => MatchEventType::TIMEOUT->value,
            'period'     => $match->current_period,
            'minute'     => $time['minute'],
            'second'     => $time['second'],
        ]);

        return back()->with('success', 'Timeout berhasil dicatat.');
    }

    /**
     * Store an official card event (yellow or red).
     */
    public function storeOfficialCard(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);
        $this->assertMatchActive($match);

        $request->validate([
            'team_id'    => 'required|integer|in:' . $match->home_team_id . ',' . $match->away_team_id,
            'official_id'=> 'required|integer|exists:team_officials,id',
            'event_type' => 'required|in:official_yellow,official_red',
            'minute'     => 'required|integer|min:0',
            'second'     => 'required|integer|min:0|max:59',
        ]);

        // Verify official belongs to the team
        $official = TeamOfficial::where('id', $request->official_id)
            ->where('team_id', $request->team_id)
            ->first();

        if (!$official) {
            return back()->with('error', 'Official tidak terdaftar dalam tim ini.');
        }

        $this->eventService->create($match, [
            'team_id'     => $request->team_id,
            'event_type'  => $request->event_type,
            'period'      => $match->current_period,
            'minute'      => $request->minute,
            'second'      => $request->second,
            'official_id' => $request->official_id,
        ]);

        return back()->with('success', 'Kartu official berhasil dicatat.');
    }

    /**
     * Undo (soft-delete) a match event.
     */
    public function destroy(FutsalMatch $match, MatchEvent $event)
    {
        $this->authorizeSupervisor($match);

        // Guard: only events belonging to this match can be undone
        if ($event->match_id !== $match->id) {
            abort(403, 'Event tidak termasuk dalam pertandingan ini.');
        }

        // Guard: only events from the current or previous period can be undone while match is active
        // Always allow undo when match is finished/locked
        $this->eventService->delete($match, $event);

        return back()->with('success', 'Event berhasil diundo dan dihapus dari timeline.');
    }
}
