<?php

namespace App\Enums;

enum PlayerPosition: string
{
    case GOALKEEPER = 'goalkeeper';
    case ANCHOR = 'anchor';
    case ALA = 'ala';
    case PIVOT = 'pivot';

    public function label(): string
    {
        return match ($this) {
            self::GOALKEEPER => 'Goalkeeper',
            self::ANCHOR => 'Anchor',
            self::ALA => 'Ala',
            self::PIVOT => 'Pivot',
        };
    }
}
