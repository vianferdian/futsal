<?php

namespace App\Http\Requests;

use App\Enums\TeamOfficialPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfficialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'position' => ['required', Rule::enum(TeamOfficialPosition::class)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|string|in:active,inactive',
        ];
    }
}
