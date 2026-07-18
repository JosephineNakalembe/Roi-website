@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.dashboard')])
            <h1 class="mb-0">Return Requests</h1>
        </div>
    </div>
    <div class="card">
        <p class="text-muted" style="margin-bottom:18px;">Manage product returns from customers</p>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
            <a href="{{ route('admin.returns.index', ['category' => 'pending']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'pending' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $pendingCount }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </a>
            <a href="{{ route('admin.returns.index', ['category' => 'approved']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'approved' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $approvedCount }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </a>
            <a href="{{ route('admin.returns.index', ['category' => 'rejected']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'rejected' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $rejectedCount }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </a>
            <a href="{{ route('admin.returns.index', ['category' => 'refunded']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'refunded' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $refundedCount }}</div>
                    <div class="stat-label">Refunded</div>
                </div>
            </a>
        </div>

        <!-- Filters -->
        <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
            <a href="{{ route('admin.returns.index') }}" class="nav-link" style="{{ !$category ? 'background:#1a1a2e;color:#fff;' : '' }}">All</a>
            <a href="{{ route('admin.returns.index', ['category' => 'pending']) }}" class="nav-link" style="{{ $category === 'pending' ? 'background:#1a1a2e;color:#fff;' : '' }}">Pending</a>
            <a href="{{ route('admin.returns.index', ['category' => 'approved']) }}" class="nav-link" style="{{ $category === 'approved' ? 'background:#1a1a2e;color:#fff;' : '' }}">Approved</a>
            <a href="{{ route('admin.returns.index', ['category' => 'rejected']) }}" class="nav-link" style="{{ $category === 'rejected' ? 'background:#1a1a2e;color:#fff;' : '' }}">Rejected</a>
            <a href="{{ route('admin.returns.index', ['category' => 'refunded']) }}" class="nav-link" style="{{ $category === 'refunded' ? 'background:#1a1a2e;color:#fff;' : '' }}">Refunded</a>
        </div>

        @if($returns->isEmpty())
            <p style="text-align:center;padding:40px;color:#6b7280;">No return requests found.</p>
        @else
            <div style="display:grid;gap:14px;">
                @foreach($returns as $return)
                    <div style="border:1px solid #e9ecef;padding:16px;border-radius:14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;background:#fff;">
                        <div>
                            <strong>{{ $return->return_number }}</strong>
                            <p class="text-muted" style="margin:2px 0 0;">
                                Order {{ $return->order->order_number }} — {{ $return->user->name }}
                            </p>
                            <p style="margin:4px 0 0;font-size:0.9rem;color:#9ca3af;">
                                {{ $return->created_at->format('M d, Y H:i') }} • 
                                @if($return->status === 'pending')
                                    <span class="badge badge-amber">Pending</span>
                                @elseif($return->status === 'approved')
                                    <span class="badge badge-blue">Approved</span>
                                @elseif($return->status === 'rejected')
                                    <span class="badge badge-red">Rejected</span>
                                @elseif($return->status === 'refunded')
                                    <span class="badge badge-green">Refunded</span>
                                @endif
                                • Reason: {{ $return->reason }}
                            </p>
                        </div>
                        <a class="btn btn-secondary" href="{{ route('admin.returns.show', $return) }}" style="padding:8px 14px;font-size:0.95rem;">View Details</a>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:20px;">{{ $returns->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection