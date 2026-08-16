<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\AuditLog;
use App\Models\Venue;
use App\Models\FutsalMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Venue::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
        }

        $venues = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('pages.admin.venues.index', compact('venues', 'search'));
    }

    public function create()
    {
        return view('pages.admin.venues.create');
    }

    public function store(StoreVenueRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $venue = Venue::create($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat venue baru: ' . $venue->name,
                'subject_type' => 'Venue',
                'subject_id' => $venue->id,
                'new_values' => $venue->only(['name', 'city', 'address', 'capacity', 'status']),
            ]);
        });

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue berhasil ditambahkan.');
    }

    public function edit(Venue $venue)
    {
        return view('pages.admin.venues.edit', compact('venue'));
    }

    public function update(UpdateVenueRequest $request, Venue $venue)
    {
        DB::transaction(function () use ($request, $venue) {
            $data = $request->validated();
            $oldValues = $venue->only(['name', 'city', 'address', 'capacity', 'status']);

            $venue->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah data venue: ' . $venue->name,
                'subject_type' => 'Venue',
                'subject_id' => $venue->id,
                'old_values' => $oldValues,
                'new_values' => $venue->only(['name', 'city', 'address', 'capacity', 'status']),
            ]);
        });

        return redirect()->route('admin.venues.index')
            ->with('success', 'Data venue berhasil diubah.');
    }

    public function destroy(Venue $venue)
    {
        // Check if venue has matches
        $hasMatches = FutsalMatch::where('venue_id', $venue->id)->count() > 0;

        if ($hasMatches) {
            return back()->with('error', 'Venue tidak dapat dihapus karena sudah digunakan dalam pertandingan.');
        }

        DB::transaction(function () use ($venue) {
            $oldValues = $venue->only(['name', 'city']);
            $venue->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus venue: ' . $venue->name,
                'subject_type' => 'Venue',
                'subject_id' => $venue->id,
                'old_values' => $oldValues,
            ]);
        });

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue berhasil dihapus.');
    }
}
