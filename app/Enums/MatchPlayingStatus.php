<?php

namespace App\Enums;

enum MatchPlayingStatus: string
{
    case PLAYING = 'playing';
    case SUBSTITUTE = 'substitute';
    case NON_PLAYING = 'non_playing';

    public function label(): string
    {
        return match ($this) {
            self::PLAYING => 'Starting 5 (Playing)',
            self::SUBSTITUTE => 'Substitute (Cadangan)',
            self::NON_PLAYING => 'Non Playing',
        };
    }
}
