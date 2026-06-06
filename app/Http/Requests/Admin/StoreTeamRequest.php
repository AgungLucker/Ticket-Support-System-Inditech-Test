<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('teams')->whereNull('deleted_at')],
            'description'   => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tim wajib diisi.',
            'name.unique'   => 'Nama tim ini sudah digunakan.',
        ];
    }
}
