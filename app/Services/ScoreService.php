<?php

namespace App\Services;

use App\Enums\MatchEventType;
use App\Models\FutsalMatch;
use App\Models\MatchEvent;

class ScoreService
{
    /**
     * Recalculate and persist the score for a match based on all non-deleted events.
     */
    public function recalculate(FutsalMatch $match): void
    {
        $events = MatchEvent::where('match_id', $match->id)
            ->whereIn('event_type', [
                MatchEventType::GOAL->value,
                MatchEventType::OWN_GOAL->value,
                MatchEventType::PENALTY_GOAL->value,
                MatchEventType::SECOND_PENALTY_GOAL->value,
            ])
            ->whereNull('deleted_at')
            ->get();

        $homeScore = 0;
        $awayScore = 0;
        $homeHalfScore = 0;
        $awayHalfScore = 0;

        foreach ($events as $event) {
            $isOwnGoal = $event->event_type === MatchEventType::OWN_GOAL;

            // For own goal: credit goes to the OPPONENT of the team that committed it
            $scoringForHome = ($event->team_id === $match->home_team_id) ? !$isOwnGoal : $isOwnGoal;

            if ($scoringForHome) {
                $homeScore++;
                if ($event->period === 'first_half') {
                    $homeHalfScore++;
                }
            } else {
                $awayScore++;
                if ($event->period === 'first_half') {
                    $awayHalfScore++;
                }
            }
        }

        $match->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'home_first_half_score' => $homeHalfScore,
            'away_first_half_score' => $awayHalfScore,
        ]);
    }

    /**
     * Count fouls for a team in a specific period.
     */
    public function foulCount(FutsalMatch $match, int $teamId, string $period): int
    {
        return MatchEvent::where('match_id', $match->id)
            ->where('team_id', $teamId)
            ->where('event_type', MatchEventType::FOUL->value)
            ->where('period', $period)
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Count timeouts used by a team in a specific period.
     */
    public function timeoutCount(FutsalMatch $match, int $teamId, string $period): int
    {
        return MatchEvent::where('match_id', $match->id)
            ->where('team_id', $teamId)
            ->where('event_type', MatchEventType::TIMEOUT->value)
            ->where('period', $period)
            ->whereNull('deleted_at')
            ->count();
    }
}
