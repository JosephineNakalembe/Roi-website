@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Addresses</h1>
        <div style="display:grid;gap:16px;">
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
        <form method="POST" action="{{ route('profile.address.save') }}" style="margin-top:24px;display:grid;gap:12px;max-width:700px;">
            @csrf
            <h2>Add Address</h2>
            <label>Label</label>
            <input class="input" name="label" value="Home">
            <label>Address line 1</label>
            <input class="input" name="line1">
            <label>Address line 2</label>
            <input class="input" name="line2">
            <label>District</label>
            <select class="input" name="city" required>
                <option value="">Select district</option>
                @foreach(['Kampala','Wakiso','Mukono','Jinja','Entebbe','Mbarara','Mbale','Gulu','Lira','Arua','Masaka','Fort Portal','Hoima','Soroti','Iganga'] as $district)
                    <option value="{{ $district }}">{{ $district }}</option>
                @endforeach
            </select>
            <label>Local area / State</label>
            <input class="input" name="state" placeholder="Sub-county or neighbourhood">
            <label>Postal code</label>
            <input class="input" name="postal_code" placeholder="00000">
            <label>Country</label>
            <input class="input" name="country" value="Uganda">
            <label>Phone</label>
            <input class="input" name="phone" placeholder="+256...">
            <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_default" value="1"> Set as default</label>
            <button class="btn">Save Address</button>
        </form>
    </div>
@endsection
