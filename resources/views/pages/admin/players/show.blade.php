@extends('layouts.app')

@section('title', 'Detail Pemain - ' . $player->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header Back link -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.players.index', ['team_id' => $player->team_id]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profil Pemain</h1>
                <p class="mt-1 text-sm text-slate-500">Detail data dan performa statistik pemain.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.players.edit', $player->id) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                Edit Profil
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row gap-8 items-center sm:items-start">
            <!-- Photo -->
            <div class="relative">
                @if ($player->photo)
                    <img class="h-32 w-32 rounded-xl object-cover border border-slate-200" src="{{ asset('storage/' . $player->photo) }}" alt="">
                @else
                    <div class="h-32 w-32 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-5xl">
                        {{ strtoupper(substr($player->name, 0, 2)) }}
                    </div>
                @endif
                <div class="absolute -bottom-3 -right-3 h-10 w-10 rounded-full bg-slate-900 border-2 border-white flex items-center justify-center text-white font-extrabold text-sm shadow-md" title="Nomor Punggung">
                    #{{ $player->shirt_number }}
                </div>
            </div>

            <!-- Details -->
            <div class="flex-1 space-y-4 text-center sm:text-left">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $player->name }}</h2>
                    <p class="text-sm font-semibold text-blue-600 mt-1">
                        {{ $player->position->label() }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm border-t border-slate-100 pt-4">
                    <div>
                        <span class="text-slate-500 block">Klub Futsal</span>
                        <a href="{{ route('admin.teams.show', $player->team_id) }}" class="font-bold text-slate-900 hover:text-blue-600">
                            {{ $player->team ? $player->team->name : '-' }}
                        </a>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Status Akun</span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border {{ $player->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                            {{ $player->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Tanggal Lahir</span>
                        <span class="font-semibold text-slate-900">
                            {{ $player->birth_date ? $player->birth_date->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Nomor Identitas</span>
                        <span class="font-semibold text-slate-900">
                            {{ $player->identity_number ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
        <h3 class="text-lg font-bold text-slate-900">Statistik Pertandingan</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="rounded-lg bg-slate-50 p-4 border border-slate-100">
                <span class="text-3xl font-extrabold text-slate-950 block">{{ $stats['goals'] }}</span>
                <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Total Gol</span>
            </div>
            <div class="rounded-lg bg-yellow-50 p-4 border border-yellow-100">
                <span class="text-3xl font-extrabold text-yellow-700 block">{{ $stats['yellow_cards'] }}</span>
                <span class="text-xs text-yellow-600 uppercase tracking-wider font-semibold">Kartu Kuning</span>
            </div>
            <div class="rounded-lg bg-red-50 p-4 border border-red-100">
                <span class="text-3xl font-extrabold text-red-700 block">{{ $stats['red_cards'] }}</span>
                <span class="text-xs text-red-600 uppercase tracking-wider font-semibold">Kartu Merah</span>
            </div>
        </div>
    </div>
</div>
@endsection
