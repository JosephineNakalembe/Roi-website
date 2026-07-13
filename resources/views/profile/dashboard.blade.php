@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">My Account</h1>
        </div>
    </div>
    <div class="card">
        <p>Manage your personal info, addresses, and payment methods.</p>
        
        <!-- Profile Section -->
        <div style="margin-bottom:24px;padding:16px;background:#f9fafb;border-radius:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="margin:0;">Profile</h2>
                <button type="button" onclick="toggleProfileEdit()" class="btn" style="padding:6px 12px;font-size:0.75rem;">Edit</button>
            </div>
            
            <!-- Display Mode -->
            <div id="profileDisplay" style="display:grid;gap:12px;">
                <div>
                    <label class="form-label" style="font-size:0.75rem;color:#6b7280;">Name</label>
                    <p style="margin:4px 0 0;font-weight:500;">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="form-label" style="font-size:0.75rem;color:#6b7280;">Email</label>
                    <p style="margin:4px 0 0;font-weight:500;">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="form-label" style="font-size:0.75rem;color:#6b7280;">Phone</label>
                    <p style="margin:4px 0 0;font-weight:500;">{{ $user->phone ?? 'Not provided' }}</p>
                </div>
            </div>
            
            <!-- Edit Mode -->
            <form id="profileEditForm" method="POST" action="{{ route('profile.update') }}" style="display:none;grid-gap:12px;">
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
                <div style="display:flex;gap:8px;margin-top:8px;">
                    <button type="submit" class="btn">Save</button>
                    <button type="button" onclick="toggleProfileEdit()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
        
        <!-- Default Address Section (unchanged) -->
        <div class="form-group">
            <label class="form-label">Default address</label>
            <textarea class="textarea" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
        </div>
        <button class="btn" style="margin-top:16px;">Save Address</button>
        
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

    <script>
        function toggleProfileEdit() {
            const display = document.getElementById('profileDisplay');
            const form = document.getElementById('profileEditForm');
            
            if (display.style.display === 'none') {
                display.style.display = 'grid';
                form.style.display = 'none';
            } else {
                display.style.display = 'none';
                form.style.display = 'grid';
            }
        }
    </script>
@endsection
