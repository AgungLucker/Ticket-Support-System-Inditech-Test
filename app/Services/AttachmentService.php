<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class AttachmentService
{
    /**
     * Store multiple uploaded files and create attachment records.
     * $context is used for the activity log (e.g. 'ticket', 'comment', 'internal note').
     */
    public function storeMany(array $files, Model $attachable, int $uploadedBy, Ticket $ticket, string $context): void
    {
        foreach ($files as $file) {
            if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
                continue;
            }
            $path = $file->store('attachments', 'public');
            $attachable->attachments()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'stored_name'   => basename($path),
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => $uploadedBy,
            ]);
            ActivityLogger::log($ticket, 'attachment_uploaded', $context, $file->getClientOriginalName());
        }
    }
}
