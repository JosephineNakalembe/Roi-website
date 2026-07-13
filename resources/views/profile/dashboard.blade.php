@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            <h1 class="mb-0">My Account</h1>
        </div>
    </div>
    <div class="card">
        <p>Manage your personal info, addresses, and payment methods.</p>
        <form method="POST" action="{{ route('profile.update') }}" class="form-grid">
            @csrf
            <div class="form-group">
                <label class="form-label">Name</label>
                <input class="input input-full" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input class="input input-full" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input class="input input-full" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Default address</label>
                <textarea class="textarea" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
            </div>
            <button class="btn" style="margin-top:16px;">Save Profile</button>
        </form>
        <div class="flex-row flex-gap-small flex-wrap" style="margin-top:24px;">
            <a class="btn btn-secondary" href="{{ route('profile.addresses') }}">Manage Addresses</a>
            <a class="btn btn-secondary" href="{{ route('profile.payments') }}">Manage Payment Methods</a>
        </div>
    </div>

    <div class="card card-pink">
        <h2>Delete Account</h2>
        <p class="text-muted" style="margin-bottom:12px;">Permanently delete your account and all personal data.</p>
        <form method="POST" action="{{ route('profile.delete-account') }}" onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account, addresses, payment methods, and all personal data. Your order records will be kept on our end for reporting purposes. This action cannot be undone.');">
            @csrf
            <label class="flex-between flex-gap-small" style="cursor:pointer;font-size:0.9rem;">
                <input type="checkbox" name="confirm" value="1" required class="checkbox">
                I understand that this action is permanent and cannot be undone.
            </label>
            <button class="btn btn-red" type="submit" style="margin-top:12px;">Permanently Delete My Account</button>
        </form>
    </div>
@endsection
