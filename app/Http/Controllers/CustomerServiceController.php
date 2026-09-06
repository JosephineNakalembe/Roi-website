<?php

namespace App\Http\Controllers;

use App\Mail\TicketOpenedAdminNotification;
use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerServiceController extends Controller
{
    public function index()
    {
        $messages = Auth::user()->customerMessages()->latest()->get();

        // Mark all messages as seen by user when they visit the help page
        Auth::user()->customerMessages()
            ->where('seen_by_user', false)
            ->update(['seen_by_user' => true]);

        return view('customer-service.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = Auth::user()->customerMessages()->create(array_merge($data, [
            'status' => 'open',
            'replies' => [],
            'seen_by_user' => true,
        ]));

        // Notify the admin that a new support ticket has been opened
        try {
            $message->load('user');
            Mail::to(config('mail.admin_notification_address'))
                ->send(new TicketOpenedAdminNotification($message));
        } catch (\Exception $e) {
            Log::error('Failed to send ticket opened admin notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Your ticket has been opened. The admin will reply soon.');
    }

    public function reply(Request $request, CustomerMessage $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        if ($message->status === 'closed') {
            return back()->withErrors(['message' => 'This ticket is closed. Please open a new ticket.']);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $replies = $message->replies ?? [];
        $replies[] = [
            'sender' => 'user',
            'message' => $data['message'],
            'created_at' => now()->toDateTimeString(),
        ];

        $message->update([
            'replies' => $replies,
            'status' => 'open',
        ]);

        return back()->with('success', 'Your reply has been sent.');
    }

    public function close(CustomerMessage $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->update(['status' => 'closed']);

        return back()->with('success', 'Your ticket has been closed.');
    }
}