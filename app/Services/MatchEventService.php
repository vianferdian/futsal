<?php

namespace App\Services;

use App\Enums\MatchEventType;
use App\Models\AuditLog;
use App\Models\FutsalMatch;
use App\Models\MatchEvent;
use Illuminate\Support\Facades\DB;

class MatchEventService
{
    public function __construct(private ScoreService $scoreService) {}

    /**
     * Create a new match event inside a transaction with audit logging and score recalculation.
     */
    public function create(FutsalMatch $match, array $data): MatchEvent
    {
        return DB::transaction(function () use ($match, $data) {
            $event = MatchEvent::create(array_merge($data, [
                'match_id' => $match->id,
                'created_by' => auth()->id(),
            ]));

            // Recalculate score for any goal-type events
            $goalTypes = [
                MatchEventType::GOAL,
                MatchEventType::OWN_GOAL,
                MatchEventType::PENALTY_GOAL,
                MatchEventType::SECOND_PENALTY_GOAL,
            ];

            if (in_array($event->event_type, $goalTypes)) {
                $this->scoreService->recalculate($match);
            }

            AuditLog::create([
                'user_id'      => auth()->id(),
                'match_id'     => $match->id,
                'action'       => 'Mencatat event ' . $event->event_type->label() . ' di pertandingan #' . $match->match_number
                    . ' menit ' . $event->minute . ':' . str_pad($event->second, 2, '0', STR_PAD_LEFT),
                'subject_type' => 'MatchEvent',
                'subject_id'   => $event->id,
                'ip_address'   => request()->ip(),
            ]);

            return $event;
        });
    }

    /**
     * Soft-delete a match event and recalculate scores if it was a goal.
     */
    public function delete(FutsalMatch $match, MatchEvent $event): void
    {
        DB::transaction(function () use ($match, $event) {
            $eventType = $event->event_type;

            AuditLog::create([
                'user_id'      => auth()->id(),
                'match_id'     => $match->id,
                'action'       => 'Menghapus event ' . $eventType->label() . ' di pertandingan #' . $match->match_number,
                'subject_type' => 'MatchEvent',
                'subject_id'   => $event->id,
                'ip_address'   => request()->ip(),
            ]);

            $event->delete(); // soft delete

            $goalTypes = [
                MatchEventType::GOAL,
                MatchEventType::OWN_GOAL,
                MatchEventType::PENALTY_GOAL,
                MatchEventType::SECOND_PENALTY_GOAL,
            ];

            if (in_array($eventType, $goalTypes)) {
                $this->scoreService->recalculate($match);
            }
        });
    }

    /**
     * Get current elapsed time string from match timer data.
     */
    public function currentMatchTime(FutsalMatch $match): array
    {
        $elapsed = $match->elapsed_seconds;
        if ($match->timer_status === 'running' && $match->timer_started_at) {
            $elapsed += abs(now()->diffInSeconds($match->timer_started_at));
        }

        $minutes = intdiv($elapsed, 60);
        $seconds = $elapsed % 60;

        return ['minute' => $minutes, 'second' => $seconds];
    }
}
