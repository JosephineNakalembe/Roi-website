<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmedMail;
use App\Models\Address;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Please login to checkout');
        }

        $user = Auth::user();
        $cartItems = $user->cartItems()->with('product.primaryImage')->get();

        $items = $cartItems->map(function ($cartItem) {
            $product = $cartItem->product;
            return $product ? [
                'product' => $product,
                'quantity' => $cartItem->quantity,
                'color' => $cartItem->color,
                'size' => $cartItem->size,
                'total' => $product->price * $cartItem->quantity,
            ] : null;
        })->filter();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $subtotal = $items->sum('total');
        $deliveryAreas = DeliveryArea::pluck('fee', 'name')->toArray();

        return view('checkout.show', compact('items', 'subtotal', 'addresses', 'deliveryAreas'));
    }

    public function process(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'delivery_area' => ['required', 'string'],
            'address_line' => ['required', 'string', 'max:500'],
            'save_default' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate delivery area
        $deliveryAreas = DeliveryArea::pluck('fee', 'name')->toArray();
        if (!isset($deliveryAreas[$data['delivery_area']])) {
            return back()->withErrors(['delivery_area' => 'Area Out of Delivery Scope'])->withInput();
        }

        $shipping = $deliveryAreas[$data['delivery_area']];

        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $items = $cartItems->map(function ($cartItem) {
            $product = $cartItem->product;
            return $product ? [
                'product' => $product,
                'quantity' => $cartItem->quantity,
                'color' => $cartItem->color,
                'size' => $cartItem->size,
            ] : null;
        })->filter();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Products in your cart are no longer available.']);
        }

        // Validate per-variant stock for every item
        foreach ($items as $item) {
            $variantStock = $item['product']->getVariantStock($item['color'], $item['size']);
            if ($variantStock < $item['quantity']) {
                return back()->withErrors(['cart' => 'Insufficient stock for "' . $item['product']->name . '" (' . ($item['color'] ?: '') . ($item['size'] ? ' / ' . $item['size'] : '') . '). Available: ' . $variantStock . '.']);
            }
        }

        // Save or update default address if toggled on
        if ($request->boolean('save_default')) {
            $user->update([
                'name' => $data['shipping_name'],
                'phone' => $data['shipping_phone'],
            ]);

            $user->addresses()->updateOrCreate(
                ['is_default' => true, 'user_id' => $user->id],
                [
                    'label' => 'Default',
                    'line1' => $data['address_line'],
                    'city' => $data['delivery_area'],
                    'country' => 'Uganda',
                ]
            );
        }

        $subtotal = $items->sum(fn ($item) => $item['product']->price * $item['quantity']);
        $total = $subtotal + $shipping;

        $order = Order::create([
            'user_id' => $user->id,
            'shipping_name' => $data['shipping_name'],
            'shipping_phone' => $data['shipping_phone'],
            'delivery_area' => $data['delivery_area'],
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'status' => 'pending',
            'notes' => 'Delivery Area: ' . $data['delivery_area'] . ' | Address: ' . $data['address_line'] . ($data['notes'] ? ' | ' . $data['notes'] : ''),
            'placed_at' => now(),
        ]);

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $color = $item['color'] ?? null;
            $size = $item['size'] ?? null;
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'total_price' => $product->price * $quantity,
                'color' => $color,
                'size' => $size,
            ]);
            // Decrement flat stock
            $product->decrement('stock', $quantity);
            // Decrement variant stock in color_stock
            if ($product->color_stock) {
                $colorStock = $product->color_stock;
                $key = $size ? "$color ($size)" : ($color ?: '');
                if (isset($colorStock[$key])) {
                    $colorStock[$key] -= $quantity;
                    if ($colorStock[$key] < 0) $colorStock[$key] = 0;
                    $product->color_stock = $colorStock;
                    $product->saveQuietly();
                }
            }
        }

        // Add initial order update with estimated delivery (1-3 day range)
        $estStart = now()->addDay()->format('d/m/Y');
        $estEnd = now()->addDays(3)->format('d/m/Y');
        $order->updates()->create([
            'order_id' => $order->id,
            'status' => 'pending',
            'note' => '📅 Estimated delivery: ' . $estStart . ' - ' . $estEnd,
        ]);

        // Clear the cart from database
        $user->cartItems()->delete();

        // Send order confirmation email
        try {
            $order->load('user', 'items');
            Mail::to($user->email)->send(new OrderConfirmedMail($order));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send order confirmation email: ' . $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Your order has been placed successfully. Estimated delivery: ' . $estStart . ' - ' . $estEnd . '.');
    }
}
