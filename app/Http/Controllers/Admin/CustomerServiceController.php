<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Mail\SupportReplyMail;
use Illuminate\Http\Request;
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

        // Send email notification with the full chat history to the customer's signup email
        Mail::to($message->user->email, $message->user->name)
            ->send(new SupportReplyMail($message));

        return back()->with('success', 'Reply sent. Email notification sent to ' . $message->user->email);
    }
}
