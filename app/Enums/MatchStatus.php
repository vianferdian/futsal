<?php

namespace App\Enums;

enum MatchStatus: string
{
    case DRAFT = 'draft';
    case WAITING_LINEUP = 'waiting_lineup';
    case LINEUP_SUBMITTED = 'lineup_submitted';
    case READY = 'ready';
    case FIRST_HALF = 'first_half';
    case HALFTIME = 'halftime';
    case SECOND_HALF = 'second_half';
    case FINISHED = 'finished';
    case LOCKED = 'locked';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::WAITING_LINEUP => 'Menunggu Lineup',
            self::LINEUP_SUBMITTED => 'Lineup Terkirim',
            self::READY => 'Siap',
            self::FIRST_HALF => 'Babak Pertama',
            self::HALFTIME => 'Jeda Babak',
            self::SECOND_HALF => 'Babak Kedua',
            self::FINISHED => 'Selesai',
            self::LOCKED => 'Terkunci',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
