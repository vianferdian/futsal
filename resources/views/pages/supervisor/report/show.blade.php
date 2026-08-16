@extends('layouts.app')

@section('title', 'Laporan Pascapertandingan — #' . $match->match_number)

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="rounded-xl border border-slate-200 bg-linear-to-r from-emerald-900 to-slate-800 p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-emerald-400 mb-1">
                    {{ $match->competition->name }} — {{ $match->round }}
                </div>
                <h1 class="text-xl font-extrabold">Laporan Pascapertandingan #{{ $match->match_number }}</h1>
                <p class="text-sm text-slate-300 mt-0.5">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <div class="rounded-full px-3 py-1 text-xs font-bold
                    @if($match->status->value === 'locked') bg-emerald-600 text-white
                    @else bg-yellow-500 text-slate-900 @endif">
                    {{ $match->status->label() }}
                </div>
                <a href="{{ route('supervisor.matches.workspace', $match->id) }}"
                   class="text-xs text-slate-300 hover:text-white underline">← Kembali ke Workspace</a>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 font-medium">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800 font-medium">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== LEFT: MATCH SUMMARY STATS ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- SCOREBOARD --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Hasil Pertandingan</h2>
                <div class="flex items-center justify-center gap-6 text-center">
                    <div class="flex-1">
                        <div class="text-sm font-bold text-slate-700">{{ $match->homeTeam->name }}</div>
                        <div class="text-xs text-slate-400">HOME</div>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="text-4xl font-extrabold tracking-widest text-slate-900">
                            {{ $match->home_score }} – {{ $match->away_score }}
                        </div>
                        <div class="mt-1 text-xs text-slate-400">
                            (B1: {{ $match->home_first_half_score }} – {{ $match->away_first_half_score }})
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-slate-700">{{ $match->awayTeam->name }}</div>
                        <div class="text-xs text-slate-400">AWAY</div>
                    </div>
                </div>
            </div>

            {{-- FOUL SUMMARY --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Ringkasan Foul</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-center">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="pb-2 text-left text-xs font-semibold text-slate-500">Tim</th>
                                <th class="pb-2 text-xs font-semibold text-slate-500">Babak 1</th>
                                <th class="pb-2 text-xs font-semibold text-slate-500">Babak 2</th>
                                <th class="pb-2 text-xs font-semibold text-slate-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr>
                                <td class="py-2 text-left font-semibold text-slate-700">{{ $match->homeTeam->name }}</td>
                                <td class="py-2 {{ $homeFoulsB1 >= 5 ? 'text-red-600 font-bold' : 'text-slate-600' }}">{{ $homeFoulsB1 }}</td>
                                <td class="py-2 {{ $homeFoulsB2 >= 5 ? 'text-red-600 font-bold' : 'text-slate-600' }}">{{ $homeFoulsB2 }}</td>
                                <td class="py-2 font-bold text-slate-800">{{ $homeFoulsB1 + $homeFoulsB2 }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-left font-semibold text-slate-700">{{ $match->awayTeam->name }}</td>
                                <td class="py-2 {{ $awayFoulsB1 >= 5 ? 'text-red-600 font-bold' : 'text-slate-600' }}">{{ $awayFoulsB1 }}</td>
                                <td class="py-2 {{ $awayFoulsB2 >= 5 ? 'text-red-600 font-bold' : 'text-slate-600' }}">{{ $awayFoulsB2 }}</td>
                                <td class="py-2 font-bold text-slate-800">{{ $awayFoulsB1 + $awayFoulsB2 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- CARD SUMMARY --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">
                    Kartu Diterbitkan ({{ $allCards->count() }} total)
                </h2>
                @if ($allCards->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-4">Tidak ada kartu diterbitkan.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($allCards as $card)
                            @php
                                $cardIcon = match($card->event_type->value) {
                                    'yellow_card', 'official_yellow' => '🟨',
                                    'second_yellow' => '🟨🟨',
                                    'red_card', 'official_red' => '🟥',
                                    default => '•'
                                };
                                $isHome = $card->team_id === $match->home_team_id;
                                $periodLabel = $card->period === 'first_half' ? 'B1' : 'B2';
                            @endphp
                            <div class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2 text-xs">
                                <span class="text-sm">{{ $cardIcon }}</span>
                                <span class="font-mono text-slate-500">{{ str_pad($card->minute, 2,'0',STR_PAD_LEFT) }}:{{ str_pad($card->second, 2,'0',STR_PAD_LEFT) }}</span>
                                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isHome ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $isHome ? 'HOME' : 'AWAY' }} · {{ $periodLabel }}
                                </span>
                                <span class="font-semibold text-slate-700">{{ $card->event_type->label() }}</span>
                                <span class="text-slate-500">
                                    @if ($card->player) — #{{ $card->player->shirt_number }} {{ $card->player->name }} @endif
                                    @if ($card->official) — {{ $card->official->name }} (official) @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- FULL EVENT TIMELINE --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Timeline Lengkap</h2>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700">{{ $events->count() }} event</span>
                </div>
                @if ($events->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-8">Tidak ada event tercatat.</p>
                @else
                    <ul class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
                        @foreach ($events as $event)
                            @php
                                $isHome = $event->team_id === $match->home_team_id;
                                $icon = match($event->event_type->value) {
                                    'goal', 'penalty_goal', 'second_penalty_goal' => '⚽',
                                    'own_goal'     => '🙈',
                                    'penalty_miss' => '❌',
                                    'yellow_card'  => '🟨',
                                    'second_yellow'=> '🟨🟨',
                                    'red_card'     => '🟥',
                                    'foul'         => '⚠️',
                                    'timeout'      => '⏱️',
                                    'official_yellow' => '📋🟨',
                                    'official_red'    => '📋🟥',
                                    default        => '•',
                                };
                                $isGoal = in_array($event->event_type->value, ['goal','penalty_goal','second_penalty_goal','own_goal']);
                                $periodLabel = $event->period === 'first_half' ? 'B1' : 'B2';
                            @endphp
                            <li class="flex items-center gap-3 px-5 py-2.5 text-xs {{ $isGoal ? 'bg-blue-50/40' : '' }} hover:bg-slate-50">
                                <span class="shrink-0 w-14 text-center font-mono text-slate-500">
                                    {{ $periodLabel }}
                                </span>
                                <span>{{ $icon }}</span>
                                <span class="font-semibold {{ $isHome ? 'text-blue-700' : 'text-red-600' }}">
                                    {{ $isHome ? $match->homeTeam->short_name : $match->awayTeam->short_name }}
                                </span>
                                <span class="text-slate-700">{{ $event->event_type->label() }}</span>
                                @if ($event->player)
                                    <span class="text-slate-500">— #{{ $event->player->shirt_number }} {{ $event->player->name }}</span>
                                @endif
                                @if ($event->official)
                                    <span class="text-slate-500">— {{ $event->official->name }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- ===== RIGHT: REPORT FORM ===== --}}
        <div class="space-y-6">

            @if ($report->locked_at)
                {{-- LOCKED REPORT VIEW --}}
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <h3 class="text-sm font-bold text-emerald-800">Laporan Terkunci</h3>
                    </div>
                    <p class="text-xs text-emerald-700">Dikunci pada {{ $report->locked_at->format('d M Y H:i') }} oleh {{ $report->submittedByUser?->name ?? '-' }}.</p>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kondisi Pertandingan</span>
                            <span class="font-semibold text-slate-800">{{ $report->match_condition->label() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Penonton</span>
                            <span class="font-semibold text-slate-800">{{ $report->attendance ? number_format($report->attendance) : '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Potensi Pelanggaran</span>
                            <span class="font-semibold {{ $report->violation_potential ? 'text-red-600' : 'text-slate-800' }}">
                                {{ $report->violation_potential ? '⚠️ Ya' : 'Tidak' }}
                            </span>
                        </div>
                        @if ($report->violation_notes)
                            <div>
                                <span class="text-slate-500 block mb-1">Catatan Pelanggaran</span>
                                <p class="text-slate-700 bg-white rounded p-2 border border-slate-200">{{ $report->violation_notes }}</p>
                            </div>
                        @endif
                        @if ($report->supervisor_notes)
                            <div>
                                <span class="text-slate-500 block mb-1">Catatan Pengawas</span>
                                <p class="text-slate-700 bg-white rounded p-2 border border-slate-200">{{ $report->supervisor_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- EDITABLE REPORT FORM --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-5">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">📝 Isi Laporan Pascapertandingan</h3>

                    @if ($errors->any())
                        <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-xs text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <div>• {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('supervisor.matches.report.save', $match->id) }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Pertandingan <span class="text-red-500">*</span></label>
                            <select name="match_condition" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @foreach (\App\Enums\MatchCondition::cases() as $condition)
                                    <option value="{{ $condition->value }}" {{ old('match_condition', $report->match_condition?->value ?? 'normal') === $condition->value ? 'selected' : '' }}>
                                        {{ $condition->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Penonton</label>
                            <input type="number" name="attendance" min="0"
                                   value="{{ old('attendance', $report->attendance) }}"
                                   placeholder="misal: 2500"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="hidden" name="violation_potential" value="0">
                                <input type="checkbox" name="violation_potential" value="1"
                                       {{ old('violation_potential', $report->violation_potential) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span>Ada Potensi Pelanggaran / Insiden</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pelanggaran / Insiden</label>
                            <textarea name="violation_notes" rows="3"
                                      placeholder="Uraikan jika ada pelanggaran atau insiden yang perlu dilaporkan..."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('violation_notes', $report->violation_notes) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pengawas</label>
                            <textarea name="supervisor_notes" rows="4"
                                      placeholder="Catatan umum pengawas tentang jalannya pertandingan..."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('supervisor_notes', $report->supervisor_notes) }}</textarea>
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                            💾 Simpan Draft Laporan
                        </button>
                    </form>
                </div>

                {{-- SUBMIT & LOCK --}}
                @if ($report->exists && !$report->locked_at)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 space-y-3">
                        <h3 class="text-sm font-bold text-amber-900">🔒 Kunci & Kirim Laporan</h3>
                        <p class="text-xs text-amber-700">
                            Setelah dikunci, laporan tidak dapat diubah dan status pertandingan akan menjadi <strong>TERKUNCI</strong>.
                            Pastikan semua data sudah benar.
                        </p>
                        <form action="{{ route('supervisor.matches.report.submit', $match->id) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin mengunci laporan ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700 transition-colors">
                                🔒 Kunci & Submit Laporan Final
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
@endsection
