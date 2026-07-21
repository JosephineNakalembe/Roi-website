<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function verify($token)
    {
        $user = User::where('verification_token', $token)
            ->where('verification_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired verification link. Please request a new one.');
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);

        Auth::login($user);

        return redirect()->route('shop.index')
            ->with('success', 'Your email has been verified successfully!');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            return back()->with('info', 'Your email is already verified.');
        }

        $token = Str::random(60);
        $user->update([
            'verification_token' => $token,
            'verification_token_expires_at' => now()->addHours(24),
        ]);

        $verificationUrl = route('verification.verify', $token);

        try {
            Mail::send('emails.verify', ['verificationUrl' => $verificationUrl, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Email Address');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send verification email. Please try again.');
        }

        return back()->with('success', 'Verification email sent successfully!');
    }
}
