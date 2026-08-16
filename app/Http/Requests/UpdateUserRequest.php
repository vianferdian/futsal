<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|max:255|unique:users,username,' . $userId,
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8',
            'role' => ['required', new Enum(UserRole::class)],
            'team_id' => 'required_if:role,team_admin|nullable|exists:teams,id',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|string|in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Username ini sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
            'team_id.required_if' => 'Tim wajib dipilih untuk peran Admin Tim.',
            'team_id.exists' => 'Tim yang dipilih tidak valid.',
            'photo.image' => 'Berkas harus berupa gambar.',
            'photo.max' => 'Ukuran gambar maksimal adalah 2MB.',
        ];
    }
}
