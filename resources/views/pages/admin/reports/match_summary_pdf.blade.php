<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Match Summary - {{ $match->match_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .header h2 {
            font-size: 12px;
            margin: 3px 0 0 0;
            color: #475569;
            font-weight: normal;
        }

        .match-meta {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .match-meta td {
            padding: 3px 0;
            font-size: 9px;
            vertical-align: top;
        }

        .scoreboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .scoreboard-table td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        .team-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .score-display {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 2px;
        }

        .halftime-score {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #0f172a;
            color: #ffffff;
            padding: 4px 8px;
            margin-top: 15px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .stats-table th, .stats-table td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            font-size: 9px;
            text-align: center;
        }

        .stats-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
        }

        .stats-table td.team-cell {
            text-align: left;
            font-weight: bold;
            width: 40%;
        }

        .timeline-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .timeline-table th, .timeline-table td {
            padding: 4px 6px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
        }

        .timeline-table th {
            background-color: #f8fafc;
            text-align: left;
            font-weight: bold;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
        }

        .roster-container {
            width: 100%;
            margin-top: 10px;
        }

        .roster-column {
            width: 49%;
            vertical-align: top;
        }

        .roster-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }

        .roster-table th, .roster-table td {
            padding: 3px 5px;
            font-size: 8.5px;
            border-bottom: 1px solid #f1f5f9;
        }

        .roster-table th {
            background-color: #f8fafc;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #e2e8f0;
        }

        .report-notes {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #e2e8f0;
            background-color: #fafafa;
        }

        .report-notes td {
            padding: 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .report-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <h1>Match Summary Report</h1>
        <h2>{{ $match->competition->name }}</h2>
    </div>

    <!-- Match Metadata -->
    <table class="match-meta">
        <tr>
            <td style="width: 35%;">
                <strong>Nomor Pertandingan:</strong> {{ $match->match_number }}<br>
                <strong>Babak / Grup:</strong> {{ $match->round }} @if($match->group_name) / {{ $match->group_name }} @endif<br>
                <strong>Tanggal:</strong> {{ $match->match_date->format('d F Y') }}
            </td>
            <td style="width: 35%;">
                <strong>Tempat (Venue):</strong> {{ $match->venue->name }}<br>
                <strong>Kickoff:</strong> {{ $match->kickoff_time }} WIB<br>
                <strong>Status:</strong> {{ $match->status->label() }}
            </td>
            <td style="width: 30%; text-align: right;">
                <strong>Pengawas:</strong><br>
                {{ $match->supervisors->pluck('name')->join(', ') ?: '-' }}
            </td>
        </tr>
    </table>

    <!-- Scoreboard -->
    <table class="scoreboard-table">
        <tr>
            <td style="width: 40%; text-align: right;">
                <span class="team-name">{{ $match->homeTeam->name }}</span>
            </td>
            <td style="width: 20%;">
                <span class="score-display">{{ $match->home_score }} - {{ $match->away_score }}</span>
                <div class="halftime-score">HT: {{ $match->home_first_half_score }} - {{ $match->away_first_half_score }}</div>
            </td>
            <td style="width: 40%; text-align: left;">
                <span class="team-name">{{ $match->awayTeam->name }}</span>
            </td>
        </tr>
    </table>

    <!-- Match Stats Summary -->
    <div class="section-title">Statistik Tim</div>
    <table class="stats-table">
        <thead>
            <tr>
                <th class="team-cell">Statistik / Akumulasi</th>
                <th colspan="3">{{ $match->homeTeam->short_name }}</th>
                <th colspan="3">{{ $match->awayTeam->short_name }}</th>
            </tr>
            <tr>
                <th class="team-cell"></th>
                <th>B1</th>
                <th>B2</th>
                <th>Total</th>
                <th>B1</th>
                <th>B2</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="team-cell">Pelanggaran (Fouls)</td>
                <td>{{ $homeFoulsB1 }}</td>
                <td>{{ $homeFoulsB2 }}</td>
                <td style="font-weight: bold;">{{ $homeFoulsB1 + $homeFoulsB2 }}</td>
                <td>{{ $awayFoulsB1 }}</td>
                <td>{{ $awayFoulsB2 }}</td>
                <td style="font-weight: bold;">{{ $awayFoulsB1 + $awayFoulsB2 }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Kejadian Penting (Events) -->
    <div class="section-title">Kejadian Penting (Timeline)</div>
    @if($events->isEmpty())
        <div style="text-align: center; padding: 10px; font-size: 9px; color: #64748b; border: 1px solid #e2e8f0;">
            Tidak ada kejadian penting yang dicatat.
        </div>
    @else
        <table class="timeline-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Babak</th>
                    <th style="width: 15%;">Tim</th>
                    <th style="width: 25%;">Aksi</th>
                    <th>Detail Pemain / Official</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    @php
                        $periodText = $event->period === 'first_half' ? 'B1' : 'B2';
                    @endphp
                    <tr>
                        <td style="font-family: monospace;">{{ $periodText }}</td>
                        <td>{{ $event->team->short_name }}</td>
                        <td style="font-weight: bold;">{{ $event->event_type->label() }}</td>
                        <td>
                            @if($event->player)
                                #{{ $event->player->shirt_number }} {{ $event->player->name }}
                                @if($event->relatedPlayer)
                                    (Assist: {{ $event->relatedPlayer->name }})
                                @endif
                            @elseif($event->official)
                                {{ $event->official->name }} (Official)
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="page-break"></div>

    <!-- Lineups & Rosters -->
    <div class="section-title">Daftar Susunan Pemain (DSP) & Lineup</div>
    <table class="roster-container" style="width: 100%;">
        <tr>
            <!-- Home Roster -->
            <td class="roster-column">
                <table class="roster-table">
                    <thead>
                        <tr>
                            <th colspan="3" style="background-color: #0f172a; color: white;">{{ $match->homeTeam->name }}</th>
                        </tr>
                        <tr>
                            <th style="width: 15%;">No</th>
                            <th style="width: 60%;">Nama Pemain</th>
                            <th>Posisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($homeLineup)
                            <!-- Starting -->
                            <tr>
                                <td colspan="3" style="background-color: #f1f5f9; font-weight: bold; font-size: 8px; text-transform: uppercase;">Starting 5</td>
                            </tr>
                            @foreach($homeLineup->players->where('playing_status.value', 'playing') as $lp)
                                <tr>
                                    <td>{{ $lp->player->shirt_number }}</td>
                                    <td>
                                        {{ $lp->player->name }}
                                        @if($lp->is_captain) <strong>(C)</strong> @endif
                                        @if($lp->is_goalkeeper) <strong>(GK)</strong> @endif
                                    </td>
                                    <td>{{ $lp->position->label() }}</td>
                                </tr>
                            @endforeach
                            <!-- Substitutes -->
                            <tr>
                                <td colspan="3" style="background-color: #f1f5f9; font-weight: bold; font-size: 8px; text-transform: uppercase;">Cadangan</td>
                            </tr>
                            @foreach($homeLineup->players->where('playing_status.value', 'substitute') as $lp)
                                <tr>
                                    <td>{{ $lp->player->shirt_number }}</td>
                                    <td>
                                        {{ $lp->player->name }}
                                        @if($lp->is_captain) <strong>(C)</strong> @endif
                                        @if($lp->is_goalkeeper) <strong>(GK)</strong> @endif
                                    </td>
                                    <td>{{ $lp->position->label() }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b;">Lineup belum terverifikasi</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>

            <!-- Spacer -->
            <td style="width: 2%;"></td>

            <!-- Away Roster -->
            <td class="roster-column">
                <table class="roster-table">
                    <thead>
                        <tr>
                            <th colspan="3" style="background-color: #0f172a; color: white;">{{ $match->awayTeam->name }}</th>
                        </tr>
                        <tr>
                            <th style="width: 15%;">No</th>
                            <th style="width: 60%;">Nama Pemain</th>
                            <th>Posisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($awayLineup)
                            <!-- Starting -->
                            <tr>
                                <td colspan="3" style="background-color: #f1f5f9; font-weight: bold; font-size: 8px; text-transform: uppercase;">Starting 5</td>
                            </tr>
                            @foreach($awayLineup->players->where('playing_status.value', 'playing') as $lp)
                                <tr>
                                    <td>{{ $lp->player->shirt_number }}</td>
                                    <td>
                                        {{ $lp->player->name }}
                                        @if($lp->is_captain) <strong>(C)</strong> @endif
                                        @if($lp->is_goalkeeper) <strong>(GK)</strong> @endif
                                    </td>
                                    <td>{{ $lp->position->label() }}</td>
                                </tr>
                            @endforeach
                            <!-- Substitutes -->
                            <tr>
                                <td colspan="3" style="background-color: #f1f5f9; font-weight: bold; font-size: 8px; text-transform: uppercase;">Cadangan</td>
                            </tr>
                            @foreach($awayLineup->players->where('playing_status.value', 'substitute') as $lp)
                                <tr>
                                    <td>{{ $lp->player->shirt_number }}</td>
                                    <td>
                                        {{ $lp->player->name }}
                                        @if($lp->is_captain) <strong>(C)</strong> @endif
                                        @if($lp->is_goalkeeper) <strong>(GK)</strong> @endif
                                    </td>
                                    <td>{{ $lp->position->label() }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b;">Lineup belum terverifikasi</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Post-Match Report Summary -->
    @if ($report)
        <div class="section-title">Laporan Pengawas Pertandingan</div>
        <table class="report-notes">
            <tr>
                <td class="report-label">Kondisi Pertandingan:</td>
                <td>{{ $report->match_condition->label() }}</td>
            </tr>
            <tr>
                <td class="report-label">Jumlah Penonton:</td>
                <td>{{ $report->attendance ? number_format($report->attendance) . ' orang' : 'Tidak dicatat' }}</td>
            </tr>
            <tr>
                <td class="report-label">Potensi Insiden:</td>
                <td style="font-weight: bold; color: {{ $report->violation_potential ? '#dc2626' : '#1e293b' }}">
                    {{ $report->violation_potential ? 'Ya' : 'Tidak' }}
                </td>
            </tr>
            @if($report->violation_notes)
                <tr>
                    <td class="report-label">Uraian Insiden:</td>
                    <td>{{ $report->violation_notes }}</td>
                </tr>
            @endif
            @if($report->supervisor_notes)
                <tr>
                    <td class="report-label">Catatan Pengawas:</td>
                    <td>{{ $report->supervisor_notes }}</td>
                </tr>
            @endif
            <tr>
                <td class="report-label">Tanda Tangan Pengawas:</td>
                <td style="font-size: 8px; color: #64748b; padding-top: 10px;">
                    Dilaporkan secara digital oleh <strong>{{ $report->submittedByUser?->name }}</strong> pada {{ $report->submitted_at?->format('d M Y H:i') }}
                </td>
            </tr>
        </table>
    @endif

</body>
</html>
