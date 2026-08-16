@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan Sistem</h1>
        <p class="mt-2 text-sm text-slate-500">Konfigurasi parameter global seperti nama sistem, durasi waktu babak, batas timeout, dan timezone.</p>
    </div>

    <!-- Alert Success -->
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 font-medium flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Form Card -->
    <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900">Konfigurasi Parameter Futsal & Sistem</h3>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="p-6 space-y-6 divide-y divide-slate-100">
            @csrf

            <!-- System Settings -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identitas & Laporan</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Aplikasi / Sistem</label>
                        <input type="text" name="system_name" value="{{ old('system_name', $settings['system_name']) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden">
                        @error('system_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Footer Laporan PDF</label>
                        <input type="text" name="pdf_footer" value="{{ old('pdf_footer', $settings['pdf_footer']) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden">
                        @error('pdf_footer')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Hak Cipta (Copyright)</label>
                        <input type="text" name="copyright" value="{{ old('copyright', $settings['copyright']) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden">
                        @error('copyright')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Futsal Rules Settings -->
            <div class="pt-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aturan Pertandingan</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi Babak (Menit)</label>
                        <input type="number" name="default_half_duration" value="{{ old('default_half_duration', $settings['default_half_duration']) }}" min="5" max="45" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden">
                        @error('default_half_duration')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Foul Sebelum Penalty</label>
                        <input type="number" name="max_fouls_before_penalty" value="{{ old('max_fouls_before_penalty', $settings['max_fouls_before_penalty']) }}" min="1" max="10" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden">
                        @error('max_fouls_before_penalty')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Timeout per Babak</label>
                        <input type="number" name="max_timeout_per_period" value="{{ old('max_timeout_per_period', $settings['max_timeout_per_period']) }}" min="1" max="5" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden">
                        @error('max_timeout_per_period')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Regional Settings -->
            <div class="pt-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lokalisasi & Waktu</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Zona Waktu (Timezone)</label>
                        <select name="timezone" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden bg-white">
                            <option value="Asia/Jakarta" {{ old('timezone', $settings['timezone']) === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                            <option value="Asia/Makassar" {{ old('timezone', $settings['timezone']) === 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                            <option value="Asia/Jayapura" {{ old('timezone', $settings['timezone']) === 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                            <option value="UTC" {{ old('timezone', $settings['timezone']) === 'UTC' ? 'selected' : '' }}>UTC / GMT</option>
                        </select>
                        @error('timezone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="pt-6 flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 transition-colors">
                    Perbarui Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
