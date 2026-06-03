<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }

    public function markRead(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('tickets.index');

        return redirect($url);
    }
}
