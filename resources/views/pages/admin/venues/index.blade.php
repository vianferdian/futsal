@extends('layouts.app')

@section('title', 'Daftar Venue Pertandingan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Venue & Lapangan</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola daftar lokasi, stadion, atau arena pertandingan futsal.</p>
        </div>
        <div>
            <a href="{{ route('admin.venues.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Venue
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
        <form action="{{ route('admin.venues.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center">
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama venue, kota, atau alamat..." class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
            </div>
            <div class="flex w-full sm:w-auto gap-2">
                <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Cari
                </button>
                @if ($search)
                    <a href="{{ route('admin.venues.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-transparent px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
        @if ($venues->isEmpty())
            <div class="p-8 text-center text-slate-500 bg-white">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="mt-4 text-sm font-semibold">Tidak ada venue ditemukan</p>
                <p class="mt-1 text-xs text-slate-400">Silakan tambahkan data venue baru.</p>
            </div>
        @else
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Venue</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kota</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Alamat</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kapasitas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($venues as $venue)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $venue->name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-medium">
                                    {{ $venue->city }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">
                                    {{ $venue->address }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $venue->capacity ? number_format($venue->capacity) . ' Penonton' : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium border {{ $venue->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $venue->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.venues.edit', $venue->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('admin.venues.destroy', $venue->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus venue ini? Pengecekan relasi dengan jadwal pertandingan akan diverifikasi.')">
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
                {{ $venues->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
