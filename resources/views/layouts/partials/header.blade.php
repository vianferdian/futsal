<header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-slate-200 bg-white px-4 shadow-xs sm:gap-x-6 sm:px-6 lg:px-8">
    
    <!-- Mobile hamburger button -->
    <button type="button" 
            class="-m-2.5 p-2.5 text-slate-700 md:hidden"
            @click="sidebarOpen = true">
        <span class="sr-only">Buka sidebar</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Divider for mobile -->
    <div class="h-6 w-px bg-slate-200 md:hidden" aria-hidden="true"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6 justify-between items-center">
        <div>
            <span class="text-sm font-medium text-slate-500">Sistem Informasi Pertandingan Futsal</span>
        </div>

        <div class="flex items-center gap-x-4 lg:gap-x-6">
            <!-- Logout Action -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="flex items-center text-sm font-medium text-slate-600 hover:text-red-600 transition-colors">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</header>
