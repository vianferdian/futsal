<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompetitionRequest;
use App\Http\Requests\UpdateCompetitionRequest;
use App\Models\AuditLog;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompetitionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Competition::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('season', 'like', "%{$search}%");
        }

        $competitions = $query->orderBy('start_date', 'desc')->paginate(10)->withQueryString();

        return view('pages.admin.competitions.index', compact('competitions', 'search'));
    }

    public function create()
    {
        return view('pages.admin.competitions.create');
    }

    public function store(StoreCompetitionRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('competitions', 'public');
                $data['logo'] = $path;
            }

            $competition = Competition::create($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat kompetisi baru: ' . $competition->name,
                'subject_type' => 'Competition',
                'subject_id' => $competition->id,
                'new_values' => $competition->only(['name', 'short_name', 'season', 'start_date', 'end_date', 'status']),
            ]);
        });

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Kompetisi berhasil ditambahkan.');
    }

    public function edit(Competition $competition)
    {
        return view('pages.admin.competitions.edit', compact('competition'));
    }

    public function update(UpdateCompetitionRequest $request, Competition $competition)
    {
        DB::transaction(function () use ($request, $competition) {
            $data = $request->validated();
            $oldValues = $competition->only(['name', 'short_name', 'season', 'start_date', 'end_date', 'logo', 'status']);

            if ($request->hasFile('logo')) {
                if ($competition->logo) {
                    Storage::disk('public')->delete($competition->logo);
                }
                $path = $request->file('logo')->store('competitions', 'public');
                $data['logo'] = $path;
            }

            $competition->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah data kompetisi: ' . $competition->name,
                'subject_type' => 'Competition',
                'subject_id' => $competition->id,
                'old_values' => $oldValues,
                'new_values' => $competition->only(['name', 'short_name', 'season', 'start_date', 'end_date', 'logo', 'status']),
            ]);
        });

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Data kompetisi berhasil diubah.');
    }

    public function destroy(Competition $competition)
    {
        // Check if competition has teams
        if ($competition->teams()->count() > 0) {
            return back()->with('error', 'Kompetisi tidak dapat dihapus karena sudah memiliki tim terdaftar.');
        }

        DB::transaction(function () use ($competition) {
            $oldValues = $competition->only(['name', 'short_name', 'season']);

            if ($competition->logo) {
                Storage::disk('public')->delete($competition->logo);
            }

            $competition->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus kompetisi: ' . $competition->name,
                'subject_type' => 'Competition',
                'subject_id' => $competition->id,
                'old_values' => $oldValues,
            ]);
        });

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Kompetisi berhasil dihapus.');
    }
}
