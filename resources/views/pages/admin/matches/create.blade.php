@extends('layouts.app')

@section('title', 'Tambah Jadwal Pertandingan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.matches.index', ['competition_id' => $selectedCompetitionId]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tambah Jadwal Pertandingan</h1>
            <p class="mt-1 text-sm text-slate-500">Buat jadwal pertandingan futsal baru.</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <form action="{{ route('admin.matches.store') }}" method="POST" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                
                <div>
                    <label for="competition_id" class="block text-sm font-semibold text-slate-700">Kompetisi</label>
                    <select name="competition_id" id="competition_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Kompetisi --</option>
                        @foreach ($competitions as $comp)
                            <option value="{{ $comp->id }}" {{ old('competition_id', $selectedCompetitionId) == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} ({{ $comp->season }})
                            </option>
                        @endforeach
                    </select>
                    @error('competition_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="round" class="block text-sm font-semibold text-slate-700">Babak / Putaran</label>
                    <input type="text" name="round" id="round" value="{{ old('round', 'Penyisihan Grup') }}" required placeholder="Contoh: Babak 16 Besar"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('round')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="group_name" class="block text-sm font-semibold text-slate-700">Nama Grup (Opsional)</label>
                    <input type="text" name="group_name" id="group_name" value="{{ old('group_name') }}" placeholder="Contoh: Grup A"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('group_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="venue_id" class="block text-sm font-semibold text-slate-700">Venue / Lapangan</label>
                    <select name="venue_id" id="venue_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Venue --</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                {{ $venue->name }} ({{ $venue->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('venue_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="home_team_id" class="block text-sm font-semibold text-slate-700">Tim Home (Tuan Rumah)</label>
                    <select name="home_team_id" id="home_team_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Tim Home --</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" data-competition="{{ $team->competition_id }}" {{ old('home_team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('home_team_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="away_team_id" class="block text-sm font-semibold text-slate-700">Tim Away (Tamu)</label>
                    <select name="away_team_id" id="away_team_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Tim Away --</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" data-competition="{{ $team->competition_id }}" {{ old('away_team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('away_team_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="match_date" class="block text-sm font-semibold text-slate-700">Tanggal Pertandingan</label>
                    <input type="date" name="match_date" id="match_date" value="{{ old('match_date') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('match_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kickoff_time" class="block text-sm font-semibold text-slate-700">Waktu Kickoff (WIB)</label>
                    <input type="time" name="kickoff_time" id="kickoff_time" value="{{ old('kickoff_time', '14:00') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('kickoff_time')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        @foreach (\App\Enums\MatchStatus::cases() as $stat)
                            <option value="{{ $stat->value }}" {{ old('status', \App\Enums\MatchStatus::SCHEDULED->value) === $stat->value ? 'selected' : '' }}>
                                {{ $stat->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="border-t border-slate-200 pt-6 flex justify-end gap-3">
                <a href="{{ route('admin.matches.index', ['competition_id' => $selectedCompetitionId]) }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" :disabled="loading" class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed gap-2">
                    <span x-show="loading" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full" style="display: none;"></span>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const compSelect = document.getElementById('competition_id');
    
    function filterTeams() {
        const compId = compSelect.value;
        ['home_team_id', 'away_team_id'].forEach(selectId => {
            const select = document.getElementById(selectId);
            const options = select.querySelectorAll('option');
            options.forEach(opt => {
                if (!opt.value) return;
                const optCompId = opt.getAttribute('data-competition');
                if (!compId || optCompId === compId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });
            // Reset selected value if it's no longer matching
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value && compId && selectedOpt.getAttribute('data-competition') !== compId) {
                select.value = '';
            }
        });
    }

    compSelect.addEventListener('change', filterTeams);
    filterTeams(); // run on initial load
});
</script>
@endsection
