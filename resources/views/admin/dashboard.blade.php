@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Admin Dashboard</h1>
        <p class="text-muted" style="margin-bottom:18px;">Overview of your store</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
            <div class="stat-card"><div class="stat-value">{{ $orders }}</div><div class="stat-label">Total Orders</div></div>
            <div class="stat-card"><div class="stat-value">{{ $pending }}</div><div class="stat-label">Pending</div></div>
            <div class="stat-card"><div class="stat-value">{{ $shipped }}</div><div class="stat-label">Shipped</div></div>
            <div class="stat-card"><div class="stat-value">{{ $delivered }}</div><div class="stat-label">Delivered</div></div>
            <div class="stat-card"><div class="stat-value">{{ $products }}</div><div class="stat-label">Products</div></div>
            <div class="stat-card"><div class="stat-value">{{ $users }}</div><div class="stat-label">Users</div></div>
            <div class="stat-card"><div class="stat-value">{{ $messages }}</div><div class="stat-label">Messages</div></div>
            <div class="stat-card"><div class="stat-value">{{ $openMessages }}</div><div class="stat-label">Open Messages</div></div>
        </div>
        <div style="margin-top:24px;display:flex;flex-wrap:wrap;gap:10px;">
            <a class="btn" href="{{ route('admin.products.index') }}">Manage Products</a>
            <a class="btn" href="{{ route('admin.orders.index') }}">Manage Orders</a>
            <a class="btn" href="{{ route('admin.users.index') }}">Manage Users</a>
            <a class="btn btn-secondary" href="{{ route('admin.support.index') }}">View Messages</a>
            <a class="btn" href="{{ route('admin.reports.index') }}">View Reports</a>
            <a class="btn" href="{{ route('admin.returns.index') }}" style="background:#f97316;">View Returns</a>
        </div>
    </div>
@endsection
