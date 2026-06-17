@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>My Account</h1>
        <p>Manage your personal info, addresses, and payment methods.</p>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <label>Name</label>
            <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            <label>Email</label>
            <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            <label>Phone</label>
            <input class="input" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            <label>Default address</label>
            <textarea class="input" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
            <button class="btn" style="margin-top:16px;">Save Profile</button>
        </form>
        <div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap;">
            <a class="btn btn-secondary" href="{{ route('profile.addresses') }}">Manage Addresses</a>
            <a class="btn btn-secondary" href="{{ route('profile.payments') }}">Manage Payment Methods</a>
        </div>
    </div>

    <div class="card" style="border:1px solid #fce4ec;">
        <h2>Delete Account</h2>
        <p class="text-muted" style="margin-bottom:12px;">Permanently delete your account and all personal data.</p>
        <form method="POST" action="{{ route('profile.delete-account') }}" onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account, addresses, payment methods, and all personal data. Your order records will be kept on our end for reporting purposes. This action cannot be undone.');">
            @csrf
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9rem;">
                <input type="checkbox" name="confirm" value="1" required>
                I understand that this action is permanent and cannot be undone.
            </label>
            <button class="btn" type="submit" style="margin-top:12px;background:#dc2626;">Permanently Delete My Account</button>
        </form>
    </div>
@endsection
