<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        return view('profile.dashboard', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($data);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function addresses()
    {
        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->get();
        return view('profile.addresses', compact('addresses'));
    }

    public function saveAddress(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        if ($data['is_default'] ?? false) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($data);
        return back()->with('success', 'Address saved.');
    }

    public function paymentMethods()
    {
        $paymentMethods = Auth::user()->paymentMethods()->orderByDesc('is_default')->get();
        return view('profile.payments', compact('paymentMethods'));
    }

    public function savePaymentMethod(Request $request)
    {
        $data = $request->validate([
            'cardholder' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'digits_between:12,19'],
            'expiry_month' => ['required', 'digits:2'],
            'expiry_year' => ['required', 'digits:4'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        if ($data['is_default'] ?? false) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $user->paymentMethods()->create([
            'cardholder' => $data['cardholder'],
            'provider' => 'card',
            'last4' => substr($data['card_number'], -4),
            'expiry_month' => $data['expiry_month'],
            'expiry_year' => $data['expiry_year'],
            'is_default' => $data['is_default'] ?? false,
        ]);

        return back()->with('success', 'Payment method saved.');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        $user = Auth::user();

        // Prevent admins from deleting their account via this route
        if ($user->isAdmin()) {
            return back()->withErrors(['delete' => 'Admin accounts cannot be deleted through this form.']);
        }

        DB::transaction(function () use ($user) {
            // 1. Anonymize all orders — keep them for admin reports but remove user association
            $user->orders()->update(['user_id' => null]);

            // 2. Delete personal data
            $user->addresses()->delete();
            $user->paymentMethods()->delete();
            $user->cartItems()->delete();
            $user->wishlistItems()->delete();
            $user->customerMessages()->delete();

            // 3. Delete the user account
            $user->delete();
        });

        // Log the user out since their account no longer exists
        Auth::logout();

        return redirect()->route('home')->with('success', 'Your account has been permanently deleted. Your order history remains on record.');
    }
}
