@extends('layouts.app')

@section('title', 'Detail Pertandingan - ' . $match->match_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.matches.index', ['competition_id' => $match->competition_id]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Detail Pertandingan</h1>
                <p class="mt-1 text-sm text-slate-500">Informasi detail jadwal, lokasi, dan penugasan staff pengawas.</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if(in_array($match->status->value, ['finished', 'locked']))
                <a href="{{ route('matches.summary', $match->id) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition-colors gap-2" target="_blank">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Lihat Ringkasan
                </a>
            @endif
            <a href="{{ route('admin.matches.edit', $match->id) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                Ubah Jadwal
            </a>
        </div>
    </div>

    <!-- Match Visualizer Header -->
    <div class="rounded-xl border border-slate-200 bg-linear-to-r from-slate-900 to-slate-800 p-8 text-white shadow-md relative overflow-hidden">
        <!-- Background accents -->
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col items-center justify-center text-center space-y-6">
            <div class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                {{ $match->competition->name }} — {{ $match->round }}
            </div>
            
            <div class="flex items-center justify-center gap-8 w-full max-w-lg">
                <!-- Home Team -->
                <div class="flex-1 text-right space-y-2">
                    <div class="text-lg sm:text-xl font-bold tracking-tight">{{ $match->homeTeam->name }}</div>
                    <div class="text-xs text-slate-400">{{ $match->homeTeam->city }}</div>
                </div>

                <!-- Score / VS -->
                <div class="flex flex-col items-center justify-center bg-slate-950/60 rounded-xl px-6 py-3 border border-slate-700 shadow-inner">
                    @if(in_array($match->status->value, ['ongoing', 'finished']))
                        <div class="text-3xl font-extrabold tracking-widest">{{ $match->home_score }} - {{ $match->away_score }}</div>
                    @else
                        <div class="text-sm font-extrabold tracking-wider text-slate-400">VS</div>
                    @endif
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        {{ $match->status->label() }}
                    </div>
                </div>

                <!-- Away Team -->
                <div class="flex-1 text-left space-y-2">
                    <div class="text-lg sm:text-xl font-bold tracking-tight">{{ $match->awayTeam->name }}</div>
                    <div class="text-xs text-slate-400">{{ $match->awayTeam->city }}</div>
                </div>
            </div>

            <div class="text-sm text-slate-300 flex flex-col sm:flex-row gap-2 sm:gap-6 pt-4 border-t border-slate-700/50 w-full justify-center">
                <span class="flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ $match->match_date->format('l, d M Y') }}
                </span>
                <span class="flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ substr($match->kickoff_time, 0, 5) }} WIB
                </span>
                <span class="flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $match->venue ? $match->venue->name : '-' }} ({{ $match->venue ? $match->venue->city : '-' }})
                </span>
            </div>
        </div>
    </div>

    <!-- Split Section: Match Info & Supervisor Assignment -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left: Match Info details -->
        <div class="md:col-span-1 rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4 h-fit">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Informasi Pertandingan</h3>
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="py-2 flex justify-between">
                    <dt class="text-slate-500">No. Pertandingan</dt>
                    <dd class="font-bold text-slate-900">{{ $match->match_number }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-slate-500">Babak</dt>
                    <dd class="font-semibold text-slate-900">{{ $match->round }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-slate-500">Grup</dt>
                    <dd class="font-semibold text-slate-900">{{ $match->group_name ?? '-' }}</dd>
                </div>
                <div class="py-2 flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="font-semibold text-blue-600">{{ $match->status->label() }}</dd>
                </div>
            </dl>
        </div>

        <!-- Right: Supervisor Assignment list/form -->
        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-6">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <h3 class="text-lg font-bold text-slate-900">Pengawas Pertandingan (PP)</h3>
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 border border-blue-100">
                    {{ $match->supervisors->count() }} Pengawas Ditugaskan
                </span>
            </div>

            <!-- List Assigned -->
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50 divide-y divide-slate-200">
                @if ($match->supervisors->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500">
                        Belum ada pengawas pertandingan yang ditugaskan ke jadwal ini.
                    </div>
                @else
                    @foreach ($match->supervisors as $spv)
                        <div class="flex items-center justify-between p-4 bg-white">
                            <div class="flex items-center gap-3">
                                @if ($spv->photo)
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ asset('storage/' . $spv->photo) }}" alt="">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($spv->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $spv->name }}</div>
                                    <div class="text-xs text-slate-500">@ {{ $spv->username }}</div>
                                </div>
                            </div>
                            <div>
                                <form action="{{ route('admin.matches.unassign-supervisor', [$match->id, $spv->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penugasan pengawas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 border border-red-200 hover:bg-red-100">
                                        Batal Tugas
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Assign Form (Only if supervisors are available) -->
            @if ($availableSupervisors->isNotEmpty())
                <div class="border-t border-slate-200 pt-6 space-y-3">
                    <h4 class="text-sm font-bold text-slate-700">Tugaskan Pengawas Baru</h4>
                    <form action="{{ route('admin.matches.assign-supervisor', $match->id) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <select name="user_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                                <option value="">-- Pilih Pengawas --</option>
                                @foreach ($availableSupervisors as $spv)
                                    <option value="{{ $spv->id }}">{{ $spv->name }} (@ {{ $spv->username }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 h-[38px]">
                                Tugaskan
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
