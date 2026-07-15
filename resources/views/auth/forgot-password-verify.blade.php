@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Enter Verification Code</h1>
        </div>
    </div>
    <div class="card" style="max-width:520px;margin:0 auto;">
        <p class="text-muted" style="margin-bottom:16px;">Enter the 6-digit verification code sent to your email.</p>
        <form method="POST" action="{{ route('password.verify-code') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <label>Verification Code</label>
            <input class="input" type="text" name="code" pattern="[0-9]{6}" maxlength="6" placeholder="123456" required autofocus style="letter-spacing: 4px; font-size: 24px; text-align: center;">
            @error('code')
                <p class="text-danger">{{ $message }}</p>
            @enderror
            <button class="btn" type="submit" style="margin-top:16px;">Verify Code</button>
        </form>
        <p style="margin-top:16px;"><a href="{{ route('password.request', ['email' => $email]) }}" style="color:#111;font-weight:700;">Resend Code</a></p>
    </div>
@endsection
