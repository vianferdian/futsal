@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Administrator</h1>
        <p class="mt-2 text-sm text-slate-500">Selamat datang kembali di panel kontrol utama.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Total Tim</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['total_teams'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Total Pemain</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['total_players'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Pertandingan Hari Ini</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['matches_today'] }}</dd>
            <div class="mt-1 flex gap-2 text-xs">
                <span class="text-red-600 font-semibold">{{ $stats['live_matches'] }} Live</span>
                <span class="text-slate-400">•</span>
                <span class="text-slate-600">{{ $stats['finished_matches'] }} Finished</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Total Kompetisi</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['total_competitions'] }}</dd>
        </div>

    </div>

    <!-- Today's Matches & Recent Activity -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Left: Today's Matches -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Pertandingan Hari Ini</h2>
            
            @if ($todayMatches->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 bg-white">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-4 text-sm font-semibold">Tidak ada pertandingan hari ini</p>
                    <p class="mt-1 text-xs text-slate-400">Jadwal pertandingan hari ini belum tersedia.</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($todayMatches as $match)
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-semibold text-slate-500">MATCH #{{ $match->match_number }}</span>
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium 
                                        @if($match->status->value === 'first_half' || $match->status->value === 'second_half') bg-red-50 text-red-700 border border-red-200 animate-pulse 
                                        @elseif($match->status->value === 'finished' || $match->status->value === 'locked') bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                        {{ $match->status->label() }}
                                    </span>
                                </div>
                                <div class="space-y-2 my-4">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-slate-900 truncate">{{ $match->homeTeam->name }}</span>
                                        <span class="font-mono font-bold text-lg text-slate-800">{{ $match->home_score }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-slate-900 truncate">{{ $match->awayTeam->name }}</span>
                                        <span class="font-mono font-bold text-lg text-slate-800">{{ $match->away_score }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-slate-100 pt-3 text-xs text-slate-500 space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span>⏰ {{ substr($match->kickoff_time, 0, 5) }} WIB</span>
                                        <span>•</span>
                                        <span>📍 {{ $match->venue->name }}</span>
                                    </div>
                                    @if ($match->supervisors->isNotEmpty())
                                        <div>Pengawas: <span class="font-medium text-slate-700">{{ $match->supervisors->first()->name }}</span></div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                                @if(in_array($match->status->value, ['finished', 'locked']))
                                    <a href="{{ route('matches.summary', $match->id) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
                                        📊 Ringkasan
                                    </a>
                                @else
                                    <div></div>
                                @endif
                                <a href="{{ route('admin.matches.show', $match->id) }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700">
                                    Buka Detail
                                    <svg class="ml-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Recent Activity -->
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Aktivitas Terkini</h2>
            
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                @if ($recentActivities->isEmpty())
                    <p class="text-sm text-slate-500 text-center py-6">Belum ada aktivitas tercatat.</p>
                @else
                    <ul class="space-y-4">
                        @foreach ($recentActivities as $log)
                            <li class="flex items-start gap-3">
                                <div class="flex-none mt-1 h-2.5 w-2.5 rounded-full bg-blue-600"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-800 font-medium leading-tight">
                                        {{ $log->action }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                        <span>{{ $log->user ? $log->user->name : 'System' }}</span>
                                        <span>•</span>
                                        <span>{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
