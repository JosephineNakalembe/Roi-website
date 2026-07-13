@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.dashboard')])
            <h1 class="mb-0">Orders Management</h1>
        </div>
    </div>
    <div class="card">
        <p class="text-muted" style="margin-bottom:18px;">View and manage all orders</p>

        <!-- Category Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
            <a href="{{ route('admin.orders.index', ['category' => 'new']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'new' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $newOrdersCount }}</div>
                    <div class="stat-label">New Orders</div>
                </div>
            </a>
            <a href="{{ route('admin.orders.index', ['category' => 'pending']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'pending' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $pendingOrdersCount }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </a>
            <a href="{{ route('admin.orders.index', ['category' => 'shipped']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'shipped' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $shippedOrdersCount }}</div>
                    <div class="stat-label">Shipped</div>
                </div>
            </a>
            <a href="{{ route('admin.orders.index', ['category' => 'delivered']) }}" style="text-decoration:none;color:inherit;">
                <div class="stat-card" style="{{ $category === 'delivered' ? 'background:#f1f3f5;' : '' }}">
                    <div class="stat-value">{{ $deliveredOrdersCount }}</div>
                    <div class="stat-label">Delivered</div>
                </div>
            </a>
        </div>

        <!-- Filter Buttons -->
        <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
            <a href="{{ route('admin.orders.index') }}" class="nav-link" style="{{ !$category ? 'background:#1a1a2e;color:#fff;' : '' }}">All</a>
            <a href="{{ route('admin.orders.index', ['category' => 'new']) }}" class="nav-link" style="{{ $category === 'new' ? 'background:#1a1a2e;color:#fff;' : '' }}">New</a>
            <a href="{{ route('admin.orders.index', ['category' => 'pending']) }}" class="nav-link" style="{{ $category === 'pending' ? 'background:#1a1a2e;color:#fff;' : '' }}">Pending</a>
            <a href="{{ route('admin.orders.index', ['category' => 'shipped']) }}" class="nav-link" style="{{ $category === 'shipped' ? 'background:#1a1a2e;color:#fff;' : '' }}">Shipped</a>
            <a href="{{ route('admin.orders.index', ['category' => 'delivered']) }}" class="nav-link" style="{{ $category === 'delivered' ? 'background:#1a1a2e;color:#fff;' : '' }}">Delivered</a>
        </div>

        @if($orders->isEmpty())
            <p style="text-align:center;padding:40px;color:#6b7280;">
                @if($category === 'new') No new orders.
                @elseif($category === 'pending') No pending orders.
                @elseif($category === 'shipped') No shipped orders.
                @elseif($category === 'delivered') No delivered orders.
                @else No orders yet.
                @endif
            </p>
        @else
            <div style="display:grid;gap:14px;">
                @foreach($orders as $order)
                    @php
                        $lastUpdate = $order->updates->first();
                        $isNew = $order->status === 'pending' && !$order->updates->whereIn('status', ['shipped', 'delivered'])->isNotEmpty();
                    @endphp
                    <div style="border:1px solid #e9ecef;padding:16px;border-radius:14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;background:#fff;">
                        <div>
                            <strong>{{ $order->order_number }}</strong>
                            <p class="text-muted" style="margin:2px 0 0;">{{ $order->user->name }} — UGX{{ number_format($order->total, 2) }}</p>
                            <p style="margin:4px 0 0;font-size:0.8rem;color:#9ca3af;">
                                {{ $order->placed_at->format('M d, Y H:i') }} • 
                                @if($isNew)
                                    <span class="badge badge-blue">New</span>
                                @elseif($order->status === 'pending')
                                    <span class="badge badge-amber">Pending</span>
                                @elseif($order->status === 'shipped')
                                    <span class="badge badge-blue">Shipped</span>
                                @elseif($order->status === 'delivered')
                                    <span class="badge badge-green">Delivered</span>
                                @endif
                            </p>
                            @if($lastUpdate && $lastUpdate->note)
                                <p style="margin:4px 0 0;font-size:0.8rem;color:#6b7280;">{{ $lastUpdate->note }}</p>
                            @endif
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                            <a class="btn btn-secondary" href="{{ route('admin.orders.show', $order) }}" style="padding:8px 14px;font-size:0.85rem;">Details</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:20px;">{{ $orders->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection