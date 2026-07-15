@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Reset Password</h1>
        </div>
    </div>
    <div class="card" style="max-width:520px;margin:0 auto;">
        <p class="text-muted" style="margin-bottom:16px;">Enter your new password below.</p>
        <form method="POST" action="{{ route('password.reset') }}">
            @csrf
            <label>New Password</label>
            <input class="input" type="password" name="password" required autofocus>
            <label>Confirm Password</label>
            <input class="input" type="password" name="password_confirmation" required>
            @error('password')
                <p class="text-danger">{{ $message }}</p>
            @enderror
            <button class="btn" type="submit" style="margin-top:16px;">Reset Password</button>
        </form>
    </div>
@endsection
