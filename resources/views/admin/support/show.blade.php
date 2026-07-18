 @extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.support.index')])
            <h1 class="mb-0">Support Ticket</h1>
        </div>
    </div>
    <div class="card" style="max-width:900px;margin:0 auto;">
        <p class="text-muted">From {{ $message->user->name }} ({{ $message->user->email }})</p>

        <div style="border:1px solid #e9ecef;border-radius:16px;overflow:hidden;margin-top:16px;">
            <!-- Chat Header -->
            <div style="padding:16px 20px;background:#f8f9fa;border-bottom:1px solid #e9ecef;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <strong style="font-size:1.15rem;">{{ $message->subject }}</strong>
                    <span>
                        Status: 
                        @if($message->status === 'closed')
                            <span class="badge badge-gray">Closed</span>
                        @elseif($message->status === 'answered')
                            <span class="badge badge-green">Answered</span>
                        @else
                            <span class="badge badge-blue">Open</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Chat Messages -->
            <div style="padding:20px;display:grid;gap:12px;max-height:500px;overflow-y:auto;">
                <!-- Initial User Message -->
                <div style="display:flex;justify-content:flex-end;">
                    <div style="max-width:80%;background:#1a1a2e;color:#fff;padding:12px 16px;border-radius:16px 16px 4px 16px;font-size:1.05rem;">
                        <p style="margin:0;font-weight:500;font-size:0.9rem;opacity:0.65;">{{ $message->user->name }}</p>
                        <p style="margin:4px 0 0;">{{ $message->message }}</p>
                        <p style="margin:4px 0 0;font-size:0.85rem;opacity:0.65;">{{ $message->created_at->format('M d, H:i') }}</p>
                    </div>
                </div>

                <!-- Replies -->
                @if($message->replies)
                    @foreach($message->replies as $reply)
                        @if($reply['sender'] === 'admin')
                            <div style="display:flex;justify-content:flex-start;">
                                <div style="max-width:80%;background:#f1f3f5;color:#1a1a2e;padding:12px 16px;border-radius:16px 16px 16px 4px;font-size:1.05rem;">
                                    <p style="margin:0;font-weight:500;font-size:0.9rem;color:#6c757d;">Admin</p>
                                    <p style="margin:4px 0 0;">{{ $reply['message'] }}</p>
                                    <p style="margin:4px 0 0;font-size:0.85rem;color:#adb5bd;">{{ \Carbon\Carbon::parse($reply['created_at'])->format('M d, H:i') }}</p>
                                </div>
                            </div>
                        @else
                            <div style="display:flex;justify-content:flex-end;">
                                <div style="max-width:80%;background:#1a1a2e;color:#fff;padding:12px 16px;border-radius:16px 16px 4px 16px;font-size:1.05rem;">
                                    <p style="margin:0;font-weight:500;font-size:0.9rem;opacity:0.65;">{{ $message->user->name }}</p>
                                    <p style="margin:4px 0 0;">{{ $reply['message'] }}</p>
                                    <p style="margin:4px 0 0;font-size:0.85rem;opacity:0.65;">{{ \Carbon\Carbon::parse($reply['created_at'])->format('M d, H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Reply Form -->
            <form method="POST" action="{{ route('admin.support.update', $message) }}" style="padding:20px;border-top:1px solid #e9ecef;background:#f8f9fa;">
                @csrf
                @method('PATCH')
                <label style="font-weight:500;font-size:1rem;margin-bottom:6px;display:block;">Reply as Admin</label>
                <textarea class="input" name="message" rows="3" required placeholder="Type your reply..."></textarea>
                <div style="display:flex;gap:12px;margin-top:12px;align-items:center;flex-wrap:wrap;">
                    <button class="btn" type="submit">Send Reply</button>
                    <select class="input" name="status" style="width:auto;padding:8px 14px;">
                        <option value="answered"{{ $message->status === 'answered' ? ' selected' : '' }}>Answered</option>
                        <option value="open"{{ $message->status === 'open' ? ' selected' : '' }}>Open</option>
                        <option value="closed"{{ $message->status === 'closed' ? ' selected' : '' }}>Closed</option>
                    </select>
                </div>
            </form>
        </div>

        <a href="{{ route('admin.support.index') }}" class="btn btn-secondary" style="margin-top:16px;">Back to Tickets</a>
    </div>
@endsection