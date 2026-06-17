@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Payment Methods</h1>
        <div style="display:grid;gap:16px;">
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
        <form method="POST" action="{{ route('profile.payment.save') }}" style="margin-top:24px;display:grid;gap:12px;">
            @csrf
            <h2>Add Payment Method</h2>
            <label>Cardholder</label>
            <input class="input" name="cardholder" required>
            <label>Card number</label>
            <input class="input" name="card_number" required>
            <label>Expiry month</label>
            <input class="input" name="expiry_month" placeholder="MM" required>
            <label>Expiry year</label>
            <input class="input" name="expiry_year" placeholder="YYYY" required>
            <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_default" value="1"> Set as default</label>
            <button class="btn">Save Payment Method</button>
        </form>
    </div>
@endsection
