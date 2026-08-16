@extends('layouts.app')

@section('title', 'Ubah Tim Futsal')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.teams.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Ubah Tim Futsal</h1>
            <p class="mt-1 text-sm text-slate-500">Ubah data tim: {{ $team->name }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <form action="{{ route('admin.teams.update', $team->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Lengkap Tim</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $team->name) }}" required placeholder="Contoh: Bintang Timur Surabaya"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="short_name" class="block text-sm font-semibold text-slate-700">Nama Singkat / Singkatan</label>
                    <input type="text" name="short_name" id="short_name" value="{{ old('short_name', $team->short_name) }}" required placeholder="Contoh: BTS"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('short_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city" class="block text-sm font-semibold text-slate-700">Kota Asal</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $team->city) }}" required placeholder="Contoh: Surabaya"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('city')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="competition_id" class="block text-sm font-semibold text-slate-700">Ikuti Kompetisi</label>
                    <select name="competition_id" id="competition_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Kompetisi (Opsional) --</option>
                        @foreach ($competitions as $comp)
                            <option value="{{ $comp->id }}" {{ old('competition_id', $team->competition_id) == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} ({{ $comp->season }})
                            </option>
                        @endforeach
                    </select>
                    @error('competition_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="active" {{ old('status', $team->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $team->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="primary_color" class="block text-sm font-semibold text-slate-700">Warna Jersey Utama</label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $team->primary_color) }}"
                               class="h-9 w-14 rounded-md border border-slate-300 cursor-pointer">
                        <span class="text-xs text-slate-500">Klik kotak untuk memilih warna</span>
                    </div>
                    @error('primary_color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="secondary_color" class="block text-sm font-semibold text-slate-700">Warna Jersey Kedua</label>
                    <div class="mt-1 flex items-center gap-2">
                        <input type="color" name="secondary_color" id="secondary_color" value="{{ old('secondary_color', $team->secondary_color) }}"
                               class="h-9 w-14 rounded-md border border-slate-300 cursor-pointer">
                        <span class="text-xs text-slate-500">Klik kotak untuk memilih warna</span>
                    </div>
                    @error('secondary_color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 space-y-3">
                    <label class="block text-sm font-semibold text-slate-700">Logo Tim</label>
                    <div class="flex items-center gap-4">
                        @if ($team->logo)
                            <img class="h-16 w-16 rounded-lg object-cover border border-slate-200" src="{{ asset('storage/' . $team->logo) }}" alt="">
                        @else
                            <div class="h-16 w-16 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
                                {{ strtoupper(substr($team->name, 0, 3)) }}
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    @error('logo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="border-t border-slate-200 pt-6 flex justify-end gap-3">
                <a href="{{ route('admin.teams.index') }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" :disabled="loading" class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed gap-2">
                    <span x-show="loading" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full" style="display: none;"></span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
