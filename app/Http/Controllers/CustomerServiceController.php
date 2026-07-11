<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwnership;
use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerServiceController extends Controller
{
    use AuthorizesOwnership;

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

        Auth::user()->customerMessages()->create(array_merge($data, [
            'status' => 'open',
            'replies' => [],
            'seen_by_user' => true,
        ]));

        return back()->with('success', 'Your ticket has been opened. The admin will reply soon.');
    }

    public function reply(Request $request, CustomerMessage $message)
    {
        $this->authorizeOwnership($message);

        if ($message->status === 'closed') {
            return back()->withErrors(['message' => 'This ticket is closed. Please open a new ticket.']);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message->update([
            'replies' => $message->addReply('user', $data['message']),
            'status' => 'open',
        ]);

        return back()->with('success', 'Your reply has been sent.');
    }

    public function close(CustomerMessage $message)
    {
        $this->authorizeOwnership($message);

        $message->update(['status' => 'closed']);

        return back()->with('success', 'Your ticket has been closed.');
    }
}