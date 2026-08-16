<?php

namespace App\Enums;

enum MatchCondition: string
{
    case NORMAL = 'normal';
    case DELAYED = 'delayed';
    case INTERRUPTED = 'interrupted';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::DELAYED => 'Ditunda (Delayed)',
            self::INTERRUPTED => 'Terhenti Sementara (Interrupted)',
            self::ABANDONED => 'Dihentikan/Batal (Abandoned)',
        };
    }
}
