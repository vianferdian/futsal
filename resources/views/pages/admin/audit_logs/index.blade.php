@extends('layouts.app')

@section('title', 'Audit Log Sistem')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit Log Sistem</h1>
        <p class="mt-2 text-sm text-slate-500">Pantau seluruh riwayat aktivitas, pencatatan skor, verifikasi lineup, dan modifikasi data dalam sistem.</p>
    </div>

    <!-- Filter Card -->
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <!-- Search Text -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Cari Aktivitas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari deskripsi tindakan..." class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-slate-900 shadow-xs placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                </div>
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Aktor (Pengguna)</label>
                <select name="user_id" onchange="this.form.submit()" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                    <option value="">-- Semua Aktor --</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role->label() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Match Filter -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pertandingan</label>
                <select name="match_id" onchange="this.form.submit()" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm bg-white">
                    <option value="">-- Semua Match --</option>
                    @foreach ($matches as $m)
                        <option value="{{ $m->id }}" {{ $matchId == $m->id ? 'selected' : '' }}>
                            {{ $m->match_number }} ({{ $m->homeTeam->short_name }} vs {{ $m->awayTeam->short_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter -->
            <div class="flex gap-2 w-full">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tanggal</label>
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-hidden sm:text-sm">
                </div>
                @if ($search || $userId || $matchId || $date)
                    <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex justify-center items-center rounded-lg border border-transparent px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 h-[38px] mt-auto">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
        @if ($logs->isEmpty())
            <div class="p-8 text-center text-slate-500 bg-white">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-4 text-sm font-semibold">Tidak ada log aktivitas ditemukan</p>
                <p class="mt-1 text-xs text-slate-400">Silakan ubah filter pencarian Anda.</p>
            </div>
        @else
            <div class="min-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengguna (Aktor)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pertandingan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tindakan / Aktivitas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white text-xs">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500 font-mono">
                                    {{ $log->created_at->format('d M Y H:i:s') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($log->user)
                                        <div class="font-semibold text-slate-900">{{ $log->user->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $log->user->role->label() }}</div>
                                    @else
                                        <span class="text-slate-400">Sistem Otomatis</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($log->match)
                                        <div class="font-semibold text-slate-900">{{ $log->match->match_number }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $log->match->homeTeam->short_name }} vs {{ $log->match->awayTeam->short_name }}</div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <div class="font-medium leading-relaxed">{{ $log->action }}</div>
                                    @if ($log->subject_type)
                                        <div class="text-[9px] text-slate-400 mt-0.5">Tipe: {{ $log->subject_type }} (#{{ $log->subject_id }})</div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-slate-500">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
