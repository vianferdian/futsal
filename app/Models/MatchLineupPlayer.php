<?php

namespace App\Models;

use App\Enums\MatchPlayingStatus;
use App\Enums\PlayerPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLineupPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_lineup_id',
        'player_id',
        'playing_status',
        'position',
        'is_goalkeeper',
        'is_captain',
    ];

    protected function casts(): array
    {
        return [
            'playing_status' => MatchPlayingStatus::class,
            'position' => PlayerPosition::class,
            'is_goalkeeper' => 'boolean',
            'is_captain' => 'boolean',
        ];
    }

    public function lineup(): BelongsTo
    {
        return $this->belongsTo(MatchLineup::class, 'match_lineup_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
