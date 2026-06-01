<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:2048'],
            'attachments' => ['nullable', 'array', 'max:5'],
        ];

        // Admin boleh pilih customer sebagai requester
        if ($this->user()->isAdmin()) {
            $rules['created_by'] = ['required', 'exists:users,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul tiket wajib diisi.',
            'description.required' => 'Deskripsi tiket wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'priority_id.required' => 'Prioritas wajib dipilih.',
            'attachments.max' => 'Maksimal 5 lampiran yang diizinkan.',
            'attachments.*.mimes' => 'Format lampiran tidak diizinkan. Hanya jpg, jpeg, png, pdf, doc, docx, xls, xlsx.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 2MB.',
        ];
    }
}
