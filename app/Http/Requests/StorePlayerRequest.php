<?php

namespace App\Http\Requests;

use App\Enums\PlayerPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlayerRequest extends FormRequest
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
            'shirt_number' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::when($this->input('status') === 'active', [
                    Rule::unique('players')->where(function ($query) {
                        return $query->where('team_id', $this->input('team_id'))
                                     ->where('status', 'active');
                    })
                ])
            ],
            'position' => ['required', Rule::enum(PlayerPosition::class)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'birth_date' => 'nullable|date',
            'identity_number' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'shirt_number.unique' => 'Nomor punggung ini sudah digunakan oleh pemain aktif lain di tim yang sama.',
        ];
    }
}
