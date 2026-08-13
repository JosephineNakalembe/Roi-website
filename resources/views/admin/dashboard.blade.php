@extends('layouts.app')

@section('content')
        <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.dashboard')])
            <h1 class="mb-0">
                Admin Dashboard
                @if(($newOrdersCount ?? 0) > 0)
                    <span class="nav-badge"><sup class="badge-red" title="New orders received">{{ $newOrdersCount }}</sup></span>
                @endif
            </h1>
        </div>
    </div>
    <div class="card">
        <p class="text-muted" style="margin-bottom:18px;">Overview of your store</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
            <div class="stat-card"><div class="stat-value">{{ $orders }}</div><div class="stat-label">Total Orders</div></div>
            <div class="stat-card"><div class="stat-value">{{ $pending }}</div><div class="stat-label">Pending</div></div>
            <div class="stat-card"><div class="stat-value">{{ $shipped }}</div><div class="stat-label">Shipped</div></div>
            <div class="stat-card"><div class="stat-value">{{ $delivered }}</div><div class="stat-label">Delivered</div></div>
            <div class="stat-card"><div class="stat-value">{{ $products }}</div><div class="stat-label">Products</div></div>
            <div class="stat-card" style="{{ $outOfStock > 0 ? 'border:2px solid #dc2626;' : '' }}"><div class="stat-value" style="{{ $outOfStock > 0 ? 'color:#dc2626;' : '' }}">{{ $outOfStock }}</div><div class="stat-label">Out of Stock</div></div>
            <div class="stat-card"><div class="stat-value">{{ $users }}</div><div class="stat-label">Users</div></div>
            <div class="stat-card"><div class="stat-value">{{ $messages }}</div><div class="stat-label">Messages</div></div>
            <div class="stat-card"><div class="stat-value">{{ $openMessages }}</div><div class="stat-label">Open Messages</div></div>
        </div>
        <div style="margin-top:24px;display:flex;flex-wrap:wrap;gap:10px;">
            <a class="btn" href="{{ route('admin.products.index') }}">Manage Products</a>
            <a class="btn" href="{{ route('admin.products.out-of-stock') }}" style="background:#dc2626;color:#fff;">Out of Stock</a>
            <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Manage Categories</a>
            <a class="btn" href="{{ route('admin.orders.index') }}">Manage Orders</a>
            <a class="btn btn-secondary" href="{{ route('admin.expenditures.index') }}">Expenditures</a>
            <a class="btn" href="{{ route('admin.users.index') }}">Manage Users</a>
            <a class="btn btn-secondary" href="{{ route('admin.support.index') }}">View Messages</a>
            <a class="btn" href="{{ route('admin.reports.index') }}">View Reports</a>
            <a class="btn" href="{{ route('admin.returns.index') }}" style="background:#f97316;">View Returns
                @if(($pendingReturnsCount ?? 0) > 0)
                    <span class="nav-badge"><sup class="badge-red" title="Unprocessed returns">{{ $pendingReturnsCount }}</sup></span>
                @endif
            </a>
            <a class="btn btn-secondary" href="{{ route('admin.delivery-areas.index') }}">Delivery Areas</a>
        </div>

        @if($systemErrors->isNotEmpty())
            <div style="margin-top:28px;border-top:1px solid #e8e4df;padding-top:20px;">
                <h2 style="margin:0 0 12px;font-size:1.05rem;">Recent System Errors</h2>
                <div style="display:grid;gap:8px;">
                    @foreach($systemErrors as $error)
                        <div style="background:#fff;border:1px solid #e8e4df;border-left:3px solid #dc2626;border-radius:8px;padding:10px 12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
                                <strong style="font-size:0.9rem;">{{ $error->exception }}</strong>
                                <span style="font-size:0.8rem;color:#9ca3af;">{{ $error->created_at->diffForHumans() }}</span>
                            </div>
                            @if($error->message)
                                <p style="margin:4px 0 0;font-size:0.85rem;color:#4b5563;">{{ $error->message }}</p>
                            @endif
                            @if($error->url)
                                <p style="margin:4px 0 0;font-size:0.8rem;color:#6b7280;word-break:break-all;">{{ $error->url }}</p>
                            @endif
                            @if($error->file)
                                <p style="margin:4px 0 0;font-size:0.78rem;color:#9ca3af;">{{ $error->file }}@if($error->line):{{ $error->line }}@endif</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
