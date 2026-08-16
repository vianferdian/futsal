@extends('layouts.app')

@section('title', 'Jadwal Pertandingan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Jadwal Pertandingan</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola jadwal, tim bertanding, lokasi venue, dan pengawas pertandingan.</p>
        </div>
        <div>
            <a href="{{ route('admin.matches.create', ['competition_id' => $competitionId]) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Jadwal
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
        <form action="{{ route('admin.matches.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <!-- Search Text -->
            <div class="flex-1 w-full relative">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Cari Pertandingan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="No. Pertandingan, babak, atau nama tim..." class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                </div>
            </div>

            <!-- Competition Filter dropdown -->
            <div class="w-full lg:w-64">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kompetisi</label>
                <select name="competition_id" onchange="this.form.submit()" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                    <option value="">-- Semua Kompetisi --</option>
                    @foreach ($competitions as $comp)
                        <option value="{{ $comp->id }}" {{ $competitionId == $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }} ({{ $comp->season }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter -->
            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
            </div>

            <div class="flex w-full lg:w-auto gap-2">
                <button type="submit" class="w-full lg:w-auto inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50 h-[38px]">
                    Cari
                </button>
                @if ($search || $competitionId || $date)
                    <a href="{{ route('admin.matches.index') }}" class="w-full lg:w-auto inline-flex justify-center items-center rounded-lg border border-transparent px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700 h-[38px]">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
        @if ($matches->isEmpty())
            <div class="p-8 text-center text-slate-500 bg-white">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-4 text-sm font-semibold">Tidak ada jadwal pertandingan ditemukan</p>
                <p class="mt-1 text-xs text-slate-400">Silakan tambahkan data pertandingan atau sesuaikan filter Anda.</p>
            </div>
        @else
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Pertandingan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kompetisi & Babak</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal & Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pertandingan (Home vs Away)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Venue</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($matches as $match)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 text-xs">
                                        {{ $match->match_number }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="font-semibold text-slate-900">{{ $match->competition->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $match->round }} @if($match->group_name) ({{ $match->group_name }}) @endif</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    <div class="font-medium">{{ $match->match_date->format('d M Y') }}</div>
                                    <div class="text-xs">{{ substr($match->kickoff_time, 0, 5) }} WIB</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ $match->homeTeam->name }}</span>
                                        <span class="text-slate-400 font-semibold text-xs">vs</span>
                                        <span class="font-bold text-slate-900">{{ $match->awayTeam->name }}</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        Skor: <strong>{{ in_array($match->status->value, ['ongoing', 'finished']) ? $match->home_score . ' - ' . $match->away_score : 'Belum Mulai' }}</strong>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $match->venue ? $match->venue->name : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium border 
                                        @if($match->status->value === 'finished') bg-blue-50 text-blue-700 border-blue-200
                                        @elseif($match->status->value === 'ongoing') bg-red-50 text-red-700 border-red-200
                                        @elseif($match->status->value === 'match_scheduled') bg-slate-50 text-slate-700 border-slate-200
                                        @else bg-yellow-50 text-yellow-700 border-yellow-200 @endif">
                                        {{ $match->status->label() }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        @if(in_array($match->status->value, ['finished', 'locked']))
                                            <a href="{{ route('matches.summary', $match->id) }}" class="text-emerald-600 hover:text-emerald-950 font-bold" target="_blank">Ringkasan</a>
                                        @endif
                                        <a href="{{ route('admin.matches.show', $match->id) }}" class="text-slate-600 hover:text-slate-900">Detail & Pengawas</a>
                                        <a href="{{ route('admin.matches.edit', $match->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('admin.matches.destroy', $match->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal pertandingan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-950">Hapus</button>
                                        </form>
                                    </div>
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
@endsection
