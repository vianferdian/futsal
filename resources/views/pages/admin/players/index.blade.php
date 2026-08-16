@extends('layouts.app')

@section('title', 'Daftar Pemain Futsal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pemain Futsal</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola pendaftaran pemain beserta posisi dan nomor punggung.</p>
        </div>
        <div>
            <a href="{{ route('admin.players.create', ['team_id' => $teamId]) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pemain
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
        <form action="{{ route('admin.players.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center">
            <!-- Search Text -->
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pemain atau nomor punggung..." class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
            </div>

            <!-- Team Filter dropdown -->
            <div class="w-full sm:w-64">
                <select name="team_id" onchange="this.form.submit()" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                    <option value="">-- Semua Tim --</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" {{ $teamId == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex w-full sm:w-auto gap-2">
                <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Filter
                </button>
                @if ($search || $teamId)
                    <a href="{{ route('admin.players.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-transparent px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
        @if ($players->isEmpty())
            <div class="p-8 text-center text-slate-500 bg-white">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="mt-4 text-sm font-semibold">Tidak ada pemain ditemukan</p>
                <p class="mt-1 text-xs text-slate-400">Silakan tambahkan data pemain atau ubah kriteria filter.</p>
            </div>
        @else
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Punggung</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama & Klub Tim</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Posisi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor Identitas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($players as $player)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-extrabold text-slate-900">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-slate-100 text-slate-800">
                                        #{{ $player->shirt_number }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        @if ($player->photo)
                                            <img class="h-9 w-9 rounded-full object-cover border border-slate-200" src="{{ asset('storage/' . $player->photo) }}" alt="">
                                        @else
                                            <div class="h-9 w-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($player->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.players.show', $player->id) }}" class="font-bold text-slate-900 hover:text-blue-600">{{ $player->name }}</a>
                                            <div class="text-xs text-slate-500">
                                                @if ($player->team)
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="h-2 w-2 rounded-full" style="background-color: {{ $player->team->primary_color }}"></span>
                                                        {{ $player->team->name }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-semibold">
                                    {{ $player->position->label() }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $player->identity_number ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium border {{ $player->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $player->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.players.show', $player->id) }}" class="text-slate-500 hover:text-slate-900">Detail</a>
                                        <a href="{{ route('admin.players.edit', $player->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('admin.players.destroy', $player->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemain ini?')">
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
                {{ $players->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
