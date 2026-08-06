<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->userNotifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(UserNotification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update(['read_at' => now()]);

        return redirect($notification->link ?? route('notifications.index'));
    }

    public function markAllRead()
    {
        Auth::user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
