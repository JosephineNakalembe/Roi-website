@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:520px;margin:0 auto;">
        <h1>Create Account</h1>
        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <label>Name</label>
            <input class="input" type="text" name="name" value="{{ old('name') }}" required autofocus>
            <label>Email</label>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required>
            <label>Password</label>
            <input class="input" type="password" name="password" required>
            <label>Confirm Password</label>
            <input class="input" type="password" name="password_confirmation" required>
            <button class="btn" type="submit" style="margin-top:16px;">Register</button>
        </form>
    </div>
@endsection
