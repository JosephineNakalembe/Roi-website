<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    protected function mergeGuestCart(Request $request, $user): void
    {
        $guestCart = $request->session()->get('guest_cart', []);

        if (empty($guestCart)) return;

        foreach ($guestCart as $item) {
            $existingItem = $user->cartItems()
                ->where('product_id', $item['product_id'])
                ->where('color', $item['color'] ?? null)
                ->where('size', $item['size'] ?? null)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $item['quantity'],
                    'selected' => true,
                ]);
            } else {
                CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'selected' => $item['selected'] ?? true,
                ]);
            }
        }

        $request->session()->forget('guest_cart');
    }

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

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors(['email' => 'Invalid login credentials'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account is not active.']);
        }

        $this->mergeGuestCart($request, $user);

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

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $isAdminEmail ? 'admin' : 'user',
            'status' => 'active',
        ]);

        Auth::login($user);

        $this->mergeGuestCart($request, $user);

        return redirect()->route('shop.index')
            ->with('success', 'Registration successful!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
