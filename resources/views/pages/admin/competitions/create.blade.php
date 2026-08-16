@extends('layouts.app')

@section('title', 'Tambah Kompetisi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.competitions.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tambah Kompetisi Baru</h1>
            <p class="mt-1 text-sm text-slate-500">Buat data kompetisi atau turnamen futsal baru.</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <form action="{{ route('admin.competitions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Kompetisi</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Liga Futsal Profesional 2026"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="short_name" class="block text-sm font-semibold text-slate-700">Nama Singkat / Singkatan</label>
                    <input type="text" name="short_name" id="short_name" value="{{ old('short_name') }}" required placeholder="Contoh: LFP 2026"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('short_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="season" class="block text-sm font-semibold text-slate-700">Season (Musim)</label>
                    <input type="text" name="season" id="season" value="{{ old('season') }}" required placeholder="Contoh: 2026"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('season')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('start_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('end_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Logo Kompetisi</label>
                    <input type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('logo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi Kompetisi</label>
                    <textarea name="description" id="description" rows="3" placeholder="Masukkan deskripsi atau keterangan kompetisi..."
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="border-t border-slate-200 pt-6 flex justify-end gap-3">
                <a href="{{ route('admin.competitions.index') }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" :disabled="loading" class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed gap-2">
                    <span x-show="loading" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full" style="display: none;"></span>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
