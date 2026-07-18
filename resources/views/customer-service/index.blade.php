@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Customer Service</h1>
        </div>
    </div>
    <div class="card" style="max-width:900px;margin:0 auto;">
        <p class="text-muted">Send a complaint, ask a question, or follow up on an existing request.</p>

        <!-- Open New Ticket Button -->
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
            <button onclick="document.getElementById('newTicketForm').style.display = document.getElementById('newTicketForm').style.display === 'none' ? 'block' : 'none'" class="btn">
                + Open New Ticket
            </button>
        </div>

        <form method="POST" action="{{ route('customer-service.store') }}" id="newTicketForm" style="display:none;gap:12px;margin-bottom:24px;">
            @csrf
            <label>Subject</label>
            <input class="input" name="subject" placeholder="Message subject" required>
            <label>Message</label>
            <textarea class="input" name="message" rows="4" placeholder="Describe your issue or question" required></textarea>
            <div style="display:flex;justify-content:flex-end;">
                <button class="btn" type="submit">Send Message</button>
            </div>
        </form>

        @if($messages->isNotEmpty())
            <div style="display:grid;gap:16px;">
                @foreach($messages as $message)
                    <div style="border:1px solid #e5e7eb;border-radius:16px;background:#fff;overflow:hidden;">
                        <!-- Ticket Header -->
                        <div style="padding:16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @if(!$message->seen_by_user && $message->replies && count($message->replies) > 0 && (collect($message->replies)->last()['sender'] ?? '') === 'admin')
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#dc2626;animation:pulse 1.5s infinite;flex-shrink:0;"></span>
                                    @endif
                                    <div>
                                        <strong>{{ $message->subject }}</strong>
                                        <p class="text-muted" style="margin:2px 0 0;font-size:0.95rem;">{{ $message->created_at->format('M d, Y H:i') }} • 
                                            @if($message->status === 'closed')
                                                <span style="color:#6b7280;">Closed</span>
                                            @elseif($message->status === 'answered')
                                                <span style="color:#059669;">Answered</span>
                                            @else
                                                <span style="color:#2563eb;">Open</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if(!$message->seen_by_user && $message->replies && count($message->replies) > 0 && (collect($message->replies)->last()['sender'] ?? '') === 'admin')
                                        <span class="badge badge-red" style="font-size:0.85rem;">New Reply</span>
                                    @endif
                                </div>
                                @if($message->status !== 'closed')
                                    <form method="POST" action="{{ route('customer-service.close', $message) }}" onsubmit="return confirm('Are you sure you want to close this ticket?');">
                                        @csrf
                                        <button class="btn" style="background:#ef4444;padding:6px 12px;font-size:0.95rem;">Close Ticket</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div style="padding:16px;display:grid;gap:12px;">
                            <!-- Initial User Message -->
                            <div style="display:flex;justify-content:flex-end;">
                                <div style="max-width:80%;background:#2563eb;color:#fff;padding:12px 16px;border-radius:16px 16px 4px 16px;font-size:1.05rem;">
                                    <p style="margin:0;">{{ $message->message }}</p>
                                    <p style="margin:4px 0 0;font-size:0.85rem;opacity:0.7;">{{ $message->created_at->format('M d, H:i') }}</p>
                                </div>
                            </div>

                            <!-- Replies -->
                            @if($message->replies)
                                @foreach($message->replies as $reply)
                                    @if($reply['sender'] === 'admin')
                                        <div style="display:flex;justify-content:flex-start;">
                                            <div style="max-width:80%;background:#f3f4f6;color:#111;padding:12px 16px;border-radius:16px 16px 16px 4px;font-size:1.05rem;">
                                                <p style="margin:0;font-weight:600;font-size:0.9rem;color:#6b7280;">Admin</p>
                                                <p style="margin:4px 0 0;">{{ $reply['message'] }}</p>
                                                <p style="margin:4px 0 0;font-size:0.85rem;color:#9ca3af;">{{ \Carbon\Carbon::parse($reply['created_at'])->format('M d, H:i') }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <div style="display:flex;justify-content:flex-end;">
                                            <div style="max-width:80%;background:#2563eb;color:#fff;padding:12px 16px;border-radius:16px 16px 4px 16px;font-size:1.05rem;">
                                                <p style="margin:0;">{{ $reply['message'] }}</p>
                                                <p style="margin:4px 0 0;font-size:0.85rem;opacity:0.7;">{{ \Carbon\Carbon::parse($reply['created_at'])->format('M d, H:i') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <!-- Reply Form (if ticket is open) -->
                            @if($message->status !== 'closed')
                                <form method="POST" action="{{ route('customer-service.reply', $message) }}" style="margin-top:8px;">
                                    @csrf
                                    <div style="display:flex;gap:8px;">
                                        <input class="input" name="message" placeholder="Type your reply..." required style="flex:1;">
                                        <button class="btn" type="submit" style="white-space:nowrap;">Send</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="text-align:center;padding:40px;color:#6b7280;">No tickets yet. Click "Open New Ticket" to start a conversation.</p>
        @endif
    </div>
@endsection