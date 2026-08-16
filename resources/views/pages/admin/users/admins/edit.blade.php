@extends('layouts.app')

@section('title', 'Ubah Administrator')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.admins.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Ubah Administrator</h1>
            <p class="mt-1 text-sm text-slate-500">Ubah data administrator: {{ $user->name }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <form action="{{ route('admin.users.admins.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            @method('PUT')

            <!-- Role Hidden Field -->
            <input type="hidden" name="role" value="admin">

            <!-- Form Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required placeholder="Contoh: Admin Utama"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required placeholder="admin_baru"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('username')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required placeholder="admin@futsal.com"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi Baru (Opsional)</label>
                    <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700">No. Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status Akun</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white" 
                            {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @if(auth()->id() === $user->id)
                        <!-- If current user, send hidden status input since disabled select is not submitted -->
                        <input type="hidden" name="status" value="active">
                    @endif
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 space-y-3">
                    <label class="block text-sm font-semibold text-slate-700">Foto Profil</label>
                    <div class="flex items-center gap-4">
                        @if ($user->photo)
                            <img class="h-16 w-16 rounded-full object-cover border border-slate-200" src="{{ asset('storage/' . $user->photo) }}" alt="">
                        @else
                            <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                        <input type="file" name="photo" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    @error('photo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="border-t border-slate-200 pt-6 flex justify-end gap-3">
                <a href="{{ route('admin.users.admins.index') }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
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
