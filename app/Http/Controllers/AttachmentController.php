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

        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(404);
        }

        return Storage::disk('public')->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }
}
