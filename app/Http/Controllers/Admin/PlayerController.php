<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Models\AuditLog;
use App\Models\Player;
use App\Models\Team;
use App\Models\MatchEvent;
use App\Enums\MatchEventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $teamId = $request->input('team_id');
        
        $query = Player::with('team');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('shirt_number', 'like', "%{$search}%");
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $players = $query->orderBy('name')->paginate(15)->withQueryString();
        $teams = Team::orderBy('name')->get();

        return view('pages.admin.players.index', compact('players', 'teams', 'search', 'teamId'));
    }

    public function create(Request $request)
    {
        $selectedTeamId = $request->input('team_id');
        $teams = Team::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.players.create', compact('teams', 'selectedTeamId'));
    }

    public function store(StorePlayerRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('players', 'public');
                $data['photo'] = $path;
            }

            $player = Player::create($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat pemain baru: ' . $player->name . ' (Tim: ' . $player->team->name . ')',
                'subject_type' => 'Player',
                'subject_id' => $player->id,
                'new_values' => $player->only(['name', 'shirt_number', 'position', 'team_id', 'status']),
            ]);
        });

        return redirect()->route('admin.players.index', ['team_id' => $request->input('team_id')])
            ->with('success', 'Pemain berhasil ditambahkan.');
    }

    public function show(Player $player)
    {
        $player->load('team');
        
        // Count goals, yellow, and red cards from match events
        $stats = [
            'goals' => MatchEvent::where('player_id', $player->id)
                ->whereIn('event_type', [MatchEventType::GOAL, MatchEventType::PENALTY_GOAL, MatchEventType::SECOND_PENALTY_GOAL])
                ->count(),
            'yellow_cards' => MatchEvent::where('player_id', $player->id)
                ->where('event_type', MatchEventType::YELLOW_CARD)
                ->count(),
            'red_cards' => MatchEvent::where('player_id', $player->id)
                ->where('event_type', MatchEventType::RED_CARD)
                ->count(),
        ];

        return view('pages.admin.players.show', compact('player', 'stats'));
    }

    public function edit(Player $player)
    {
        $teams = Team::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.players.edit', compact('player', 'teams'));
    }

    public function update(UpdatePlayerRequest $request, Player $player)
    {
        DB::transaction(function () use ($request, $player) {
            $data = $request->validated();
            $oldValues = $player->only(['name', 'shirt_number', 'position', 'team_id', 'photo', 'status']);

            if ($request->hasFile('photo')) {
                if ($player->photo) {
                    Storage::disk('public')->delete($player->photo);
                }
                $path = $request->file('photo')->store('players', 'public');
                $data['photo'] = $path;
            }

            $player->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah data pemain: ' . $player->name,
                'subject_type' => 'Player',
                'subject_id' => $player->id,
                'old_values' => $oldValues,
                'new_values' => $player->only(['name', 'shirt_number', 'position', 'team_id', 'photo', 'status']),
            ]);
        });

        return redirect()->route('admin.players.index', ['team_id' => $player->team_id])
            ->with('success', 'Data pemain berhasil diubah.');
    }

    public function destroy(Player $player)
    {
        $teamId = $player->team_id;

        DB::transaction(function () use ($player) {
            $oldValues = $player->only(['name', 'shirt_number', 'team_id']);

            if ($player->photo) {
                Storage::disk('public')->delete($player->photo);
            }

            $player->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus pemain: ' . $player->name,
                'subject_type' => 'Player',
                'subject_id' => $player->id,
                'old_values' => $oldValues,
            ]);
        });

        return redirect()->route('admin.players.index', ['team_id' => $teamId])
            ->with('success', 'Pemain berhasil dihapus.');
    }
}
