@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            <h1 class="mb-0">My Returns</h1>
        </div>
    </div>
    <div class="card">

        @if($returns->isEmpty())
            <div style="text-align:center;padding:40px;color:#9ca3af;">
                <p style="font-size:1.1rem;">You haven't made any return requests yet.</p>
                <a href="{{ route('orders.index') }}" class="btn" style="margin-top:12px;">View Your Orders</a>
            </div>
        @else
            <div style="display:grid;gap:14px;margin-top:16px;">
                @foreach($returns as $return)
                    <a href="{{ route('returns.track', $return) }}" style="display:block;text-decoration:none;color:inherit;padding:16px;border:1px solid #e5e7eb;border-radius:14px;background:#fff;transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)';" onmouseout="this.style.boxShadow='';">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                            <div>
                                <strong style="font-size:1.05rem;">{{ $return->return_number }}</strong>
                                <p style="margin:2px 0 0;font-size:0.85rem;color:#6b7280;">
                                    Order {{ $return->order->order_number }} • {{ $return->created_at->format('M d, Y') }}
                                </p>
                                <p style="margin:2px 0 0;font-size:0.85rem;color:#6b7280;">
                                    Reason: {{ $return->reason }}
                                </p>
                            </div>
                            <div style="text-align:right;">
                                @if($return->status === 'pending')
                                    <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.8rem;font-weight:600;background:#fef3c7;color:#92400e;">Pending Review</span>
                                @elseif($return->status === 'approved')
                                    <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.8rem;font-weight:600;background:#dbeafe;color:#1e40af;">Approved</span>
                                @elseif($return->status === 'rejected')
                                    <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.8rem;font-weight:600;background:#fee2e2;color:#991b1b;">Rejected</span>
                                @elseif($return->status === 'refunded')
                                    <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.8rem;font-weight:600;background:#d1fae5;color:#065f46;">Refunded</span>
                                @endif
                                <p style="margin:4px 0 0;font-size:0.8rem;color:#2563eb;">Click to track →</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div style="margin-top:20px;">{{ $returns->links() }}</div>
        @endif
    </div>
@endsection