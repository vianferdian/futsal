<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfficialRequest;
use App\Http\Requests\UpdateOfficialRequest;
use App\Models\AuditLog;
use App\Models\TeamOfficial;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OfficialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $teamId = $request->input('team_id');
        
        $query = TeamOfficial::with('team');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $officials = $query->orderBy('name')->paginate(15)->withQueryString();
        $teams = Team::orderBy('name')->get();

        return view('pages.admin.officials.index', compact('officials', 'teams', 'search', 'teamId'));
    }

    public function create(Request $request)
    {
        $selectedTeamId = $request->input('team_id');
        $teams = Team::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.officials.create', compact('teams', 'selectedTeamId'));
    }

    public function store(StoreOfficialRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('officials', 'public');
                $data['photo'] = $path;
            }

            $official = TeamOfficial::create($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat official baru: ' . $official->name . ' (Tim: ' . $official->team->name . ')',
                'subject_type' => 'TeamOfficial',
                'subject_id' => $official->id,
                'new_values' => $official->only(['name', 'position', 'team_id', 'status']),
            ]);
        });

        return redirect()->route('admin.officials.index', ['team_id' => $request->input('team_id')])
            ->with('success', 'Official berhasil ditambahkan.');
    }

    public function edit(TeamOfficial $official)
    {
        $teams = Team::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.officials.edit', compact('official', 'teams'));
    }

    public function update(UpdateOfficialRequest $request, TeamOfficial $official)
    {
        DB::transaction(function () use ($request, $official) {
            $data = $request->validated();
            $oldValues = $official->only(['name', 'position', 'team_id', 'photo', 'status']);

            if ($request->hasFile('photo')) {
                if ($official->photo) {
                    Storage::disk('public')->delete($official->photo);
                }
                $path = $request->file('photo')->store('officials', 'public');
                $data['photo'] = $path;
            }

            $official->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah data official: ' . $official->name,
                'subject_type' => 'TeamOfficial',
                'subject_id' => $official->id,
                'old_values' => $oldValues,
                'new_values' => $official->only(['name', 'position', 'team_id', 'photo', 'status']),
            ]);
        });

        return redirect()->route('admin.officials.index', ['team_id' => $official->team_id])
            ->with('success', 'Data official berhasil diubah.');
    }

    public function destroy(TeamOfficial $official)
    {
        $teamId = $official->team_id;

        DB::transaction(function () use ($official) {
            $oldValues = $official->only(['name', 'position', 'team_id']);

            if ($official->photo) {
                Storage::disk('public')->delete($official->photo);
            }

            $official->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus official: ' . $official->name,
                'subject_type' => 'TeamOfficial',
                'subject_id' => $official->id,
                'old_values' => $oldValues,
            ]);
        });

        return redirect()->route('admin.officials.index', ['team_id' => $teamId])
            ->with('success', 'Official berhasil dihapus.');
    }
}
