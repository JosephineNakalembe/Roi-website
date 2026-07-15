@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Forgot Password</h1>
        </div>
    </div>
    <div class="card" style="max-width:520px;margin:0 auto;">
        <p class="text-muted" style="margin-bottom:16px;">Enter your email address and we'll send you a verification code to reset your password.</p>
        <form method="POST" action="{{ route('password.send-code') }}">
            @csrf
            <label>Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <p class="text-danger">{{ $message }}</p>
            @enderror
            <button class="btn" type="submit" style="margin-top:16px;">Send Verification Code</button>
        </form>
        <p style="margin-top:16px;"><a href="{{ route('login') }}" style="color:#111;font-weight:700;">Back to Login</a></p>
    </div>
@endsection
