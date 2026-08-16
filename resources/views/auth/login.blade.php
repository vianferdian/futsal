<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Masuk | Sistem Informasi Pertandingan Futsal</title>
    
    <!-- Favicon -->
    <link class="fav" rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230f172a' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10' fill='%23ffffff'/%3E%3Cpolygon points='12 7.5 15.5 10 14 14 10 14 8.5 10' fill='%230f172a'/%3E%3Cpath d='M12 7.5V2M15.5 10L19.5 8.5M14 14L17.5 19.5M10 14L6.5 19.5M8.5 10L4.5 8.5'/%3E%3C/svg%3E">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-900 antialiased font-sans flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center">
            <img src="{{ asset('afp_jabar.png') }}" alt="Asosiasi Futsal" class="h-20 w-auto object-contain rounded-xl shadow-md border border-slate-200">
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 border border-slate-200 sm:rounded-xl sm:px-10 shadow-xs">
            
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                
                <div>
                    <label for="login" class="block text-sm font-semibold text-slate-700">Email atau Username</label>
                    <div class="mt-1">
                        <input id="login" name="login" type="text" autocomplete="username" required 
                               value="{{ old('login') }}"
                               placeholder="admin@futsal.com atau admin_tim"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                    </div>
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
                    <div class="mt-1 relative rounded-md shadow-xs">
                        <input id="password" name="password" type="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                               placeholder="••••••••"
                               class="block w-full rounded-lg border border-slate-300 pl-3 pr-10 py-2 text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-hidden">
                            <!-- Eye icon -->
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye slash icon -->
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 014.132-5.4M9.88 9.88a3 3 0 104.24 4.24M10.8 4.8A10.016 10.016 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.4M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="remember" class="ml-2 block text-sm font-medium text-slate-700">Ingat Saya</label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            :disabled="loading"
                            class="flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed items-center gap-2">
                        <span x-show="loading" class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full" style="display: none;"></span>
                        <span x-text="loading ? 'Memproses...' : 'Masuk'">Masuk</span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Copyright Section -->
        <div class="mt-8 text-center text-xs text-slate-400">
            {{ \App\Models\Setting::getByKey('copyright', 'Copyright 2026 @ Asosiasi Futsal Provinsi Jawa Barat') }}
        </div>
    </div>
</body>
</html>
