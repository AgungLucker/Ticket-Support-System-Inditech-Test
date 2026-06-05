<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function show(Attachment $attachment)
    {
        Gate::authorize('view', $attachment);

        // Cek local disk 
        if (Storage::exists($attachment->path)) {
            return Storage::response($attachment->path, $attachment->original_name, [
                'Content-Type' => $attachment->mime_type,
            ]);
        }

        // Fallback
        if (Storage::disk('public')->exists($attachment->path)) {
            return Storage::disk('public')->response($attachment->path, $attachment->original_name, [
                'Content-Type' => $attachment->mime_type,
            ]);
        }

        abort(404);
    }
}
