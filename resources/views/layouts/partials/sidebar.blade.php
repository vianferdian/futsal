@php
    $role = auth()->user()->role->value;
    $currentRoute = request()->route()->getName();
@endphp

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" 
     x-cloak
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-slate-900/80 md:hidden" 
     @click="sidebarOpen = false">
</div>

<!-- Sidebar Component -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out md:translate-x-0">
    
    <!-- Logo area -->
    <div class="flex h-16 items-center border-b border-slate-200 px-6 justify-between">
        <div class="flex items-center gap-2">
            <img src="{{ asset('afp_jabar.png') }}" alt="Asosiasi Futsal" class="h-10 w-auto object-contain rounded">
        </div>
        <button class="md:hidden text-slate-500 hover:text-slate-900" @click="sidebarOpen = false">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation links -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
        @if ($role === 'admin')
            <!-- Admin Navigation -->
            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Overview</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $currentRoute === 'admin.dashboard' ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="mr-3 h-5 w-5 {{ $currentRoute === 'admin.dashboard' ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                        </svg>
                        Dashboard
                    </a>
                </div>
            </div>

            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Pertandingan</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.matches.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ (request()->routeIs('admin.matches.*') && request()->query('status') !== 'finished') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Jadwal
                    </a>
                    <a href="{{ route('admin.matches.index', ['status' => 'finished']) }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ (request()->routeIs('admin.matches.*') && request()->query('status') === 'finished') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Hasil Pertandingan
                    </a>
                </div>
            </div>

            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Master Data</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.competitions.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.competitions.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Kompetisi
                    </a>
                    <a href="{{ route('admin.teams.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.teams.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Tim
                    </a>
                    <a href="{{ route('admin.players.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.players.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Pemain
                    </a>
                    <a href="{{ route('admin.officials.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.officials.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Official
                    </a>
                    <a href="{{ route('admin.venues.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.venues.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Venue
                    </a>
                </div>
            </div>

            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Pengguna</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.users.supervisors.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.supervisors.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Pengawas
                    </a>
                    <a href="{{ route('admin.users.team-admins.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.team-admins.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Admin Tim
                    </a>
                    <a href="{{ route('admin.users.admins.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.admins.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Administrator
                    </a>
                </div>
            </div>

            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">System</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.settings.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Pengaturan
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.audit-logs.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Audit Log
                    </a>
                </div>
            </div>

        @elseif ($role === 'supervisor')
            <!-- Supervisor Navigation -->
            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Overview</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('supervisor.dashboard') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ ($currentRoute === 'supervisor.dashboard' && !request()->query('status')) ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Dashboard / Tugas
                    </a>
                    <a href="{{ route('supervisor.dashboard', ['status' => 'finished']) }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ (request()->query('status') === 'finished') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Ringkasan Pertandingan
                    </a>
                </div>
            </div>

        @elseif ($role === 'team_admin')
            <!-- Team Admin Navigation -->
            <div>
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Team Overview</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('team.dashboard') }}" @click="sidebarOpen = false" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ $currentRoute === 'team.dashboard' ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Dashboard / Jadwal Tim
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- User area -->
    <div class="border-t border-slate-200 p-4">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-950 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ auth()->user()->role->label() }}</p>
            </div>
        </div>
    </div>
</aside>
