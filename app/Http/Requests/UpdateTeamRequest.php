<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'competition_id' => 'nullable|exists:competitions,id',
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'city' => 'required|string|max:100',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'status' => 'required|string|in:active,inactive',
        ];
    }
}
