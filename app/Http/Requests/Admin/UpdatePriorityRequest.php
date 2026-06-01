<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('priorities')->ignore($this->route('priority'))->whereNull('deleted_at')],
            'color' => ['nullable', 'string', 'max:20'],
            'level' => ['required', 'integer', Rule::unique('priorities')->ignore($this->route('priority'))->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama prioritas wajib diisi.',
            'name.unique' => 'Nama prioritas ini sudah digunakan.',
            'level.required' => 'Level angka wajib diisi.',
            'level.unique' => 'Level angka ini sudah digunakan oleh prioritas lain.',
        ];
    }
}
