@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Addresses</h1>
        </div>
    </div>
    <div class="card">
        <div class="form-grid" style="gap:16px;">
            @forelse($addresses as $address)
                <div style="padding:16px;border:1px solid #e5e7eb;border-radius:14px;">
                    <strong>{{ $address->label }}</strong>
                    <p>{{ $address->line1 }}{{ $address->line2 ? ', ' . $address->line2 : '' }}, {{ $address->city }}, {{ $address->state }}, {{ $address->postal_code }}</p>
                    <p class="text-muted">{{ $address->country }} • {{ $address->phone }}</p>
                    @if($address->is_default)<span style="color:#16a34a;">Default</span>@endif
                </div>
            @empty
                <p>No saved addresses yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('profile.address.save') }}" class="form-grid" style="margin-top:24px;max-width:700px;">
            @csrf
            <h2>Add Address</h2>
            <div class="form-group">
                <label class="form-label">Label</label>
                <input class="input input-full" name="label" value="Home">
            </div>
            <div class="form-group">
                <label class="form-label">Address line 1</label>
                <input class="input input-full" name="line1">
            </div>
            <div class="form-group">
                <label class="form-label">Address line 2</label>
                <input class="input input-full" name="line2">
            </div>
            <div class="form-group">
                <label class="form-label">District</label>
                <select class="select" name="city" required>
                    <option value="">Select district</option>
                    @foreach(['Kampala','Wakiso','Mukono','Jinja','Entebbe','Mbarara','Mbale','Gulu','Lira','Arua','Masaka','Fort Portal','Hoima','Soroti','Iganga'] as $district)
                        <option value="{{ $district }}">{{ $district }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Local area / State</label>
                <input class="input input-full" name="state" placeholder="Sub-county or neighbourhood">
            </div>
            <div class="form-group">
                <label class="form-label">Postal code</label>
                <input class="input input-full" name="postal_code" placeholder="00000">
            </div>
            <div class="form-group">
                <label class="form-label">Country</label>
                <input class="input input-full" name="country" value="Uganda">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input class="input input-full" name="phone" placeholder="+256...">
            </div>
            <div class="form-group">
                <label class="flex-between flex-gap-small">
                    <input type="checkbox" name="is_default" value="1" class="checkbox">
                    Set as default
                </label>
            </div>
            <button class="btn">Save Address</button>
        </form>
    </div>
@endsection
