@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Notifications</h1>
        </div>
    </div>
    <div class="card">
        @if($notifications->isEmpty())
            <p class="text-muted" style="text-align:center;padding:24px;">You have no notifications.</p>
        @else
            <div style="display:grid;gap:10px;">
                @foreach($notifications as $notification)
                    <a href="{{ route('notifications.read', $notification) }}" style="display:block;padding:12px 14px;border-radius:12px;text-decoration:none;color:inherit;border:1px solid #e8e4df;{{ $notification->read_at ? 'background:#fff;' : 'background:#f5f3ef;' }}">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                            <strong style="font-size:0.95rem;">{{ $notification->title }}</strong>
                            <span class="text-muted" style="font-size:0.8rem;white-space:nowrap;">{{ $notification->created_at->format('M d, H:i') }}</span>
                        </div>
                        @if($notification->body)
                            <p style="margin:4px 0 0;color:#6c757d;font-size:0.9rem;">{{ $notification->body }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
            <div style="margin-top:16px;">{{ $notifications->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
