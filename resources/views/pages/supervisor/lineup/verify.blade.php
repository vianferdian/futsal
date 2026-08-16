@extends('layouts.app')

@section('title', 'Verifikasi Susunan Pemain - #' . $match->match_number)

@section('content')
@php
    $basicColors = [
        '#ef4444' => 'Merah',
        '#3b82f6' => 'Biru',
        '#22c55e' => 'Hijau',
        '#eab308' => 'Kuning',
        '#ffffff' => 'Putih',
        '#000000' => 'Hitam',
        '#f97316' => 'Oranye',
        '#a855f7' => 'Ungu',
        '#ec4899' => 'Pink',
        '#64748b' => 'Abu-abu'
    ];
@endphp
<div class="space-y-6" x-data="{ openUnlockModal: false, unlockTeamId: '', unlockTeamName: '', unlockActionUrl: '' }">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('supervisor.dashboard') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Verifikasi Lineup & Jersey</h1>
                <p class="mt-1 text-sm text-slate-500">Pertandingan: {{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold border 
                @if($match->status->value === 'ready') bg-emerald-50 text-emerald-700 border-emerald-200
                @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                Status Pertandingan: {{ $match->status->label() }}
            </span>
        </div>
    </div>

    @if ($match->status->value === 'ready' || in_array($match->status->value, ['first_half', 'halftime', 'second_half', 'finished', 'locked']))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-900">Kedua Lineup & Jersey Telah Diverifikasi</h3>
                    <p class="text-xs text-emerald-700">Pertandingan siap dimulai atau sedang berjalan.</p>
                </div>
            </div>
            <div>
                <a href="{{ route('supervisor.matches.workspace', $match->id) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition-colors shadow-xs gap-1.5">
                    Masuk ke Workspace Pertandingan
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    @endif

    <!-- Match Visualizer Header -->
    <div class="rounded-xl border border-slate-200 bg-linear-to-r from-slate-900 to-slate-800 p-6 text-white shadow-xs">
        <div class="flex flex-col sm:flex-row justify-between items-center text-center gap-4">
            <div class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                {{ $match->competition->name }} — {{ $match->round }}
            </div>
            <div class="text-sm text-slate-300 flex gap-4">
                <span>{{ $match->match_date->format('d M Y') }}</span>
                <span>{{ substr($match->kickoff_time, 0, 5) }} WIB</span>
                <span>{{ $match->venue->name }}</span>
            </div>
        </div>
    </div>

    <!-- Team Lineups Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Loop for Home & Away Teams -->
        @foreach ([
            ['type' => 'Home', 'team' => $match->homeTeam, 'lineup' => $homeLineup, 'jersey' => $homeJersey],
            ['type' => 'Away', 'team' => $match->awayTeam, 'lineup' => $awayLineup, 'jersey' => $awayJersey]
        ] as $item)
            @php
                $team = $item['team'];
                $lineup = $item['lineup'];
                $jersey = $item['jersey'];
                $type = $item['type'];
            @endphp
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase block">{{ $type }} TEAM</span>
                        <h2 class="text-lg font-bold text-slate-900">{{ $team->name }}</h2>
                    </div>
                    <div>
                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold border
                            @if(!$lineup || $lineup->status === 'draft') bg-amber-50 text-amber-700 border-amber-100
                            @elseif($lineup->status === 'submitted') bg-blue-50 text-blue-700 border-blue-100
                            @else bg-emerald-50 text-emerald-700 border-emerald-100 @endif">
                            {{ $lineup ? ucfirst($lineup->status) : 'Draft' }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 flex-1 space-y-6">
                    <!-- Jersey Preview -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Warna Jersey & Perlengkapan</h3>
                        <form action="{{ route('supervisor.matches.verify-lineup.jersey', [$match->id, $team->id]) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <!-- Players Jersey Colors -->
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-100 space-y-2">
                                    <div class="font-bold text-slate-700">Jersey Pemain</div>
                                    <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                        <span>Baju</span>
                                        <select name="player_jersey_color" class="h-8 w-24 rounded-md border border-slate-300 px-1 py-0.5 text-xs">
                                            @foreach($basicColors as $hex => $name)
                                                <option value="{{ $hex }}" {{ (old('player_jersey_color', $jersey->player_jersey_color ?? '#3b82f6') == $hex) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                        <span>Celana</span>
                                        <select name="player_short_color" class="h-8 w-24 rounded-md border border-slate-300 px-1 py-0.5 text-xs">
                                            @foreach($basicColors as $hex => $name)
                                                <option value="{{ $hex }}" {{ (old('player_short_color', $jersey->player_short_color ?? '#ffffff') == $hex) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                        <span>Kaos Kaki</span>
                                        <select name="player_socks_color" class="h-8 w-24 rounded-md border border-slate-300 px-1 py-0.5 text-xs">
                                            @foreach($basicColors as $hex => $name)
                                                <option value="{{ $hex }}" {{ (old('player_socks_color', $jersey->player_socks_color ?? '#ffffff') == $hex) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- GK Jersey Colors -->
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-100 space-y-2">
                                    <div class="font-bold text-slate-700">Jersey Kiper</div>
                                    <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                        <span>Baju</span>
                                        <select name="goalkeeper_jersey_color" class="h-8 w-24 rounded-md border border-slate-300 px-1 py-0.5 text-xs">
                                            @foreach($basicColors as $hex => $name)
                                                <option value="{{ $hex }}" {{ (old('goalkeeper_jersey_color', $jersey->goalkeeper_jersey_color ?? '#f59e0b') == $hex) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                        <span>Celana</span>
                                        <select name="goalkeeper_short_color" class="h-8 w-24 rounded-md border border-slate-300 px-1 py-0.5 text-xs">
                                            @foreach($basicColors as $hex => $name)
                                                <option value="{{ $hex }}" {{ (old('goalkeeper_short_color', $jersey->goalkeeper_short_color ?? '#ffffff') == $hex) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                        <span>Kaos Kaki</span>
                                        <select name="goalkeeper_socks_color" class="h-8 w-24 rounded-md border border-slate-300 px-1 py-0.5 text-xs">
                                            @foreach($basicColors as $hex => $name)
                                                <option value="{{ $hex }}" {{ (old('goalkeeper_socks_color', $jersey->goalkeeper_socks_color ?? '#ffffff') == $hex) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @if(!in_array($match->status->value, ['finished', 'locked']))
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-slate-800 text-white px-3 py-1.5 text-xs font-bold hover:bg-slate-900 transition-colors shadow-xs">
                                        Simpan Warna Jersey
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Lineup Players List -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Susunan Pemain</h3>
                        @if (!$lineup || $lineup->players->isEmpty())
                            <div class="text-center py-6 text-sm text-slate-400 border border-dashed border-slate-200 rounded-lg bg-slate-50">
                                Lineup belum disusun oleh Admin Tim.
                            </div>
                        @else
                            <div class="rounded-lg border border-slate-200 overflow-hidden divide-y divide-slate-100">
                                @foreach ($lineup->players as $lp)
                                    <div class="flex items-center justify-between p-3 text-xs bg-white hover:bg-slate-50">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center font-bold text-slate-700 bg-slate-100 rounded-md px-1.5 py-0.5">
                                                {{ $lp->player->shirt_number }}
                                            </span>
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    {{ $lp->player->name }}
                                                    @if($lp->is_captain)
                                                        <span class="inline-flex items-center rounded-sm bg-blue-100 text-blue-800 text-[10px] font-bold px-1.5">CAPTAIN</span>
                                                    @endif
                                                    @if($lp->is_goalkeeper)
                                                        <span class="inline-flex items-center rounded-sm bg-yellow-100 text-yellow-800 text-[10px] font-bold px-1.5">GK</span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-slate-400">{{ $lp->position->label() }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center rounded-sm px-1.5 py-0.5 text-[10px] font-semibold border
                                                @if($lp->playing_status->value === 'playing') bg-blue-50 text-blue-700 border-blue-100
                                                @else bg-slate-50 text-slate-600 border-slate-100 @endif">
                                                {{ $lp->playing_status->label() }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Unlock Reason Detail -->
                    @if ($lineup && $lineup->status === 'draft' && $lineup->unlock_reason)
                        <div class="p-3 rounded-lg bg-red-50 text-red-800 border border-red-100 text-xs">
                            <strong>Alasan Penolakan Sebelumnya:</strong>
                            <p class="mt-1 font-semibold italic">{{ $lineup->unlock_reason }}</p>
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                @if ($lineup && in_array($lineup->status, ['submitted', 'verified']))
                    <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-2">
                        <!-- Unlock Button -->
                        <button type="button" 
                                @click="openUnlockModal = true; unlockTeamId = '{{ $team->id }}'; unlockTeamName = '{{ $team->name }}'; unlockActionUrl = '{{ route('supervisor.matches.verify-lineup.unlock', [$match->id, $team->id]) }}'"
                                class="inline-flex justify-center items-center rounded-lg border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-xs hover:bg-red-100">
                            Buka Kunci (Unlock)
                        </button>
                        
                        <!-- Verify Button -->
                        @if ($lineup->status === 'submitted')
                            <form action="{{ route('supervisor.matches.verify-lineup.approve', [$match->id, $team->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700">
                                    Verifikasi Lineup
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

    </div>

    <!-- Unlock Modal (Vanilla Tailwind dialog controlled by Alpine/Vanilla JS) -->
    <div x-show="openUnlockModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-500/75 transition-opacity" @click="openUnlockModal = false"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4 border border-slate-200">
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Buka Kunci Lineup: <span x-text="unlockTeamName"></span></h3>
                        <p class="mt-1 text-xs text-slate-500">Kunci lineup akan dibuka dan dikembalikan ke status Draft agar dapat diedit kembali oleh Admin Tim.</p>
                    </div>

                    <form :action="unlockActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="unlock_reason" class="block text-xs font-semibold text-slate-700">Tuliskan alasan/catatan perbaikan:</label>
                            <textarea id="unlock_reason" name="unlock_reason" rows="4" required placeholder="Contoh: Jumlah starting 5 kurang dari 5 pemain, warna jersey bertabrakan..."
                                      class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-xs"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="openUnlockModal = false" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-red-700">
                                Konfirmasi Unlock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
