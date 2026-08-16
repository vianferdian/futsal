@extends('layouts.app')

@section('title', 'Detail Tim - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row items-center gap-6 justify-between">
            <div class="flex items-center gap-5 flex-col sm:flex-row text-center sm:text-left">
                @if ($team->logo)
                    <img class="h-20 w-20 rounded-xl object-cover border border-slate-200" src="{{ asset('storage/' . $team->logo) }}" alt="">
                @else
                    <div class="h-20 w-20 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-3xl">
                        {{ strtoupper(substr($team->name, 0, 3)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $team->name }}</h1>
                    <div class="mt-1 text-sm text-slate-500 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                        <span>Singkatan: <strong>{{ $team->short_name }}</strong></span>
                        <span class="hidden sm:inline text-slate-300">•</span>
                        <span>Kota Asal: <strong>{{ $team->city }}</strong></span>
                        <span class="hidden sm:inline text-slate-300">•</span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border {{ $team->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                            {{ $team->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.teams.edit', $team->id) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Edit Tim
                </a>
                <a href="{{ route('admin.teams.index') }}" class="inline-flex items-center rounded-lg border border-transparent px-4 py-2.5 text-sm font-semibold text-slate-500 hover:text-slate-700">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.teams.show', [$team->id, 'tab' => 'overview']) }}" class="border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'overview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Ringkasan
            </a>
            <a href="{{ route('admin.teams.show', [$team->id, 'tab' => 'players']) }}" class="border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'players' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Daftar Pemain ({{ $team->players->count() }})
            </a>
            <a href="{{ route('admin.teams.show', [$team->id, 'tab' => 'officials']) }}" class="border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'officials' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Official Tim ({{ $team->officials->count() }})
            </a>
            <a href="{{ route('admin.teams.show', [$team->id, 'tab' => 'matches']) }}" class="border-b-2 py-4 px-1 text-sm font-medium {{ $tab === 'matches' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                Riwayat Pertandingan
            </a>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div class="mt-6">
        @if ($tab === 'overview')
            <!-- Overview Tab -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Klub -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Informasi Klub</h3>
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="py-3 flex justify-between">
                            <dt class="text-slate-500">Nama Tim</dt>
                            <dd class="font-semibold text-slate-900">{{ $team->name }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-slate-500">Nama Singkat</dt>
                            <dd class="font-semibold text-slate-900">{{ $team->short_name }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-slate-500">Kota Asal</dt>
                            <dd class="font-semibold text-slate-900">{{ $team->city }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-slate-500">Kompetisi Terdaftar</dt>
                            <dd class="font-semibold text-blue-600">
                                @if ($team->competition)
                                    <a href="{{ route('admin.competitions.edit', $team->competition->id) }}" class="hover:underline">
                                        {{ $team->competition->name }} ({{ $team->competition->season }})
                                    </a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Jersey Warna -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Jersey Klub</h3>
                    <div class="flex gap-6 items-center">
                        <div class="text-center space-y-2">
                            <div class="h-16 w-16 rounded-xl border border-slate-300 shadow-xs inline-block" style="background-color: {{ $team->primary_color }}"></div>
                            <div class="text-xs text-slate-500 font-semibold">Jersey Utama</div>
                        </div>
                        <div class="text-center space-y-2">
                            <div class="h-16 w-16 rounded-xl border border-slate-300 shadow-xs inline-block" style="background-color: {{ $team->secondary_color }}"></div>
                            <div class="text-xs text-slate-500 font-semibold">Jersey Kedua</div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif ($tab === 'players')
            <!-- Players Tab -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">Daftar Pemain Futsal</h3>
                    <a href="{{ route('admin.players.create', ['team_id' => $team->id]) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-xs hover:bg-blue-700">
                        Tambah Pemain Baru
                    </a>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
                    @if ($team->players->isEmpty())
                        <div class="p-8 text-center text-slate-500 bg-white">
                            Tidak ada pemain terdaftar di tim ini.
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No. Punggung</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Pemain</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Posisi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                                    <th scope="col" class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($team->players as $player)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900">
                                            # {{ $player->shirt_number }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <div class="flex items-center gap-3">
                                                @if ($player->photo)
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ asset('storage/' . $player->photo) }}" alt="">
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                                        {{ strtoupper(substr($player->name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <a href="{{ route('admin.players.show', $player->id) }}" class="font-bold text-slate-900 hover:text-blue-600">{{ $player->name }}</a>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $player->position->label() }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border {{ $player->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                                {{ $player->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ route('admin.players.show', $player->id) }}" class="text-slate-500 hover:text-slate-900">Detail</a>
                                                <a href="{{ route('admin.players.edit', $player->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        @elseif ($tab === 'officials')
            <!-- Officials Tab -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">Official Tim</h3>
                    <a href="{{ route('admin.officials.create', ['team_id' => $team->id]) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-xs hover:bg-blue-700">
                        Tambah Official Baru
                    </a>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
                    @if ($team->officials->isEmpty())
                        <div class="p-8 text-center text-slate-500 bg-white">
                            Tidak ada official terdaftar di tim ini.
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Official</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jabatan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                                    <th scope="col" class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($team->officials as $official)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <div class="flex items-center gap-3">
                                                @if ($official->photo)
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ asset('storage/' . $official->photo) }}" alt="">
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                        {{ strtoupper(substr($official->name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div class="font-bold text-slate-900">{{ $official->name }}</div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $official->position->label() }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border {{ $official->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                                {{ $official->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <a href="{{ route('admin.officials.edit', $official->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        @elseif ($tab === 'matches')
            <!-- Matches Tab -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-900">Riwayat & Jadwal Pertandingan</h3>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
                    @if ($matches->isEmpty())
                        <div class="p-8 text-center text-slate-500 bg-white">
                            Tidak ada data pertandingan untuk tim ini.
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jadwal / Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Pertandingan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Skor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Venue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($matches as $match)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            <div>{{ $match->match_date->format('d M Y') }}</div>
                                            <div class="text-xs">{{ substr($match->kickoff_time, 0, 5) }} WIB</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                            <span class="{{ $match->home_team_id === $team->id ? 'font-bold text-blue-600' : 'text-slate-900' }}">
                                                {{ $match->homeTeam->name }}
                                            </span>
                                            <span class="text-slate-400 mx-1">vs</span>
                                            <span class="{{ $match->away_team_id === $team->id ? 'font-bold text-blue-600' : 'text-slate-900' }}">
                                                {{ $match->awayTeam->name }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900">
                                            @if (in_array($match->status->value, ['ongoing', 'finished']))
                                                {{ $match->home_score }} - {{ $match->away_score }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $match->venue ? $match->venue->name : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border 
                                                @if($match->status->value === 'finished') bg-blue-50 text-blue-700 border-blue-200
                                                @elseif($match->status->value === 'ongoing') bg-red-50 text-red-700 border-red-200
                                                @else bg-slate-50 text-slate-700 border-slate-200 @endif">
                                                {{ $match->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="border-t border-slate-200 px-6 py-4">
                            {{ $matches->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
