<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwnership;
use App\Models\Order;
use App\Models\OrderItemReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use AuthorizesOwnership;

    public function index()
    {
        $user = Auth::user();

        $orders = $user->orders()
            ->with('items', 'items.review', 'updates')
            ->latest()
            ->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorizeOwnership($order);

        $order->load('items.product', 'items.review', 'address', 'updates', 'returns.items.orderItem', 'returns.statusUpdates');
        return view('orders.show', compact('order'));
    }

    public function confirmReceived(Order $order)
    {
        $this->authorizeOwnership($order);

        if ($order->status !== 'shipped') {
            return back()->withErrors(['Order can only be confirmed as received when it is shipped.']);
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Create an order update for tracking
        $order->updates()->create([
            'order_id' => $order->id,
            'status' => 'delivered',
            'note' => 'Buyer confirmed receipt of items.',
        ]);

        return back()->with('success', 'You have confirmed receipt of your items. Please leave a review for the products.');
    }

    public function review(Request $request, Order $order, $itemId)
    {
        $this->authorizeOwnership($order);

        if ($order->status !== 'delivered') {
            return back()->withErrors(['You can only review items after confirming delivery.']);
        }

        $item = $order->items()->where('id', $itemId)->firstOrFail();

        if ($item->review) {
            return back()->withErrors(['This item has already been reviewed.']);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $item->review()->create([
            'user_id' => Auth::id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('success', 'Thank you! Your review has been submitted.');
    }

}
