<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'name',
        'short_name',
        'logo',
        'city',
        'primary_color',
        'secondary_color',
        'status',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function officials(): HasMany
    {
        return $this->hasMany(TeamOfficial::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
