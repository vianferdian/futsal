@extends('layouts.app')

@section('title', 'Daftar Tim Futsal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tim Futsal</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola daftar klub dan tim futsal peserta kompetisi.</p>
        </div>
        <div>
            <a href="{{ route('admin.teams.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Tim
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
        <form action="{{ route('admin.teams.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center">
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama tim, singkatan, atau kota asal..." class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
            </div>
            <div class="flex w-full sm:w-auto gap-2">
                <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Cari
                </button>
                @if ($search)
                    <a href="{{ route('admin.teams.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-transparent px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
        @if ($teams->isEmpty())
            <div class="p-8 text-center text-slate-500 bg-white">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mt-4 text-sm font-semibold">Tidak ada tim ditemukan</p>
                <p class="mt-1 text-xs text-slate-400">Silakan tambahkan data tim baru.</p>
            </div>
        @else
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Logo & Nama Tim</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kota</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kompetisi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Warna Jersey</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($teams as $team)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        @if ($team->logo)
                                            <img class="h-9 w-9 rounded-lg object-cover border border-slate-200" src="{{ asset('storage/' . $team->logo) }}" alt="">
                                        @else
                                            <div class="h-9 w-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($team->name, 0, 3)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.teams.show', $team->id) }}" class="font-bold text-slate-900 hover:text-blue-600">{{ $team->name }}</a>
                                            <div class="text-xs text-slate-500">{{ $team->short_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $team->city }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $team->competition ? $team->competition->name : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex gap-1.5">
                                        <span class="h-5 w-5 rounded-full border border-slate-200 inline-block shadow-xs" style="background-color: {{ $team->primary_color }}" title="Warna Utama"></span>
                                        <span class="h-5 w-5 rounded-full border border-slate-200 inline-block shadow-xs" style="background-color: {{ $team->secondary_color }}" title="Warna Kedua"></span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium border {{ $team->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $team->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.teams.show', $team->id) }}" class="text-slate-500 hover:text-slate-900">Detail</a>
                                        <a href="{{ route('admin.teams.edit', $team->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('admin.teams.destroy', $team->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tim ini? Seluruh data pemain dan official terkait akan ikut terhapus.')">
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
                {{ $teams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
