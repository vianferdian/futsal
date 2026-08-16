@extends('layouts.app')

@section('title', 'Ringkasan Pertandingan - #' . $match->match_number)

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="rounded-xl border border-slate-200 bg-linear-to-r from-slate-900 to-slate-800 p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    {{ $match->competition->name }} — {{ $match->round }}
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight">Ringkasan Pertandingan #{{ $match->match_number }}</h1>
                <p class="text-xs text-slate-300">Pertandingan diselenggarakan pada {{ $match->match_date->format('d F Y') }} di {{ $match->venue->name }}, {{ $match->venue->city }}</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('matches.summary.pdf', $match->id) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700 transition-colors shadow-xs gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Match Status Banner -->
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="inline-block h-2 w-2 rounded-full @if($match->status->value === 'locked') bg-emerald-500 @else bg-yellow-500 @endif"></span>
            <span class="text-xs font-semibold text-slate-700">Status Pertandingan: <strong class="text-slate-900">{{ $match->status->label() }}</strong></span>
        </div>
        <div class="text-xs text-slate-500">
            Pengawas Pertandingan: 
            <strong>
                {{ $match->supervisors->pluck('name')->join(', ') ?: 'Belum ditugaskan' }}
            </strong>
        </div>
    </div>

    <!-- Scoreboard and Core Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Home Team -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs flex flex-col items-center justify-center text-center space-y-3">
            <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-lg border border-slate-200">
                {{ substr($match->homeTeam->name, 0, 2) }}
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-900">{{ $match->homeTeam->name }}</h3>
                <p class="text-xs text-slate-400">HOME TEAM</p>
            </div>
            @if ($homeJersey)
                <div class="flex gap-2">
                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 gap-1.5">
                        <span class="h-2 w-2 rounded-full border border-slate-300" style="background-color: {{ $homeJersey->player_jersey_color }}"></span>
                        Pemain
                    </span>
                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 gap-1.5">
                        <span class="h-2 w-2 rounded-full border border-slate-300" style="background-color: {{ $homeJersey->goalkeeper_jersey_color }}"></span>
                        Kiper
                    </span>
                </div>
            @endif
        </div>

        <!-- Scores & Details -->
        <div class="rounded-xl border border-slate-200 bg-slate-900 p-6 text-white shadow-xs flex flex-col items-center justify-center text-center space-y-4">
            <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">HASIL AKHIR</span>
            <div class="text-5xl font-extrabold tracking-widest">{{ $match->home_score }} - {{ $match->away_score }}</div>
            <div class="text-xs text-slate-300">
                Babak Pertama: <strong class="text-white">{{ $match->home_first_half_score }} - {{ $match->away_first_half_score }}</strong>
            </div>
        </div>

        <!-- Away Team -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs flex flex-col items-center justify-center text-center space-y-3">
            <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-lg border border-slate-200">
                {{ substr($match->awayTeam->name, 0, 2) }}
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-900">{{ $match->awayTeam->name }}</h3>
                <p class="text-xs text-slate-400">AWAY TEAM</p>
            </div>
            @if ($awayJersey)
                <div class="flex gap-2">
                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 gap-1.5">
                        <span class="h-2 w-2 rounded-full border border-slate-300" style="background-color: {{ $awayJersey->player_jersey_color }}"></span>
                        Pemain
                    </span>
                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 gap-1.5">
                        <span class="h-2 w-2 rounded-full border border-slate-300" style="background-color: {{ $awayJersey->goalkeeper_jersey_color }}"></span>
                        Kiper
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Match Statistics (Fouls and Timeouts) -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Statistik Pertandingan</h3>
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Akumulasi Pelanggaran (Foul)</h4>
            <div class="overflow-hidden rounded-lg border border-slate-100">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">Tim</th>
                            <th class="px-4 py-2 text-center font-medium">Babak 1</th>
                            <th class="px-4 py-2 text-center font-medium">Babak 2</th>
                            <th class="px-4 py-2 text-center font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $match->homeTeam->name }}</td>
                            <td class="px-4 py-3 text-center {{ $homeFoulsB1 >= 5 ? 'text-red-600 font-bold' : '' }}">{{ $homeFoulsB1 }}</td>
                            <td class="px-4 py-3 text-center {{ $homeFoulsB2 >= 5 ? 'text-red-600 font-bold' : '' }}">{{ $homeFoulsB2 }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $homeFoulsB1 + $homeFoulsB2 }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $match->awayTeam->name }}</td>
                            <td class="px-4 py-3 text-center {{ $awayFoulsB1 >= 5 ? 'text-red-600 font-bold' : '' }}">{{ $awayFoulsB1 }}</td>
                            <td class="px-4 py-3 text-center {{ $awayFoulsB2 >= 5 ? 'text-red-600 font-bold' : '' }}">{{ $awayFoulsB2 }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $awayFoulsB1 + $awayFoulsB2 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Match Timeline Events -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Kejadian Penting Pertandingan (Timeline)</h3>
        
        @if($events->isEmpty())
            <p class="text-xs text-slate-400 text-center py-6">Tidak ada kejadian penting yang tercatat.</p>
        @else
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($events as $event)
                        @php
                            $isHome = $event->team_id === $match->home_team_id;
                            $icon = match($event->event_type->value) {
                                'goal', 'penalty_goal', 'second_penalty_goal' => '⚽',
                                'own_goal' => '🙈',
                                'penalty_miss' => '❌',
                                'yellow_card' => '🟨',
                                'second_yellow' => '🟨🟨',
                                'red_card' => '🟥',
                                'foul' => '⚠️',
                                'timeout' => '⏱️',
                                'official_yellow' => '📋🟨',
                                'official_red' => '📋🟥',
                                default => '•',
                            };
                            $goalTypes = ['goal', 'own_goal', 'penalty_goal', 'second_penalty_goal'];
                            $isGoal = in_array($event->event_type->value, $goalTypes);
                            $periodLabel = $event->period === 'first_half' ? 'Babak 1' : 'Babak 2';
                        @endphp
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-sm shadow-xs">
                                            {{ $icon }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-xs text-slate-800">
                                                <strong class="font-semibold text-slate-900">{{ $event->event_type->label() }}</strong> 
                                                untuk <strong class="font-semibold text-slate-900">{{ $event->team->name }}</strong>
                                                @if($event->player)
                                                    — #{{ $event->player->shirt_number }} {{ $event->player->name }}
                                                @endif
                                                @if($event->relatedPlayer)
                                                    (Assist: {{ $event->relatedPlayer->name }})
                                                @endif
                                                @if($event->official)
                                                    — Official: {{ $event->official->name }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right text-xs whitespace-nowrap text-slate-500 font-mono">
                                            <span>{{ $periodLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Match Roster & Lineups -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Home Team Roster -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Lineup {{ $match->homeTeam->name }}</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pemain Utama (Starting 5)</h4>
                    <div class="space-y-1.5">
                        @if ($homeLineup)
                            @forelse ($homeLineup->players->where('playing_status.value', 'playing') as $lp)
                                <div class="flex items-center justify-between text-xs py-1 px-2 bg-slate-50 rounded">
                                    <span class="font-medium text-slate-800">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        {{ $lp->position->label() }}
                                        @if ($lp->is_captain) <span class="text-blue-600 font-bold ml-1">(C)</span> @endif
                                        @if ($lp->is_goalkeeper) <span class="text-emerald-600 font-bold ml-1">(GK)</span> @endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Tidak ada data pemain utama.</p>
                            @endforelse
                        @else
                            <p class="text-xs text-slate-400">Lineup belum dibuat.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pemain Cadangan (Substitutes)</h4>
                    <div class="space-y-1.5">
                        @if ($homeLineup)
                            @forelse ($homeLineup->players->where('playing_status.value', 'substitute') as $lp)
                                <div class="flex items-center justify-between text-xs py-1 px-2 bg-slate-50/50 rounded">
                                    <span class="font-medium text-slate-700">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</span>
                                    <span class="text-[10px] font-medium text-slate-400">
                                        {{ $lp->position->label() }}
                                        @if ($lp->is_captain) <span class="text-blue-600 font-bold ml-1">(C)</span> @endif
                                        @if ($lp->is_goalkeeper) <span class="text-emerald-600 font-bold ml-1">(GK)</span> @endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Tidak ada data pemain cadangan.</p>
                            @endforelse
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Official Tim</h4>
                    <div class="space-y-1">
                        @forelse ($homeOfficials as $off)
                            <div class="text-xs py-1 text-slate-700">
                                <strong>{{ $off->name }}</strong> <span class="text-slate-400">({{ $off->position->label() }})</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Tidak ada data official.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Away Team Roster -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Lineup {{ $match->awayTeam->name }}</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pemain Utama (Starting 5)</h4>
                    <div class="space-y-1.5">
                        @if ($awayLineup)
                            @forelse ($awayLineup->players->where('playing_status.value', 'playing') as $lp)
                                <div class="flex items-center justify-between text-xs py-1 px-2 bg-slate-50 rounded">
                                    <span class="font-medium text-slate-800">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        {{ $lp->position->label() }}
                                        @if ($lp->is_captain) <span class="text-blue-600 font-bold ml-1">(C)</span> @endif
                                        @if ($lp->is_goalkeeper) <span class="text-emerald-600 font-bold ml-1">(GK)</span> @endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Tidak ada data pemain utama.</p>
                            @endforelse
                        @else
                            <p class="text-xs text-slate-400">Lineup belum dibuat.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pemain Cadangan (Substitutes)</h4>
                    <div class="space-y-1.5">
                        @if ($awayLineup)
                            @forelse ($awayLineup->players->where('playing_status.value', 'substitute') as $lp)
                                <div class="flex items-center justify-between text-xs py-1 px-2 bg-slate-50/50 rounded">
                                    <span class="font-medium text-slate-700">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</span>
                                    <span class="text-[10px] font-medium text-slate-400">
                                        {{ $lp->position->label() }}
                                        @if ($lp->is_captain) <span class="text-blue-600 font-bold ml-1">(C)</span> @endif
                                        @if ($lp->is_goalkeeper) <span class="text-emerald-600 font-bold ml-1">(GK)</span> @endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Tidak ada data pemain cadangan.</p>
                            @endforelse
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Official Tim</h4>
                    <div class="space-y-1">
                        @forelse ($awayOfficials as $off)
                            <div class="text-xs py-1 text-slate-700">
                                <strong>{{ $off->name }}</strong> <span class="text-slate-400">({{ $off->position->label() }})</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Tidak ada data official.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post-Match Report Summary -->
    @if ($report)
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Catatan Pengawas & Kondisi Lapangan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-slate-700">
                <div class="space-y-2">
                    <div class="flex justify-between border-b border-slate-50 pb-1.5">
                        <span class="text-slate-500">Kondisi Pertandingan</span>
                        <span class="font-semibold text-slate-900">{{ $report->match_condition->label() }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-50 pb-1.5">
                        <span class="text-slate-500">Jumlah Penonton</span>
                        <span class="font-semibold text-slate-900">{{ $report->attendance ? number_format($report->attendance) . ' orang' : 'Tidak dicatat' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-50 pb-1.5">
                        <span class="text-slate-500">Potensi Pelanggaran</span>
                        <span class="font-bold {{ $report->violation_potential ? 'text-red-600' : 'text-slate-900' }}">
                            {{ $report->violation_potential ? 'Ya' : 'Tidak' }}
                        </span>
                    </div>
                </div>
                <div class="md:col-span-2 space-y-3">
                    @if ($report->violation_potential && $report->violation_notes)
                        <div class="bg-red-50/50 border border-red-100 rounded-lg p-3">
                            <span class="text-[10px] font-bold text-red-600 uppercase tracking-widest block mb-1">Catatan Insiden / Pelanggaran Keamanan</span>
                            <p class="text-slate-700 leading-relaxed">{{ $report->violation_notes }}</p>
                        </div>
                    @endif
                    @if ($report->supervisor_notes)
                        <div class="bg-slate-50 border border-slate-100 rounded-lg p-3">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Ulasan Pengawas Pertandingan</span>
                            <p class="text-slate-700 leading-relaxed">{{ $report->supervisor_notes }}</p>
                        </div>
                    @endif
                    <div class="text-[10px] text-slate-400 text-right">
                        Dilaporkan oleh: <strong class="text-slate-500">{{ $report->submittedByUser?->name ?? 'Pengawas' }}</strong> pada {{ $report->submitted_at?->format('d M Y H:i') ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
