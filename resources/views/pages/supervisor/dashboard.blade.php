@extends('layouts.app')

@section('title', 'Dashboard Pengawas Pertandingan')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Pengawas Pertandingan</h1>
        <p class="mt-2 text-sm text-slate-500">Berikut adalah daftar penugasan pertandingan Anda.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 gap-5 sm:grid-cols-4">
        
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Tugas Hari Ini</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['today_assigned'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Mendatang (Upcoming)</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['upcoming'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Selesai (Finished)</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['finished'] }}</dd>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <dt class="truncate text-sm font-medium text-slate-500">Total Tugas</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stats['total_assigned'] }}</dd>
        </div>

    </div>

    <!-- Next Assignment & Assignments Table -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Left: All Assignments -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Daftar Pertandingan Anda</h2>
            
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
                @if ($assignments->isEmpty())
                    <p class="text-sm text-slate-500 text-center py-8">Anda belum memiliki penugasan pertandingan.</p>
                @else
                    <div class="min-w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Match</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pertandingan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal & Venue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($assignments as $match)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                            #{{ $match->match_number }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold">{{ $match->homeTeam->name }}</span>
                                                <span class="text-slate-400">vs</span>
                                                <span class="font-semibold">{{ $match->awayTeam->name }}</span>
                                            </div>
                                            <div class="text-xs text-slate-500 font-mono mt-0.5">
                                                Skor: {{ $match->home_score }} - {{ $match->away_score }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            <div>{{ $match->match_date->format('d M Y') }} • {{ substr($match->kickoff_time, 0, 5) }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $match->venue->name }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium 
                                                @if($match->status->value === 'first_half' || $match->status->value === 'second_half') bg-red-50 text-red-700 border border-red-200 
                                                @elseif($match->status->value === 'finished' || $match->status->value === 'locked') bg-emerald-50 text-emerald-700 border border-emerald-200
                                                @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                                {{ $match->status->label() }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-3">
                                                @if(in_array($match->status->value, ['finished', 'locked']))
                                                    <a href="{{ route('matches.summary', $match->id) }}" class="text-emerald-600 hover:text-emerald-950 font-bold" target="_blank">Ringkasan</a>
                                                @endif
                                                <a href="{{ route('supervisor.matches.verify-lineup', $match->id) }}" class="text-blue-600 hover:text-blue-900">Buka Workspace</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200 px-6 py-4">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Next Assignment -->
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Tugas Berikutnya</h2>
            
            @if (!$nextAssignment)
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 bg-white">
                    <p class="text-sm font-semibold">Tidak ada tugas aktif berikutnya</p>
                    <p class="text-xs text-slate-400 mt-1">Seluruh penugasan Anda telah selesai.</p>
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-5">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 tracking-wider">NEXT ASSIGNMENT</span>
                        <span class="inline-flex items-center rounded-md bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 text-xs font-semibold">
                            {{ $nextAssignment->status->label() }}
                        </span>
                    </div>

                    <div class="text-center py-4 space-y-2">
                        <div class="text-base font-bold text-slate-950 truncate">{{ $nextAssignment->homeTeam->name }}</div>
                        <div class="text-xs font-semibold text-slate-400">VS</div>
                        <div class="text-base font-bold text-slate-950 truncate">{{ $nextAssignment->awayTeam->name }}</div>
                    </div>

                    <div class="border-t border-b border-slate-100 py-3 text-sm text-slate-600 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Tanggal</span>
                            <span class="font-semibold text-slate-800">{{ $nextAssignment->match_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Kickoff</span>
                            <span class="font-semibold text-slate-800">{{ substr($nextAssignment->kickoff_time, 0, 5) }} WIB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Venue</span>
                            <span class="font-semibold text-slate-800 truncate max-w-[150px]">{{ $nextAssignment->venue->name }}</span>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('supervisor.matches.verify-lineup', $nextAssignment->id) }}" class="w-full flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                            Buka Match Workspace
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
