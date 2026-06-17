@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Customer Messages</h1>
        <p class="text-muted">Review and reply to buyer complaints and requests.</p>

        @if($messages->isEmpty())
            <p>No messages yet.</p>
        @else
            <div style="display:grid;gap:16px;">
                @foreach($messages as $message)
                    <div style="padding:16px;border:1px solid #e5e7eb;border-radius:16px;">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                            <div>
                                <strong>{{ $message->subject }}</strong>
                                <p class="text-muted">From {{ $message->user->name }} • {{ ucfirst($message->status) }}</p>
                            </div>
                            <a class="btn" href="{{ route('admin.support.show', $message) }}">Reply</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:20px;">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
