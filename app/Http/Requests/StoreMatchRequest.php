<?php

namespace App\Http\Requests;

use App\Enums\MatchStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'competition_id' => 'required|exists:competitions,id',
            'round' => 'required|string|max:50',
            'group_name' => 'nullable|string|max:50',
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'venue_id' => 'required|exists:venues,id',
            'match_date' => 'required|date',
            'kickoff_time' => [
                'required',
                function ($attribute, $value, $fail) {
                    $matchDate = $this->input('match_date');
                    $venueId = $this->input('venue_id');
                    
                    if ($matchDate && $venueId) {
                        $exists = \App\Models\FutsalMatch::where('venue_id', $venueId)
                            ->whereDate('match_date', $matchDate)
                            ->where('kickoff_time', $value)
                            ->exists();
                            
                        if ($exists) {
                            $fail('Jadwal bentrok! Venue sudah digunakan oleh pertandingan lain pada tanggal dan jam yang sama.');
                        }
                    }
                }
            ],
            'status' => ['required', Rule::enum(MatchStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'away_team_id.different' => 'Tim Home dan Tim Away tidak boleh sama.',
        ];
    }
}
