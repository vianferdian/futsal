@extends('layouts.app')

@section('title', 'Daftar Official Tim')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Official Tim Futsal</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola pendaftaran staff official dan pelatih klub tim futsal.</p>
        </div>
        <div>
            <a href="{{ route('admin.officials.create', ['team_id' => $teamId]) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Official
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
        <form action="{{ route('admin.officials.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center">
            <!-- Search Text -->
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama official atau jabatan..." class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
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
                    <a href="{{ route('admin.officials.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-transparent px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
        @if ($officials->isEmpty())
            <div class="p-8 text-center text-slate-500 bg-white">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mt-4 text-sm font-semibold">Tidak ada official ditemukan</p>
                <p class="mt-1 text-xs text-slate-400">Silakan tambahkan data official atau ubah kriteria filter.</p>
            </div>
        @else
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Official</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Klub Tim</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jabatan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($officials as $official)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        @if ($official->photo)
                                            <img class="h-9 w-9 rounded-full object-cover border border-slate-200" src="{{ asset('storage/' . $official->photo) }}" alt="">
                                        @else
                                            <div class="h-9 w-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($official->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $official->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    @if ($official->team)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $official->team->primary_color }}"></span>
                                            {{ $official->team->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-semibold">
                                    {{ $official->position->label() }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium border {{ $official->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $official->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.officials.edit', $official->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('admin.officials.destroy', $official->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus official ini?')">
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
                {{ $officials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
