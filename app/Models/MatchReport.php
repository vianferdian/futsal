<?php

namespace App\Models;

use App\Enums\MatchCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'attendance',
        'match_condition',
        'violation_potential',
        'violation_notes',
        'supervisor_notes',
        'submitted_by',
        'submitted_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'match_condition' => MatchCondition::class,
            'violation_potential' => 'boolean',
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class);
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
