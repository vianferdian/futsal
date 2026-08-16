@extends('layouts.app')

@section('title', 'Dashboard Admin Tim')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Admin Tim: {{ $team->name }}</h1>
        <p class="mt-2 text-sm text-slate-500">Kelola pertandingan, jadwal, dan susunan pemain tim Anda.</p>
    </div>

    <!-- Next Match & Recent Matches -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Left: Match History -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Jadwal & Hasil Pertandingan</h2>
            
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
                @if ($matches->isEmpty())
                    <p class="text-sm text-slate-500 text-center py-8">Tim Anda belum memiliki jadwal pertandingan.</p>
                @else
                    <div class="min-w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Match</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lawan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal & Venue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Skor Akhir</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($matches as $match)
                                    @php
                                        $isHome = $match->home_team_id === $team->id;
                                        $opponent = $isHome ? $match->awayTeam : $match->homeTeam;
                                    @endphp
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                            #{{ $match->match_number }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700">
                                                    {{ $isHome ? 'HOME' : 'AWAY' }}
                                                </span>
                                                <span class="font-bold">{{ $opponent->name }}</span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            <div>{{ $match->match_date->format('d M Y') }} • {{ substr($match->kickoff_time, 0, 5) }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $match->venue->name }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-bold text-slate-800">
                                            {{ $match->home_score }} - {{ $match->away_score }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium 
                                                @if($match->status->value === 'first_half' || $match->status->value === 'second_half') bg-red-50 text-red-700 border border-red-200 
                                                @elseif($match->status->value === 'finished' || $match->status->value === 'locked') bg-emerald-50 text-emerald-700 border border-emerald-200
                                                @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                                {{ $match->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200 px-6 py-4">
                        {{ $matches->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Next Match -->
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Pertandingan Berikutnya</h2>
            
            @if (!$nextMatch)
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 bg-white">
                    <p class="text-sm font-semibold">Tidak ada jadwal pertandingan aktif</p>
                    <p class="text-xs text-slate-400 mt-1">Tim Anda belum memiliki jadwal pertandingan terdekat.</p>
                </div>
            @else
                @php
                    $isHomeNext = $nextMatch->home_team_id === $team->id;
                    $opponentNext = $isHomeNext ? $nextMatch->awayTeam : $nextMatch->homeTeam;
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 tracking-wider">NEXT MATCH</span>
                        <span class="inline-flex items-center rounded-md bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 text-xs font-semibold">
                            {{ $nextMatch->status->label() }}
                        </span>
                    </div>

                    <div class="text-center py-4 space-y-2">
                        <div class="text-xs text-slate-400 font-medium">Lawan Tim:</div>
                        <div class="text-lg font-bold text-slate-950 truncate">{{ $opponentNext->name }}</div>
                        <div class="text-xs text-slate-500">({{ $opponentNext->city }})</div>
                    </div>

                    <div class="border-t border-b border-slate-100 py-3 text-sm text-slate-600 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Tanggal</span>
                            <span class="font-semibold text-slate-800">{{ $nextMatch->match_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Kickoff</span>
                            <span class="font-semibold text-slate-800">{{ substr($nextMatch->kickoff_time, 0, 5) }} WIB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Venue</span>
                            <span class="font-semibold text-slate-800 truncate max-w-[150px]">{{ $nextMatch->venue->name }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-slate-400">Status Lineup</span>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold
                                @if($lineupStatus === 'Verified') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($lineupStatus === 'Submitted') bg-blue-50 text-blue-700 border border-blue-200
                                @elseif($lineupStatus === 'Draft') bg-amber-50 text-amber-700 border border-amber-200
                                @else bg-red-50 text-red-700 border border-red-200 @endif">
                                {{ $lineupStatus }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('team.matches.lineup', $nextMatch->id) }}" class="w-full flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                            Atur Susunan Pemain
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
