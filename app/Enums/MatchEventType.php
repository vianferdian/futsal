<?php

namespace App\Enums;

enum MatchEventType: string
{
    case GOAL = 'goal';
    case OWN_GOAL = 'own_goal';
    case PENALTY_GOAL = 'penalty_goal';
    case SECOND_PENALTY_GOAL = 'second_penalty_goal';
    case PENALTY_MISS = 'penalty_miss';
    case YELLOW_CARD = 'yellow_card';
    case SECOND_YELLOW = 'second_yellow';
    case RED_CARD = 'red_card';
    case FOUL = 'foul';
    case TIMEOUT = 'timeout';
    case OFFICIAL_YELLOW = 'official_yellow';
    case OFFICIAL_RED = 'official_red';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::GOAL => 'Gol Normal',
            self::OWN_GOAL => 'Gol Bunuh Diri',
            self::PENALTY_GOAL => 'Gol Penalti',
            self::SECOND_PENALTY_GOAL => 'Gol Second Penalty',
            self::PENALTY_MISS => 'Gagal Penalti',
            self::YELLOW_CARD => 'Kartu Kuning',
            self::SECOND_YELLOW => 'Kartu Kuning Kedua',
            self::RED_CARD => 'Kartu Merah',
            self::FOUL => 'Pelanggaran (Foul)',
            self::TIMEOUT => 'Timeout',
            self::OFFICIAL_YELLOW => 'Kartu Kuning Official',
            self::OFFICIAL_RED => 'Kartu Merah Official',
            self::OTHER => 'Lain-lain',
        };
    }
}
