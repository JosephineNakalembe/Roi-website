<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid login credentials'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account is not active.']);
        }

        $isAdminEmail = $user->email === 'josephinenakalembe33@gmail.com';
        return redirect()->intended($isAdminEmail ? route('admin.dashboard') : route('shop.index'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $isAdminEmail = $data['email'] === 'josephinenakalembe33@gmail.com';

        // Generate verification token
        $verificationToken = Str::random(60);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $isAdminEmail ? 'admin' : 'user',
            'status' => 'active',
            'verification_token' => $verificationToken,
            'verification_token_expires_at' => now()->addHours(24),
        ]);

        // Send verification email
        $verificationUrl = route('verification.verify', $verificationToken);
        Mail::send('emails.verify', ['verificationUrl' => $verificationUrl, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify Your Email Address');
        });

        // Auto-login the user but redirect to a page telling them to verify email
        Auth::login($user);

        return redirect()->route('shop.index')
            ->with('success', 'Registration successful! Please check your email to verify your account.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
