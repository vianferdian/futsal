<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchJersey extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
        'player_jersey_color',
        'player_short_color',
        'player_socks_color',
        'goalkeeper_jersey_color',
        'goalkeeper_short_color',
        'goalkeeper_socks_color',
        'created_by',
        'updated_by',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
