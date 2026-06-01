<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSlaRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'priority_id' => ['required', 'exists:priorities,id', 'unique:sla_rules,priority_id'],
            'response_time_hours' => ['required', 'integer', 'min:1'],
            'resolution_time_hours' => ['required', 'integer', 'min:1', 'gte:response_time_hours'],
        ];
    }

    public function messages(): array
    {
        return [
            'priority_id.required' => 'Prioritas wajib dipilih.',
            'priority_id.unique' => 'Prioritas ini sudah memiliki aturan SLA.',
            'response_time_hours.required' => 'Target Respon wajib diisi.',
            'resolution_time_hours.required' => 'Target Penyelesaian wajib diisi.',
            'resolution_time_hours.gte' => 'Target Penyelesaian tidak boleh lebih cepat (angkanya lebih kecil) dari Target Respon awal.',
        ];
    }
}
