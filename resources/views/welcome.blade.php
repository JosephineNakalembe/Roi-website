@extends('layouts.app')

@section('content')
    <section class="card card-centered-text">
        <h1>Welcome to ROI Store</h1>
        <p class="text-muted">Browse products, add items to your cart, checkout securely, and track orders from a single login.</p>
        <div class="flex-center flex-gap-medium flex-wrap" style="margin-top:20px;">
            <a class="btn" href="{{ route('shop.index') }}">Shop Now</a>
            @guest
                <a class="btn btn-secondary" href="{{ route('register') }}">Create Account</a>
            @endguest
        </div>
    </section>

    <section class="card" style="margin-top:24px;">
        <h2>Unified buyer and admin flow</h2>
        <p class="text-muted">Users sign in with email and password only. Admin access is granted by role after login, without a separate admin sign-in page.</p>
    </section>
@endsection
