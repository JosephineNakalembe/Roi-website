@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Login</h1>
        </div>
    </div>
    <div class="card" style="max-width:520px;margin:0 auto;">
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <label>Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <label>Password</label>
            <div style="position:relative;">
                <input class="input" type="password" name="password" id="login-password" required style="padding-right:64px;">
                <button type="button" onclick="togglePassword('login-password', this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;font-size:0.85rem;font-weight:600;">Show</button>
            </div>
            <button class="btn" type="submit" style="margin-top:16px;">Sign In</button>
            <p class="text-muted" style="margin-top:12px;"><a href="{{ route('password.request') }}" style="color:#111;font-weight:700;">Forgot Password?</a></p>
            <p class="text-muted" style="margin-top:12px;">Don't have an account? <a href="{{ route('register') }}" style="color:#111;font-weight:700;">Sign up</a></p>
        </form>
    </div>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
        }
    </script>
@endsection
