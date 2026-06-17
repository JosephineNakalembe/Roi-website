<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    const DELIVERY_AREAS = [
        'Kampala Road' => 3500,
        'Nakasero' => 4000,
        'Old Kampala' => 3000,
        'Kisenyi' => 3500,
        'Wandegeya' => 3000,
        'Makerere' => 2000,
        'Ntinda' => 6000,
        'Naguru' => 5000,
        'Bugolobi' => 7000,
        'Nakawa' => 6500,
        'Kyambogo' => 7000,
        'Banda' => 10000,
        'Kiwatule' => 7000,
        'Namugongo' => 14000,
        'Kololo' => 5000,
        'Bukoto' => 5000,
        'Kamwokya' => 4000,
        'Acacia Area' => 4500,
        'Kisementi' => 3500,
        'Muyenga' => 7000,
        'Makindye' => 13000,
        'Kansanga' => 7000,
        'Ggaba' => 12500,
        'Munyonyo' => 14000,
        'Buziga' => 12000,
        'Zana' => 8000,
        'Bunamwaya' => 10000,
        'Najjanankumbi' => 7000,
        'Lubowa' => 7000,
        'Seguku' => 9000,
        'Kajjansi' => 14000,
        'Rubaga' => 4400,
        'Mengo' => 4000,
        'Namirembe' => 5000,
        'Kawempe' => 6000,
        'Bwaise' => 5000,
        'Kazo' => 5000,
        'Kanyanya' => 5000,
        'Maganjo' => 5500,
        'Kyaliwajjala' => 13000,
        'Kira' => 12500,
        'Najjera' => 10000,
        'Bulindo' => 15000,
    ];

    public function show(Request $request)
    {
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
        $deliveryAreas = self::DELIVERY_AREAS;

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
        $deliveryAreas = self::DELIVERY_AREAS;
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
            $product->decrement('stock', $quantity);
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

        return redirect()->route('orders.index')->with('success', 'Your order has been placed successfully. Estimated delivery: ' . $estStart . ' - ' . $estEnd . '.');
    }
}