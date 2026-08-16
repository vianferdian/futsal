<?php

namespace App\Enums;

enum TeamOfficialPosition: string
{
    case HEAD_COACH = 'Head Coach';
    case ASSISTANT_COACH = 'Assistant Coach';
    case TEAM_MANAGER = 'Team Manager';
    case GOALKEEPER_COACH = 'Goalkeeper Coach';
    case PHYSIOTHERAPIST = 'Physiotherapist';
    case DOCTOR = 'Doctor';
    case MEDICAL_STAFF = 'Medical Staff';
    case KITMAN = 'Kitman';
    case OTHER = 'Other';

    public function label(): string
    {
        return $this->value;
    }
}
