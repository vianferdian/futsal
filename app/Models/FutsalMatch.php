<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FutsalMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'competition_id',
        'match_number',
        'round',
        'group_name',
        'home_team_id',
        'away_team_id',
        'venue_id',
        'match_date',
        'kickoff_time',
        'first_half_duration',
        'second_half_duration',
        'status',
        'home_score',
        'away_score',
        'home_first_half_score',
        'away_first_half_score',
        'current_period',
        'timer_status',
        'timer_started_at',
        'timer_paused_at',
        'elapsed_seconds',
        'started_at',
        'finished_at',
        'locked_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatchStatus::class,
            'match_date' => 'date',
            'timer_started_at' => 'datetime',
            'timer_paused_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(MatchAssignment::class, 'match_id');
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'match_assignments', 'match_id', 'user_id')
            ->where('assignment_type', 'supervisor');
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(MatchLineup::class, 'match_id');
    }

    public function jerseys(): HasMany
    {
        return $this->hasMany(MatchJersey::class, 'match_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(MatchReport::class, 'match_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
