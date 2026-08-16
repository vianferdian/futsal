@extends('layouts.app')

@section('title', 'Susunan Pemain & Jersey')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('team.dashboard') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Atur Susunan Pemain & Jersey</h1>
            <p class="mt-1 text-sm text-slate-500">Pertandingan: {{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</p>
        </div>
    </div>

    <!-- Rejection/Unlock Warning Banner -->
    @if ($lineup && $lineup->status === 'draft' && $lineup->unlock_reason)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="font-bold">Lineup Sebelumnya Ditolak/Dibuka Kembali</h4>
                    <p class="mt-1 text-xs">Alasan dari Pengawas Pertandingan:</p>
                    <p class="mt-1 font-semibold text-xs italic bg-white/50 p-2 rounded-md border border-red-200">{{ $lineup->unlock_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Lock State Banner -->
    @if ($lineup && in_array($lineup->status, ['submitted', 'verified']))
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div>
                    <strong>Susunan Pemain Terkunci (Status: {{ ucfirst($lineup->status) }}).</strong> Anda tidak dapat mengubah susunan pemain kecuali dibuka kunci oleh Pengawas Pertandingan.
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('team.matches.lineup.save', $match->id) }}" method="POST" class="space-y-6" x-data="{ actionType: 'draft' }">
        @csrf

        @php
            $isLocked = $lineup && in_array($lineup->status, ['submitted', 'verified']);
        @endphp


        <!-- 2. Players Selection Card -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pendaftaran Skuad & Susunan Pemain</h2>
                    <p class="text-xs text-slate-400 mt-1">Tentukan Starting 5, Kapten, dan Penjaga Gawang utama tim Anda.</p>
                </div>
                <div class="text-xs font-bold text-slate-400">
                    SQUAD SIZE: 5 - 14 PEMAIN
                </div>
            </div>

            @if ($players->isEmpty())
                <div class="p-8 text-center text-slate-500">
                    Tim Anda belum memiliki pemain aktif. Silakan hubungi Admin untuk pendataan pemain.
                </div>
            @else
                <div class="min-w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-16">No. Punggung</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Pemain</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Posisi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Bermain</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Kiper Utama (GK)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Kapten (C)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($players as $player)
                                @php
                                    $lp = $lineupPlayers->get($player->id);
                                    $statusVal = $lp ? $lp->playing_status->value : 'non_playing';
                                    $isGK = $lp ? $lp->is_goalkeeper : false;
                                    $isCpt = $lp ? $lp->is_captain : false;
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-6 py-4 font-bold text-slate-900 text-center bg-slate-50/50">
                                        {{ $player->shirt_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">
                                        {{ $player->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                        {{ $player->position->label() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <select name="players[{{ $player->id }}][playing_status]" {{ $isLocked ? 'disabled' : '' }} 
                                                class="block w-48 rounded-lg border border-slate-300 px-2 py-1 text-slate-900 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 sm:text-xs bg-white">
                                            <option value="non_playing" {{ $statusVal === 'non_playing' ? 'selected' : '' }}>Tidak Bermain (Non-Playing)</option>
                                            <option value="playing" {{ $statusVal === 'playing' ? 'selected' : '' }}>Starting 5 (Playing)</option>
                                            <option value="substitute" {{ $statusVal === 'substitute' ? 'selected' : '' }}>Substitute (Cadangan)</option>
                                        </select>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <input type="radio" name="goalkeeper_id" value="{{ $player->id }}" {{ $isGK ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <input type="radio" name="captain_id" value="{{ $player->id }}" {{ $isCpt ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- 3. GK & Captain Form Helper Bindings -->
        <!-- Because radio inputs return a single value of selected player, we will append these hidden values or handle in controller -->
        <input type="hidden" name="action" :value="actionType">

        <!-- Form Submission footer -->
        @if (!$isLocked)
            <div class="flex justify-end gap-3 items-center flex-wrap">
                <a href="{{ route('team.dashboard') }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" @click="actionType = 'draft'" class="inline-flex justify-center items-center rounded-lg border border-blue-300 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-xs hover:bg-blue-100">
                    Simpan Draft
                </button>
                <button type="submit" @click="actionType = 'submit'" class="inline-flex justify-center items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-700" onclick="return confirm('Apakah Anda yakin ingin mengirim lineup ini? Setelah dikirim, lineup akan dikunci.')">
                    Kirim Lineup (Lock)
                </button>
            </div>
        @endif
    </form>
</div>

<script>
document.addEventListener('submit', function(e) {
    const action = document.querySelector('input[name="action"]').value;
    if (action === 'submit') {
        // Validate radio buttons are checked
        const gkSelected = document.querySelector('input[name="goalkeeper_id"]:checked');
        const captainSelected = document.querySelector('input[name="captain_id"]:checked');
        
        if (!gkSelected) {
            alert('Harap pilih tepat 1 Kiper Utama (GK) untuk lineup utama.');
            e.preventDefault();
            return false;
        }

        if (!captainSelected) {
            alert('Harap pilih tepat 1 Kapten (C) untuk skuad.');
            e.preventDefault();
            return false;
        }

        // Dynamically add goalkeeper and captain fields inside the players array
        const gkId = gkSelected.value;
        const capId = captainSelected.value;

        // Set goalkeeper input helper
        const gkHelper = document.createElement('input');
        gkHelper.type = 'hidden';
        gkHelper.name = `players[${gkId}][is_goalkeeper]`;
        gkHelper.value = '1';
        e.target.appendChild(gkHelper);

        // Set captain input helper
        const capHelper = document.createElement('input');
        capHelper.type = 'hidden';
        capHelper.name = `players[${capId}][is_captain]`;
        capHelper.value = '1';
        e.target.appendChild(capHelper);
    } else {
        // For draft, map whatever is selected
        const gkSelected = document.querySelector('input[name="goalkeeper_id"]:checked');
        const captainSelected = document.querySelector('input[name="captain_id"]:checked');
        
        if (gkSelected) {
            const gkHelper = document.createElement('input');
            gkHelper.type = 'hidden';
            gkHelper.name = `players[${gkSelected.value}][is_goalkeeper]`;
            gkHelper.value = '1';
            e.target.appendChild(gkHelper);
        }
        if (captainSelected) {
            const capHelper = document.createElement('input');
            capHelper.type = 'hidden';
            capHelper.name = `players[${captainSelected.value}][is_captain]`;
            capHelper.value = '1';
            e.target.appendChild(capHelper);
        }
    }
});
</script>
@endsection
