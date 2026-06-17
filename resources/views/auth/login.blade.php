@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:520px;margin:0 auto;">
        <h1>Login</h1>
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <label>Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <label>Password</label>
            <input class="input" type="password" name="password" required>
            <label style="display:flex;align-items:center;gap:8px;margin-top:12px;"><input type="checkbox" name="remember"> Remember me</label>
            <button class="btn" type="submit" style="margin-top:16px;">Sign In</button>
            <p class="text-muted" style="margin-top:12px;">Don't have an account? <a href="{{ route('register') }}" style="color:#111;font-weight:700;">Sign up</a></p>
        </form>
    </div>
@endsection
