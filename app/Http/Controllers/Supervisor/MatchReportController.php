<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\MatchCondition;
use App\Enums\MatchStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FutsalMatch;
use App\Models\MatchEvent;
use App\Models\MatchReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchReportController extends Controller
{
    private function authorizeSupervisor(FutsalMatch $match): void
    {
        if (!$match->supervisors()->where('users.id', auth()->id())->exists()) {
            abort(403, 'Anda tidak ditugaskan sebagai pengawas di pertandingan ini.');
        }
    }

    /**
     * Show the post-match report form & summary.
     */
    public function show(FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);

        if (!in_array($match->status->value, ['finished', 'locked'])) {
            return redirect()
                ->route('supervisor.matches.workspace', $match->id)
                ->with('error', 'Laporan pascapertandingan hanya tersedia setelah pertandingan selesai.');
        }

        $match->load(['homeTeam', 'awayTeam', 'venue', 'competition']);

        $report = MatchReport::firstOrNew(['match_id' => $match->id]);

        // Build event summary for each team
        $goalTypes = ['goal', 'own_goal', 'penalty_goal', 'second_penalty_goal'];

        $events = MatchEvent::with(['player', 'official', 'team'])
            ->where('match_id', $match->id)
            ->whereNull('deleted_at')
            ->orderBy('period')
            ->orderBy('minute')
            ->orderBy('second')
            ->get();

        $homeGoals = $events->filter(fn($e) =>
            in_array($e->event_type->value, ['goal', 'penalty_goal', 'second_penalty_goal']) && $e->team_id === $match->home_team_id
            || ($e->event_type->value === 'own_goal' && $e->team_id === $match->away_team_id)
        );
        $awayGoals = $events->filter(fn($e) =>
            in_array($e->event_type->value, ['goal', 'penalty_goal', 'second_penalty_goal']) && $e->team_id === $match->away_team_id
            || ($e->event_type->value === 'own_goal' && $e->team_id === $match->home_team_id)
        );

        $homeCards = $events->where('team_id', $match->home_team_id)
            ->whereIn('event_type.value', ['yellow_card', 'second_yellow', 'red_card']);
        $awayCards = $events->where('team_id', $match->away_team_id)
            ->whereIn('event_type.value', ['yellow_card', 'second_yellow', 'red_card']);

        $homeFoulsB1 = $events->where('team_id', $match->home_team_id)->where('event_type.value', 'foul')->where('period', 'first_half')->count();
        $homeFoulsB2 = $events->where('team_id', $match->home_team_id)->where('event_type.value', 'foul')->where('period', 'second_half')->count();
        $awayFoulsB1 = $events->where('team_id', $match->away_team_id)->where('event_type.value', 'foul')->where('period', 'first_half')->count();
        $awayFoulsB2 = $events->where('team_id', $match->away_team_id)->where('event_type.value', 'foul')->where('period', 'second_half')->count();

        $allCards = $events->filter(fn($e) => in_array($e->event_type->value, ['yellow_card', 'second_yellow', 'red_card', 'official_yellow', 'official_red']));

        return view('pages.supervisor.report.show', compact(
            'match', 'report', 'events',
            'homeGoals', 'awayGoals',
            'homeFoulsB1', 'homeFoulsB2', 'awayFoulsB1', 'awayFoulsB2',
            'allCards'
        ));
    }

    /**
     * Save (upsert) the post-match report. Can be saved multiple times until locked.
     */
    public function save(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);

        if (!in_array($match->status->value, ['finished', 'locked'])) {
            return back()->with('error', 'Pertandingan belum selesai.');
        }

        // Don't allow editing locked report
        $existing = MatchReport::where('match_id', $match->id)->first();
        if ($existing && $existing->locked_at) {
            return back()->with('error', 'Laporan sudah dikunci dan tidak dapat diubah.');
        }

        $request->validate([
            'attendance'          => 'nullable|integer|min:0',
            'match_condition'     => 'required|in:normal,delayed,interrupted,abandoned',
            'violation_potential' => 'nullable|boolean',
            'violation_notes'     => 'nullable|string|max:2000',
            'supervisor_notes'    => 'nullable|string|max:3000',
        ]);

        DB::transaction(function () use ($request, $match, $existing) {
            $data = [
                'match_id'            => $match->id,
                'attendance'          => $request->attendance,
                'match_condition'     => $request->match_condition,
                'violation_potential' => $request->boolean('violation_potential'),
                'violation_notes'     => $request->violation_notes,
                'supervisor_notes'    => $request->supervisor_notes,
            ];

            $report = MatchReport::updateOrCreate(
                ['match_id' => $match->id],
                $data
            );

            AuditLog::create([
                'user_id'      => auth()->id(),
                'match_id'     => $match->id,
                'action'       => 'Menyimpan laporan pascapertandingan #' . $match->match_number,
                'subject_type' => 'MatchReport',
                'subject_id'   => $report->id,
                'ip_address'   => request()->ip(),
            ]);
        });

        return back()->with('success', 'Laporan berhasil disimpan.');
    }

    /**
     * Submit & lock the report permanently.
     */
    public function submit(Request $request, FutsalMatch $match)
    {
        $this->authorizeSupervisor($match);

        $report = MatchReport::where('match_id', $match->id)->first();

        if (!$report) {
            return back()->with('error', 'Simpan laporan terlebih dahulu sebelum mengunci.');
        }

        if ($report->locked_at) {
            return back()->with('error', 'Laporan sudah terkunci.');
        }

        DB::transaction(function () use ($report, $match) {
            $report->update([
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'locked_at'    => now(),
            ]);

            // Also lock the match
            $match->update([
                'status'    => MatchStatus::LOCKED,
                'locked_at' => now(),
            ]);

            AuditLog::create([
                'user_id'      => auth()->id(),
                'match_id'     => $match->id,
                'action'       => 'Mengunci laporan dan pertandingan #' . $match->match_number,
                'subject_type' => 'MatchReport',
                'subject_id'   => $report->id,
                'ip_address'   => request()->ip(),
            ]);
        });

        return redirect()
            ->route('supervisor.matches.report', $match->id)
            ->with('success', 'Laporan berhasil dikunci. Pertandingan sekarang berstatus TERKUNCI.');
    }
}
