@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.dashboard')])
            <h1 class="mb-0">Admin Dashboard</h1>
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
            <a class="btn" href="{{ route('admin.returns.index') }}" style="background:#f97316;">View Returns</a>
        </div>
    </div>
@endsection
