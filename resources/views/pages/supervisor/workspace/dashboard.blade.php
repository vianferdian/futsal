@extends('layouts.app')

@section('title', 'Match Workspace - #' . $match->match_number)

@section('content')
<div class="space-y-6">
    <!-- Header/Scoreboard -->
    <div class="rounded-xl border border-slate-200 bg-linear-to-r from-slate-900 to-slate-800 p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col items-center justify-center text-center space-y-4">
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                {{ $match->competition->name }} — {{ $match->round }}
            </div>
            
            <div class="flex items-center justify-center gap-4 sm:gap-8 w-full max-w-lg">
                <div class="flex-1 text-right">
                    <div class="text-base sm:text-lg font-bold truncate">{{ $match->homeTeam->name }}</div>
                </div>

                <div class="flex flex-col items-center justify-center bg-slate-950/60 rounded-xl px-6 py-2 border border-slate-700 shadow-inner min-w-[120px]">
                    @if(in_array($match->status->value, ['first_half', 'halftime', 'second_half', 'finished', 'locked']))
                        <div class="text-2xl font-extrabold whitespace-nowrap">{{ $match->home_score }} - {{ $match->away_score }}</div>
                    @else
                        <div class="text-xs font-extrabold tracking-wider text-slate-400">READY</div>
                    @endif
                </div>

                <div class="flex-1 text-left">
                    <div class="text-base sm:text-lg font-bold truncate">{{ $match->awayTeam->name }}</div>
                </div>
            </div>

            <div class="text-xs text-slate-300 flex flex-wrap gap-4 pt-2 border-t border-slate-700/50 w-full justify-center">
                <span>{{ $match->venue->name }}</span>
                <span>•</span>
                <span>Status: <strong class="text-blue-400">{{ $match->status->label() }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Main Workspace Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Match Control Panel -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Ready state: Start Match trigger -->
            @if ($match->status->value === 'ready')
                <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-xs text-center space-y-6">
                    <div class="max-w-md mx-auto space-y-2">
                        <h2 class="text-xl font-bold text-slate-900">Persiapan Memulai Pertandingan</h2>
                        <p class="text-sm text-slate-500">Kedua lineup tim telah diverifikasi oleh pengawas dan jersey warna telah disepakati. Silakan klik tombol di bawah untuk memulai babak pertama.</p>
                    </div>

                    <div class="flex justify-center gap-4 flex-wrap">
                        <a href="{{ route('matches.start-list', $match->id) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                            <svg class="mr-2 h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Start List (DSP)
                        </a>

                        <form action="{{ route('supervisor.matches.start', $match->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulai babak pertama pertandingan?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 gap-2">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Mulai Pertandingan (Kickoff)
                            </button>
                        </form>
                    </div>
                </div>

            <!-- Wait state: Lineups not verified -->
            @elseif (in_array($match->status->value, ['draft', 'waiting_lineup', 'lineup_submitted']))
                <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-xs text-center space-y-6">
                    <div class="max-w-md mx-auto space-y-2 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h2 class="text-lg font-bold text-slate-900 mt-4">Susunan Pemain Belum Siap</h2>
                        <p class="text-sm">Pertandingan tidak dapat dimulai karena lineup kedua tim belum lengkap diserahkan dan diverifikasi oleh Pengawas.</p>
                    </div>

                    <div>
                        <a href="{{ route('supervisor.matches.verify-lineup', $match->id) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                            Verifikasi Lineup & Jersey
                        </a>
                    </div>
                </div>

            <!-- Running match workspace controls -->
            @else
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-6" 
                     x-data="matchTimer({
                         status: '{{ $match->status->value }}',
                         timerStatus: '{{ $match->timer_status }}',
                         elapsedSeconds: {{ $match->elapsed_seconds }},
                         timerStartedAt: '{{ $match->timer_started_at ? $match->timer_started_at->toIso8601String() : "" }}',
                         durationMinutes: {{ $match->status->value === 'second_half' ? $match->second_half_duration : $match->first_half_duration }}
                     })" x-init="initTimer()">
                    
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-2">Kontrol Pertandingan Aktif</h2>
                                     <div class="flex justify-center">
                        <!-- Period control widget -->
                        <div class="bg-slate-50 border border-slate-100 rounded-lg p-5 text-center space-y-1 flex flex-col justify-center items-center max-w-sm w-full">
                            <div class="text-xs font-bold text-slate-400 uppercase">Babak Aktif</div>
                            <div class="text-lg font-bold text-slate-800">{{ $match->status->label() }}</div>
                            <div class="text-[10px] text-slate-400">Durasi: {{ $match->status->value === 'second_half' ? $match->second_half_duration : $match->first_half_duration }} menit</div>
                        </div>
                    </div>

                    <!-- Timer Actions -->
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                        @if ($match->status->value === 'first_half' || $match->status->value === 'second_half')

                            @if ($match->status->value === 'first_half')
                                <form action="{{ route('supervisor.matches.end-first-half', $match->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan babak pertama?')">
                                    @csrf
                                    <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-slate-700 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-slate-800">
                                        🔚 Akhiri Babak 1
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if ($match->status->value === 'halftime')
                            <form action="{{ route('supervisor.matches.start-second-half', $match->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin memulai babak kedua?')">
                                @csrf
                                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-blue-700">
                                    ▶ Mulai Babak 2
                                </button>
                            </form>
                        @endif

                        @if (in_array($match->status->value, ['first_half', 'halftime', 'second_half']))
                            <form action="{{ route('supervisor.matches.finish', $match->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan pertandingan?')">
                                @csrf
                                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-red-700">
                                    🏁 Selesaikan Pertandingan
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Quick Event Buttons (Live) -->
                    <div class="space-y-4 pt-4 border-t border-slate-100"
                         x-data="{
                            modalGoal: false, modalCard: false, modalOfficial: false,
                            goalTeamId: '', cardTeamId: '', officialTeamId: '',
                            goalMinute: 0, goalSecond: 0,
                            cardMinute: 0, cardSecond: 0, officialMinute: 0, officialSecond: 0
                         }">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aksi Cepat Pencatatan</h3>

                        <!-- Foul Counters (1-click) -->
                        <div class="grid grid-cols-2 gap-3">
                            @php
                                $homeFoulColor = $homeFouls >= 6 ? 'bg-red-600 hover:bg-red-700' : ($homeFouls >= 5 ? 'bg-amber-500 hover:bg-amber-600' : 'bg-slate-700 hover:bg-slate-800');
                                $awayFoulColor = $awayFouls >= 6 ? 'bg-red-600 hover:bg-red-700' : ($awayFouls >= 5 ? 'bg-amber-500 hover:bg-amber-600' : 'bg-slate-700 hover:bg-slate-800');
                            @endphp
                            <form action="{{ route('supervisor.matches.events.foul', $match->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="team_id" value="{{ $match->home_team_id }}">
                                <button type="submit" class="w-full inline-flex flex-col items-center justify-center rounded-lg {{ $homeFoulColor }} text-white font-bold text-sm py-3 shadow-xs transition-colors">
                                    <span class="text-xs opacity-70">HOME FOUL</span>
                                    <span class="text-xl font-extrabold">{{ $homeFouls }}</span>
                                </button>
                            </form>
                            <form action="{{ route('supervisor.matches.events.foul', $match->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="team_id" value="{{ $match->away_team_id }}">
                                <button type="submit" class="w-full inline-flex flex-col items-center justify-center rounded-lg {{ $awayFoulColor }} text-white font-bold text-sm py-3 shadow-xs transition-colors">
                                    <span class="text-xs opacity-70">AWAY FOUL</span>
                                    <span class="text-xl font-extrabold">{{ $awayFouls }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Goal -->
                            <button type="button" @click="modalGoal = true; goalMinute = 0; goalSecond = 0"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 text-white font-bold text-sm py-3 shadow-xs hover:bg-blue-700 transition-colors">
                                ⚽ Gol
                            </button>
                            <!-- Card -->
                            <button type="button" @click="modalCard = true; cardMinute = 0; cardSecond = 0"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-yellow-500 text-white font-bold text-sm py-3 shadow-xs hover:bg-yellow-600 transition-colors">
                                🟨 Kartu
                            </button>
                            <!-- Official Card -->
                            <button type="button" @click="modalOfficial = true; officialMinute = 0; officialSecond = 0"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-purple-600 text-white font-bold text-sm py-3 shadow-xs hover:bg-purple-700 transition-colors">
                                📋 Kartu Official
                            </button>
                        </div>

                        <!-- ===== GOAL MODAL ===== -->
                        <div x-show="modalGoal" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
                            <div class="fixed inset-0 bg-slate-900/70" @click="modalGoal = false"></div>
                            <div class="relative z-50 flex min-h-full items-center justify-center p-4">
                                <div class="w-full max-w-md rounded-xl bg-white shadow-xl border border-slate-200 p-6 space-y-4">
                                    <h3 class="text-base font-bold text-slate-900">⚽ Catat Gol</h3>
                                    <form action="{{ route('supervisor.matches.events.goal', $match->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tim</label>
                                            <select name="team_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" x-model="goalTeamId">
                                                <option value="">— Pilih Tim —</option>
                                                <option value="{{ $match->home_team_id }}">{{ $match->homeTeam->name }} (HOME)</option>
                                                <option value="{{ $match->away_team_id }}">{{ $match->awayTeam->name }} (AWAY)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Gol</label>
                                            <select name="event_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="goal">Gol Normal</option>
                                                <option value="penalty_goal">Gol Penalti</option>
                                                <option value="second_penalty_goal">Gol Second Penalty</option>
                                                <option value="own_goal">Gol Bunuh Diri</option>
                                                <option value="penalty_miss">Gagal Penalti (tidak skor)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pemain</label>
                                            <select name="player_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="">— Pilih Pemain —</option>
                                                @if ($homeLineup)
                                                    <optgroup label="{{ $match->homeTeam->name }} (HOME)">
                                                        @foreach ($homeLineup->players->sortBy('player.shirt_number') as $lp)
                                                            <option value="{{ $lp->player_id }}">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if ($awayLineup)
                                                    <optgroup label="{{ $match->awayTeam->name }} (AWAY)">
                                                        @foreach ($awayLineup->players->sortBy('player.shirt_number') as $lp)
                                                            <option value="{{ $lp->player_id }}">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                        </div>
                                        <input type="hidden" name="minute" value="0">
                                        <input type="hidden" name="second" value="0">
                                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                            <button type="button" @click="modalGoal = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">Simpan Gol</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ===== CARD MODAL ===== -->
                        <div x-show="modalCard" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
                            <div class="fixed inset-0 bg-slate-900/70" @click="modalCard = false"></div>
                            <div class="relative z-50 flex min-h-full items-center justify-center p-4">
                                <div class="w-full max-w-md rounded-xl bg-white shadow-xl border border-slate-200 p-6 space-y-4">
                                    <h3 class="text-base font-bold text-slate-900">🟨 Catat Kartu Pemain</h3>
                                    <form action="{{ route('supervisor.matches.events.card', $match->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tim</label>
                                            <select name="team_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="">— Pilih Tim —</option>
                                                <option value="{{ $match->home_team_id }}">{{ $match->homeTeam->name }} (HOME)</option>
                                                <option value="{{ $match->away_team_id }}">{{ $match->awayTeam->name }} (AWAY)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Kartu</label>
                                            <select name="event_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="yellow_card">🟨 Kartu Kuning</option>
                                                <option value="second_yellow">🟨🟨 Kartu Kuning Kedua</option>
                                                <option value="red_card">🟥 Kartu Merah</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pemain</label>
                                            <select name="player_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="">— Pilih Pemain —</option>
                                                @if ($homeLineup)
                                                    <optgroup label="{{ $match->homeTeam->name }} (HOME)">
                                                        @foreach ($homeLineup->players->sortBy('player.shirt_number') as $lp)
                                                            <option value="{{ $lp->player_id }}">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if ($awayLineup)
                                                    <optgroup label="{{ $match->awayTeam->name }} (AWAY)">
                                                        @foreach ($awayLineup->players->sortBy('player.shirt_number') as $lp)
                                                            <option value="{{ $lp->player_id }}">#{{ $lp->player->shirt_number }} {{ $lp->player->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                        </div>
                                        <input type="hidden" name="minute" value="0">
                                        <input type="hidden" name="second" value="0">
                                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                            <button type="button" @click="modalCard = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                                            <button type="submit" class="rounded-lg bg-yellow-500 px-4 py-2 text-xs font-semibold text-white hover:bg-yellow-600">Simpan Kartu</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ===== OFFICIAL CARD MODAL ===== -->
                        <div x-show="modalOfficial" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
                            <div class="fixed inset-0 bg-slate-900/70" @click="modalOfficial = false"></div>
                            <div class="relative z-50 flex min-h-full items-center justify-center p-4">
                                <div class="w-full max-w-md rounded-xl bg-white shadow-xl border border-slate-200 p-6 space-y-4">
                                    <h3 class="text-base font-bold text-slate-900">📋 Kartu Official Tim</h3>
                                    <form action="{{ route('supervisor.matches.events.official-card', $match->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tim</label>
                                            <select name="team_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="">— Pilih Tim —</option>
                                                <option value="{{ $match->home_team_id }}">{{ $match->homeTeam->name }} (HOME)</option>
                                                <option value="{{ $match->away_team_id }}">{{ $match->awayTeam->name }} (AWAY)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Official</label>
                                            <select name="official_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="">— Pilih Official —</option>
                                                @if ($homeOfficials->isNotEmpty())
                                                    <optgroup label="{{ $match->homeTeam->name }} (HOME)">
                                                        @foreach ($homeOfficials as $off)
                                                            <option value="{{ $off->id }}">{{ $off->name }} — {{ $off->position->label() }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                @if ($awayOfficials->isNotEmpty())
                                                    <optgroup label="{{ $match->awayTeam->name }} (AWAY)">
                                                        @foreach ($awayOfficials as $off)
                                                            <option value="{{ $off->id }}">{{ $off->name }} — {{ $off->position->label() }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Kartu</label>
                                            <select name="event_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                                <option value="official_yellow">🟨 Kartu Kuning</option>
                                                <option value="official_red">🟥 Kartu Merah</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="minute" value="0">
                                        <input type="hidden" name="second" value="0">
                                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                            <button type="button" @click="modalOfficial = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                                            <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-xs font-semibold text-white hover:bg-purple-700">Simpan Kartu Official</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                <script>
                    function matchTimer(config) {
                        return {
                            status: config.status,
                            timerStatus: config.timerStatus,
                            elapsedSeconds: config.elapsedSeconds,
                            timerStartedAt: config.timerStartedAt ? new Date(config.timerStartedAt).getTime() : null,
                            durationSeconds: config.durationMinutes * 60,
                            displayTime: '20:00',
                            intervalId: null,

                            initTimer() {
                                this.updateDisplay();
                                if (this.timerStatus === 'running') {
                                    this.startInterval();
                                }
                            },

                            startInterval() {
                                if (this.intervalId) clearInterval(this.intervalId);
                                this.intervalId = setInterval(() => {
                                    this.updateDisplay();
                                }, 1000);
                            },

                            updateDisplay() {
                                if (this.status === 'finished' || this.status === 'locked' || this.timerStatus === 'finished') {
                                    this.displayTime = '00:00';
                                    if (this.intervalId) clearInterval(this.intervalId);
                                    return;
                                }

                                let currentElapsed = this.elapsedSeconds;
                                if (this.timerStatus === 'running' && this.timerStartedAt) {
                                    const now = new Date().getTime();
                                    const diff = Math.floor((now - this.timerStartedAt) / 1000);
                                    currentElapsed += diff;
                                }

                                let remaining = this.durationSeconds - currentElapsed;
                                if (remaining <= 0) {
                                    remaining = 0;
                                    this.timerStatus = 'paused';
                                    if (this.intervalId) clearInterval(this.intervalId);
                                }

                                const minutes = Math.floor(remaining / 60);
                                const seconds = remaining % 60;
                                this.displayTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                            }
                        }
                    }
                </script>
            </div>
        @endif

        </div>

        <!-- Right: Team Jerseys & Start List quick view -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Printable Start List block -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Dokumen Pertandingan</h3>
                <p class="text-xs text-slate-400">Cetak dokumen Start List resmi pertandingan untuk kebutuhan arsip fisik.</p>
                <a href="{{ route('matches.start-list', $match->id) }}" target="_blank" class="w-full inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Cetak DSP (Start List)
                </a>
                @if (in_array($match->status->value, ['finished', 'locked']))
                    <a href="{{ route('matches.summary', $match->id) }}" target="_blank"
                       class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition-colors">
                        📊 Lihat Ringkasan Pertandingan
                    </a>
                    <a href="{{ route('matches.summary.pdf', $match->id) }}" target="_blank"
                       class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition-colors">
                        📥 Download Ringkasan (PDF)
                    </a>
                    <a href="{{ route('supervisor.matches.report', $match->id) }}"
                       class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ $match->status->value === 'locked' ? '📄 Lihat Laporan (Terkunci)' : '📝 Buat Laporan Pascapertandingan' }}
                    </a>
                @endif
            </div>

            <!-- Team Jersey quick view -->
            @if ($homeJersey && $awayJersey)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Warna Jersey Bertanding</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-700">{{ $match->homeTeam->name }}</span>
                            <div class="flex gap-1">
                                <span class="h-3 w-3 rounded-full border border-slate-300" style="background-color: {{ $homeJersey->player_jersey_color }}"></span>
                                <span class="h-3 w-3 rounded-full border border-slate-300" style="background-color: {{ $homeJersey->goalkeeper_jersey_color }}"></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-700">{{ $match->awayTeam->name }}</span>
                            <div class="flex gap-1">
                                <span class="h-3 w-3 rounded-full border border-slate-300" style="background-color: {{ $awayJersey->player_jersey_color }}"></span>
                                <span class="h-3 w-3 rounded-full border border-slate-300" style="background-color: {{ $awayJersey->goalkeeper_jersey_color }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        <!-- ===== MATCH TIMELINE ===== -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">Timeline Pertandingan</h3>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700">{{ $events->count() }} event</span>
            </div>

            @if ($events->isEmpty())
                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                    <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-slate-400">Belum ada event dicatat.</p>
                </div>
            @else
                <ul class="divide-y divide-slate-50 max-h-[520px] overflow-y-auto">
                    @foreach ($events as $event)
                        @php
                            $isHome = $event->team_id === $match->home_team_id;
                            $icon = match($event->event_type->value) {
                                'goal', 'penalty_goal', 'second_penalty_goal' => '⚽',
                                'own_goal'    => '🙈',
                                'penalty_miss'=> '❌',
                                'yellow_card' => '🟨',
                                'second_yellow'=> '🟨🟨',
                                'red_card'    => '🟥',
                                'foul'        => '⚠️',
                                'timeout'     => '⏱️',
                                'official_yellow' => '📋🟨',
                                'official_red'    => '📋🟥',
                                default       => '•',
                            };
                            $goalTypes = ['goal','penalty_goal','second_penalty_goal','own_goal'];
                            $isGoal = in_array($event->event_type->value, $goalTypes);
                            $periodLabel = $event->period === 'first_half' ? 'B1' : 'B2';
                        @endphp
                        <li class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $isGoal ? 'bg-blue-50/50' : '' }}">
                            <!-- Side indicator -->
                            <div class="mt-0.5 flex shrink-0 flex-col items-center gap-0.5">
                                <span class="text-[10px] font-bold {{ $isHome ? 'text-blue-600' : 'text-red-500' }}">
                                    {{ $isHome ? 'HME' : 'AWY' }}
                                </span>
                                <span class="text-xs font-mono text-slate-400">{{ $periodLabel }}</span>
                            </div>

                            <!-- Icon & Description -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sm leading-none">{{ $icon }}</span>
                                    <span class="text-xs font-semibold text-slate-800">{{ $event->event_type->label() }}</span>
                                </div>
                                @if ($event->player)
                                    <div class="mt-0.5 text-[11px] text-slate-500 truncate">
                                        #{{ $event->player->shirt_number }} {{ $event->player->name }}
                                        @if ($event->relatedPlayer)
                                            <span class="text-slate-400">· assist: {{ $event->relatedPlayer->name }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if ($event->official)
                                    <div class="mt-0.5 text-[11px] text-slate-500 truncate">
                                        {{ $event->official->name }}
                                    </div>
                                @endif
                                <div class="mt-0.5 text-[10px] text-slate-300">oleh {{ $event->createdByUser?->name ?? 'Sistem' }}</div>
                            </div>

                            <!-- Undo Button -->
                            @if (!in_array($match->status->value, ['finished', 'locked']))
                                <form method="POST" action="{{ route('supervisor.matches.events.destroy', [$match->id, $event->id]) }}"
                                      onsubmit="return confirm('Undo event ini? Data yang sudah di-undo tidak dapat dikembalikan.')"
                                      class="shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[10px] font-bold text-red-600 hover:bg-red-100 transition-colors"
                                            title="Undo event ini">
                                        Undo
                                    </button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        </div>
    </div>
</div>
@endsection
