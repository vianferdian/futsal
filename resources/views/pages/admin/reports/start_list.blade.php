<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Start List - {{ $match->match_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .no-print-btn {
            background-color: #2563eb;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 12px;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
        }
        .no-print-btn:hover {
            background-color: #1d4ed8;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 4px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .match-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .match-info-table th, .match-info-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .match-info-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .grid-container {
            display: flex;
            gap: 15px;
            width: 100%;
            margin-bottom: 20px;
        }
        .column {
            flex: 1;
            border: 1px solid #000;
            padding: 10px;
        }
        .column h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 12px;
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .roster-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .roster-table th, .roster-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        .roster-table th {
            background-color: #f9fafb;
            font-size: 10px;
            text-transform: uppercase;
        }
        .roster-table td.center {
            text-align: center;
        }
        .roster-table tr.starter {
            font-weight: bold;
            background-color: #fafafa;
        }

        .jersey-badge {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .officials-list {
            margin-top: 10px;
            font-size: 10px;
        }
        .officials-list div {
            margin-bottom: 3px;
        }

        .footer-sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .footer-sig-table td {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
            height: 80px;
        }
        .sig-line {
            width: 150px;
            border-bottom: 1px solid #000;
            margin: 0 auto 5px auto;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print Button -->
    <div class="no-print">
        <button onclick="window.print()" class="no-print-btn">
            Print / Simpan PDF
        </button>
    </div>

    <!-- Main start list document -->
    <table class="header-table">
        <tr>
            <td class="title">Daftar Susunan Pemain (Start List)</td>
        </tr>
    </table>

    <!-- Match details -->
    <table class="match-info-table">
        <tr>
            <th>Kompetisi</th>
            <td>{{ $match->competition->name }} (Season {{ $match->competition->season }})</td>
            <th>No. Pertandingan</th>
            <td><strong>{{ $match->match_number }}</strong></td>
        </tr>
        <tr>
            <th>Babak / Grup</th>
            <td>{{ $match->round }} @if($match->group_name) / {{ $match->group_name }} @endif</td>
            <th>Tanggal / Jam</th>
            <td>{{ $match->match_date->format('d M Y') }} / {{ substr($match->kickoff_time, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <th>Venue</th>
            <td>{{ $match->venue->name }} ({{ $match->venue->city }})</td>
            <th>Pengawas</th>
            <td>
                @foreach ($match->supervisors as $spv)
                    {{ $spv->name }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </td>
        </tr>
    </table>

    <div class="grid-container">
        
        <!-- Home Team Column -->
        <div class="column">
            <h3>HOME: {{ $match->homeTeam->name }}</h3>
            
            <!-- Home Jersey preview -->
            @if ($homeJersey)
                <div style="margin-bottom: 10px; font-size: 10px;">
                    <strong>Jersey Pemain:</strong> 
                    <span class="jersey-badge" style="background-color: {{ $homeJersey->player_jersey_color }}"></span> Baju, 
                    <span class="jersey-badge" style="background-color: {{ $homeJersey->player_short_color }}"></span> Celana, 
                    <span class="jersey-badge" style="background-color: {{ $homeJersey->player_socks_color }}"></span> Kaki
                    <br>
                    <strong>Jersey Kiper:</strong> 
                    <span class="jersey-badge" style="background-color: {{ $homeJersey->goalkeeper_jersey_color }}"></span> Baju, 
                    <span class="jersey-badge" style="background-color: {{ $homeJersey->goalkeeper_short_color }}"></span> Celana, 
                    <span class="jersey-badge" style="background-color: {{ $homeJersey->goalkeeper_socks_color }}"></span> Kaki
                </div>
            @endif

            <table class="roster-table">
                <thead>
                    <tr>
                        <th style="width: 25px; text-align: center;">#</th>
                        <th>Nama Pemain</th>
                        <th>Posisi</th>
                        <th style="width: 70px; text-align: center;">Peran / Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($homeLineup && !$homeLineup->players->isEmpty())
                        @foreach ($homeLineup->players as $lp)
                            <tr class="{{ $lp->playing_status->value === 'playing' ? 'starter' : '' }}">
                                <td class="center font-bold">{{ $lp->player->shirt_number }}</td>
                                <td>{{ $lp->player->name }}</td>
                                <td>{{ $lp->position->label() }}</td>
                                <td class="center">
                                    @if ($lp->playing_status->value === 'playing')
                                        Starter
                                    @else
                                        Cadangan
                                    @endif
                                    @if ($lp->is_captain) (C) @endif
                                    @if ($lp->is_goalkeeper) (GK) @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="text-align: center; color: #777; font-style: italic;">Lineup belum diserahkan</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <h4 style="margin-bottom: 5px; font-size: 11px; text-transform: uppercase;">Official Tim</h4>
            <div class="officials-list">
                @if ($homeOfficials->isEmpty())
                    <div style="color: #777; font-style: italic;">Tidak ada official aktif terdaftar.</div>
                @else
                    @foreach ($homeOfficials as $off)
                        <div><strong>{{ $off->position->label() }}:</strong> {{ $off->name }}</div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Away Team Column -->
        <div class="column">
            <h3>AWAY: {{ $match->awayTeam->name }}</h3>
            
            <!-- Away Jersey preview -->
            @if ($awayJersey)
                <div style="margin-bottom: 10px; font-size: 10px;">
                    <strong>Jersey Pemain:</strong> 
                    <span class="jersey-badge" style="background-color: {{ $awayJersey->player_jersey_color }}"></span> Baju, 
                    <span class="jersey-badge" style="background-color: {{ $awayJersey->player_short_color }}"></span> Celana, 
                    <span class="jersey-badge" style="background-color: {{ $awayJersey->player_socks_color }}"></span> Kaki
                    <br>
                    <strong>Jersey Kiper:</strong> 
                    <span class="jersey-badge" style="background-color: {{ $awayJersey->goalkeeper_jersey_color }}"></span> Baju, 
                    <span class="jersey-badge" style="background-color: {{ $awayJersey->goalkeeper_short_color }}"></span> Celana, 
                    <span class="jersey-badge" style="background-color: {{ $awayJersey->goalkeeper_socks_color }}"></span> Kaki
                </div>
            @endif

            <table class="roster-table">
                <thead>
                    <tr>
                        <th style="width: 25px; text-align: center;">#</th>
                        <th>Nama Pemain</th>
                        <th>Posisi</th>
                        <th style="width: 70px; text-align: center;">Peran / Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($awayLineup && !$awayLineup->players->isEmpty())
                        @foreach ($awayLineup->players as $lp)
                            <tr class="{{ $lp->playing_status->value === 'playing' ? 'starter' : '' }}">
                                <td class="center font-bold">{{ $lp->player->shirt_number }}</td>
                                <td>{{ $lp->player->name }}</td>
                                <td>{{ $lp->position->label() }}</td>
                                <td class="center">
                                    @if ($lp->playing_status->value === 'playing')
                                        Starter
                                    @else
                                        Cadangan
                                    @endif
                                    @if ($lp->is_captain) (C) @endif
                                    @if ($lp->is_goalkeeper) (GK) @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="text-align: center; color: #777; font-style: italic;">Lineup belum diserahkan</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <h4 style="margin-bottom: 5px; font-size: 11px; text-transform: uppercase;">Official Tim</h4>
            <div class="officials-list">
                @if ($awayOfficials->isEmpty())
                    <div style="color: #777; font-style: italic;">Tidak ada official aktif terdaftar.</div>
                @else
                    @foreach ($awayOfficials as $off)
                        <div><strong>{{ $off->position->label() }}:</strong> {{ $off->name }}</div>
                    @endforeach
                @endif
            </div>
        </div>

    </div>

    <!-- Signatures -->
    <table class="footer-sig-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                Admin Tim (Home)
            </td>
            <td>
                <div class="sig-line"></div>
                Admin Tim (Away)
            </td>
            <td>
                <div class="sig-line"></div>
                Pengawas Pertandingan (PP)
            </td>
        </tr>
    </table>

</body>
</html>
