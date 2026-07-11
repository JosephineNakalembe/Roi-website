<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Mail\SupportReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerServiceController extends Controller
{
    public function index()
    {
        $messages = CustomerMessage::with('user')->latest()->paginate(20);
        return view('admin.support.index', compact('messages'));
    }

    public function show(CustomerMessage $message)
    {
        $message->load('user');
        return view('admin.support.show', compact('message'));
    }

    public function update(Request $request, CustomerMessage $message)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'status' => ['nullable', 'in:open,answered,closed'],
        ]);

        $replies = $message->replies ?? [];
        $replies[] = [
            'sender' => 'admin',
            'message' => $data['message'],
            'created_at' => now()->toDateTimeString(),
        ];

        $message->update([
            'replies' => $replies,
            'status' => $data['status'] ?? 'answered',
            'seen_by_user' => false,
        ]);

        // Send email notification with the full chat history to the customer's signup email.
        // The reply is already persisted above, so a mail failure must not fail the request or
        // be reported as a success — log it and tell the admin the email did not go out.
        try {
            Mail::to($message->user->email, $message->user->name)
                ->send(new SupportReplyMail($message));
        } catch (\Throwable $e) {
            Log::error('Failed to send support reply email', [
                'customer_message_id' => $message->id,
                'recipient' => $message->user->email,
                'exception' => $e->getMessage(),
            ]);

            return back()->with('warning', 'Reply saved, but the email notification to ' . $message->user->email . ' could not be sent.');
        }

        return back()->with('success', 'Reply sent. Email notification sent to ' . $message->user->email);
    }
}
