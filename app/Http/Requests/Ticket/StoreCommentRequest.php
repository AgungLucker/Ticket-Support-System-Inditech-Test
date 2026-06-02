<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'          => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['sometimes', 'boolean'],
            'attachments'      => ['nullable', 'array', 'max:5'],
            'attachments.*'    => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required'    => 'Isi komentar tidak boleh kosong.',
            'attachments.max'     => 'Maksimal 5 lampiran.',
            'attachments.*.mimes' => 'Format file tidak didukung.',
            'attachments.*.max'   => 'Ukuran file maksimal 2MB.',
        ];
    }
}
