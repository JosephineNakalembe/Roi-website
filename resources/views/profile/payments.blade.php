@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            <h1 class="mb-0">Payment Methods</h1>
        </div>
    </div>
    <div class="card">
        <div class="form-grid" style="gap:16px;">
            @forelse($paymentMethods as $method)
                <div style="padding:16px;border:1px solid #e5e7eb;border-radius:14px;">
                    <strong>•••• {{ $method->last4 }}</strong>
                    <p class="text-muted">Expires {{ $method->expiry_month }}/{{ $method->expiry_year }}</p>
                    @if($method->is_default)<span style="color:#16a34a;">Default</span>@endif
                </div>
            @empty
                <p>No payment methods saved yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('profile.payment.save') }}" class="form-grid" style="margin-top:24px;">
            @csrf
            <h2>Add Payment Method</h2>
            <div class="form-group">
                <label class="form-label">Cardholder</label>
                <input class="input input-full" name="cardholder" required>
            </div>
            <div class="form-group">
                <label class="form-label">Card number</label>
                <input class="input input-full" name="card_number" required>
            </div>
            <div class="form-group">
                <label class="form-label">Expiry month</label>
                <input class="input input-full" name="expiry_month" placeholder="MM" required>
            </div>
            <div class="form-group">
                <label class="form-label">Expiry year</label>
                <input class="input input-full" name="expiry_year" placeholder="YYYY" required>
            </div>
            <div class="form-group">
                <label class="flex-between flex-gap-small">
                    <input type="checkbox" name="is_default" value="1" class="checkbox">
                    Set as default
                </label>
            </div>
            <button class="btn">Save Payment Method</button>
        </form>
    </div>
@endsection
