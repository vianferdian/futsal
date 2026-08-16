<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Player;
use App\Models\TeamOfficial;
use App\Models\Venue;
use App\Models\FutsalMatch;
use App\Models\MatchAssignment;
use App\Models\Setting;
use App\Enums\UserRole;
use App\Enums\MatchStatus;
use App\Enums\PlayerPosition;
use App\Enums\TeamOfficialPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Settings
        Setting::setByKey('system_name', 'Sistem Scoring Futsal Indonesia');
        Setting::setByKey('default_half_duration', '20');
        Setting::setByKey('max_fouls_before_penalty', '5');
        Setting::setByKey('max_timeout_per_period', '1');
        Setting::setByKey('pdf_footer', 'Sistem Futsal Scoring - Dokumen Resmi');
        Setting::setByKey('timezone', 'Asia/Jakarta');
        Setting::setByKey('copyright', 'Copyright 2026 @ Asosiasi Futsal Provinsi Jawa Barat');


        // 2. Seed Admin & Supervisors
        $admin = User::create([
            'name' => 'Administrator Utama',
            'username' => 'admin',
            'email' => 'admin@futsal.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'status' => 'active',
            'phone' => '081234567890',
        ]);

        $supervisor1 = User::create([
            'name' => 'Pengawas Budi',
            'username' => 'supervisor1',
            'email' => 'spv1@futsal.com',
            'password' => Hash::make('password'),
            'role' => UserRole::SUPERVISOR,
            'status' => 'active',
            'phone' => '081234567891',
        ]);

        $supervisor2 = User::create([
            'name' => 'Pengawas Joko',
            'username' => 'supervisor2',
            'email' => 'spv2@futsal.com',
            'password' => Hash::make('password'),
            'role' => UserRole::SUPERVISOR,
            'status' => 'active',
            'phone' => '081234567892',
        ]);

        // 3. Seed Competition
        $competition = Competition::create([
            'name' => 'Liga Futsal Indonesia 2026',
            'short_name' => 'LFI 2026',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'description' => 'Liga Futsal kasta tertinggi di Indonesia musim 2026.',
            'status' => 'active',
        ]);

        // 4. Seed Teams
        $teamsData = [
            [
                'name' => 'Bintang Timur Surabaya',
                'short_name' => 'BTS',
                'city' => 'Surabaya',
                'primary_color' => '#008000', // Green
                'secondary_color' => '#ffffff',
            ],
            [
                'name' => 'Black Steel Papua',
                'short_name' => 'BSP',
                'city' => 'Manokwari',
                'primary_color' => '#000000', // Black
                'secondary_color' => '#ffcc00', // Yellow
            ],
            [
                'name' => 'Cosmo JNE Jakarta',
                'short_name' => 'CSM',
                'city' => 'Jakarta',
                'primary_color' => '#ff6600', // Orange
                'secondary_color' => '#0033cc', // Blue
            ],
            [
                'name' => 'Pendekar United',
                'short_name' => 'PKU',
                'city' => 'Tangerang',
                'primary_color' => '#330033', // Purple
                'secondary_color' => '#ffffff',
            ],
        ];

        $teams = [];
        foreach ($teamsData as $index => $tData) {
            $team = Team::create(array_merge($tData, [
                'competition_id' => $competition->id,
                'status' => 'active',
            ]));
            $teams[] = $team;

            // Seed Team Admin User
            User::create([
                'name' => 'Admin ' . $team->name,
                'username' => 'admin_team' . ($index + 1),
                'email' => 'team' . ($index + 1) . '@futsal.com',
                'password' => Hash::make('password'),
                'role' => UserRole::TEAM_ADMIN,
                'team_id' => $team->id,
                'status' => 'active',
                'phone' => '08123456789' . ($index + 3),
            ]);

            // Seed Players (12-15 Players)
            $firstNames = ['Ahmad', 'Dimas', 'Rian', 'Bayu', 'Bambang', 'Doni', 'Fajar', 'Satria', 'Eko', 'Rudi', 'Gerry', 'Hadi', 'Ilham', 'Joni', 'Kiki'];
            $lastNames = ['Pratama', 'Hidayat', 'Saputra', 'Wijaya', 'Kusuma', 'Santoso', 'Gunawan', 'Laksana', 'Pamungkas', 'Nugroho', 'Wibowo', 'Firmansyah', 'Subagja', 'Pambudi', 'Putra'];
            
            $positions = [
                PlayerPosition::GOALKEEPER,
                PlayerPosition::GOALKEEPER,
                PlayerPosition::ANCHOR,
                PlayerPosition::ANCHOR,
                PlayerPosition::ANCHOR,
                PlayerPosition::ALA,
                PlayerPosition::ALA,
                PlayerPosition::ALA,
                PlayerPosition::ALA,
                PlayerPosition::ALA,
                PlayerPosition::PIVOT,
                PlayerPosition::PIVOT,
                PlayerPosition::PIVOT,
            ];

            for ($i = 1; $i <= count($positions); $i++) {
                $pName = $firstNames[($index + $i) % count($firstNames)] . ' ' . $lastNames[($index * 2 + $i) % count($lastNames)];
                Player::create([
                    'team_id' => $team->id,
                    'name' => $pName,
                    'shirt_number' => $i + ($i > 5 ? ($index * 3) : 0), // Spread numbers to avoid duplicates
                    'position' => $positions[$i - 1],
                    'birth_date' => '2000-01-' . str_pad($i + $index, 2, '0', STR_PAD_LEFT),
                    'status' => 'active',
                ]);
            }

            // Seed Officials (4 Officials)
            $officialPositions = [
                TeamOfficialPosition::HEAD_COACH,
                TeamOfficialPosition::ASSISTANT_COACH,
                TeamOfficialPosition::TEAM_MANAGER,
                TeamOfficialPosition::KITMAN,
            ];

            $offNames = ['Coach ' . $team->short_name, 'Assistant ' . $team->short_name, 'Manager ' . $team->short_name, 'Kitman ' . $team->short_name];

            for ($i = 0; $i < count($officialPositions); $i++) {
                TeamOfficial::create([
                    'team_id' => $team->id,
                    'name' => $offNames[$i],
                    'position' => $officialPositions[$i],
                    'status' => 'active',
                ]);
            }
        }

        // 5. Seed Venues
        $venue1 = Venue::create([
            'name' => 'GOR Bima Cirebon',
            'city' => 'Cirebon',
            'address' => 'Jl. Bypass Sunyaragi, Cirebon',
            'capacity' => 2000,
            'status' => 'active',
        ]);

        $venue2 = Venue::create([
            'name' => 'GOR UNY Yogyakarta',
            'city' => 'Yogyakarta',
            'address' => 'Jl. Colombo No.1, Yogyakarta',
            'capacity' => 5000,
            'status' => 'active',
        ]);

        // 6. Seed Matches (4 Matches: 2 today, 2 upcoming/finished)
        
        // Match 1: Today, Live (status: first_half)
        $match1 = FutsalMatch::create([
            'competition_id' => $competition->id,
            'match_number' => 'M001',
            'round' => 'Babak Grup',
            'group_name' => 'Grup A',
            'home_team_id' => $teams[0]->id, // BTS
            'away_team_id' => $teams[1]->id, // BSP
            'venue_id' => $venue1->id,
            'match_date' => today(),
            'kickoff_time' => '13:00:00',
            'first_half_duration' => 20,
            'second_half_duration' => 20,
            'status' => MatchStatus::FIRST_HALF,
            'home_score' => 2,
            'away_score' => 1,
            'current_period' => 'first_half',
            'timer_status' => 'running',
            'timer_started_at' => now(),
            'elapsed_seconds' => 450, // 7.5 mins
            'started_at' => now()->subMinutes(10),
            'created_by' => $admin->id,
        ]);

        MatchAssignment::create([
            'match_id' => $match1->id,
            'user_id' => $supervisor1->id,
            'assignment_type' => 'supervisor',
        ]);

        // Match 2: Today, Draft/Waiting lineup
        $match2 = FutsalMatch::create([
            'competition_id' => $competition->id,
            'match_number' => 'M002',
            'round' => 'Babak Grup',
            'group_name' => 'Grup A',
            'home_team_id' => $teams[2]->id, // Cosmo
            'away_team_id' => $teams[3]->id, // Pendekar
            'venue_id' => $venue1->id,
            'match_date' => today(),
            'kickoff_time' => '15:30:00',
            'first_half_duration' => 20,
            'second_half_duration' => 20,
            'status' => MatchStatus::WAITING_LINEUP,
            'home_score' => 0,
            'away_score' => 0,
            'created_by' => $admin->id,
        ]);

        MatchAssignment::create([
            'match_id' => $match2->id,
            'user_id' => $supervisor2->id,
            'assignment_type' => 'supervisor',
        ]);

        // Match 3: Future
        $match3 = FutsalMatch::create([
            'competition_id' => $competition->id,
            'match_number' => 'M003',
            'round' => 'Babak Grup',
            'group_name' => 'Grup A',
            'home_team_id' => $teams[0]->id, // BTS
            'away_team_id' => $teams[2]->id, // Cosmo
            'venue_id' => $venue2->id,
            'match_date' => today()->addDays(2),
            'kickoff_time' => '13:00:00',
            'first_half_duration' => 20,
            'second_half_duration' => 20,
            'status' => MatchStatus::DRAFT,
            'home_score' => 0,
            'away_score' => 0,
            'created_by' => $admin->id,
        ]);

        MatchAssignment::create([
            'match_id' => $match3->id,
            'user_id' => $supervisor1->id,
            'assignment_type' => 'supervisor',
        ]);

        // Match 4: Finished/Locked
        $match4 = FutsalMatch::create([
            'competition_id' => $competition->id,
            'match_number' => 'M004',
            'round' => 'Babak Grup',
            'group_name' => 'Grup A',
            'home_team_id' => $teams[1]->id, // BSP
            'away_team_id' => $teams[3]->id, // Pendekar
            'venue_id' => $venue2->id,
            'match_date' => today()->subDays(1),
            'kickoff_time' => '15:30:00',
            'first_half_duration' => 20,
            'second_half_duration' => 20,
            'status' => MatchStatus::LOCKED,
            'home_score' => 4,
            'away_score' => 2,
            'started_at' => now()->subDays(1)->subHours(2),
            'finished_at' => now()->subDays(1)->subHour(),
            'locked_at' => now()->subDays(1)->subHour(),
            'created_by' => $admin->id,
        ]);

        MatchAssignment::create([
            'match_id' => $match4->id,
            'user_id' => $supervisor2->id,
            'assignment_type' => 'supervisor',
        ]);
        
        // Seed some audit logs
        \App\Models\AuditLog::create([
            'user_id' => $admin->id,
            'match_id' => $match1->id,
            'action' => 'Membuat pertandingan M001 BTS vs BSP',
            'subject_type' => 'Match',
            'subject_id' => $match1->id,
        ]);

        \App\Models\AuditLog::create([
            'user_id' => $admin->id,
            'match_id' => $match2->id,
            'action' => 'Membuat pertandingan M002 Cosmo vs Pendekar',
            'subject_type' => 'Match',
            'subject_id' => $match2->id,
        ]);

        \App\Models\AuditLog::create([
            'user_id' => $supervisor1->id,
            'match_id' => $match1->id,
            'action' => 'Memulai babak pertama pertandingan M001',
            'subject_type' => 'Match',
            'subject_id' => $match1->id,
        ]);
    }
}
