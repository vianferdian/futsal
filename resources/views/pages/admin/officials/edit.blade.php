@extends('layouts.app')

@section('title', 'Ubah Official Tim')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.officials.index', ['team_id' => $official->team_id]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Ubah Official Tim</h1>
            <p class="mt-1 text-sm text-slate-500">Ubah data official: {{ $official->name }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <form action="{{ route('admin.officials.update', $official->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Lengkap Official</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $official->name) }}" required placeholder="Contoh: Coach Dadang"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="team_id" class="block text-sm font-semibold text-slate-700">Pilih Klub Tim</label>
                    <select name="team_id" id="team_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Tim --</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id', $official->team_id) == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="position" class="block text-sm font-semibold text-slate-700">Jabatan Official</label>
                    <select name="position" id="position" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach (\App\Enums\TeamOfficialPosition::cases() as $pos)
                            <option value="{{ $pos->value }}" {{ old('position', $official->position->value) === $pos->value ? 'selected' : '' }}>
                                {{ $pos->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('position')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                        <option value="active" {{ old('status', $official->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $official->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 space-y-3">
                    <label class="block text-sm font-semibold text-slate-700">Foto Official</label>
                    <div class="flex items-center gap-4">
                        @if ($official->photo)
                            <img class="h-16 w-16 rounded-full object-cover border border-slate-200" src="{{ asset('storage/' . $official->photo) }}" alt="">
                        @else
                            <div class="h-16 w-16 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xl">
                                {{ strtoupper(substr($official->name, 0, 2)) }}
                            </div>
                        @endif
                        <input type="file" name="photo" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    @error('photo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="border-t border-slate-200 pt-6 flex justify-end gap-3">
                <a href="{{ route('admin.officials.index', ['team_id' => $official->team_id]) }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
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
