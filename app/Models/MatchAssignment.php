<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'user_id',
        'assignment_type',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
