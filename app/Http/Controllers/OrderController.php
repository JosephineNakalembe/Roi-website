<?php

namespace App\Http\Controllers;

use App\Mail\OrderCancelledMail;
use App\Mail\OrderDeliveredMail;
use App\Models\Order;
use App\Models\OrderItemReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
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
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product', 'items.review', 'address', 'updates', 'returns.items.orderItem', 'returns.statusUpdates');
        return view('orders.show', compact('order'));
    }

    public function confirmReceived(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

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

        // Send delivery confirmation email
        try {
            $order->load('user', 'items');
            Mail::to($order->user->email)->send(new OrderDeliveredMail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send delivery email: ' . $e->getMessage());
        }

        return back()->with('success', 'You have confirmed receipt of your items. Please leave a review for the products.');
    }

    public function review(Request $request, Order $order, $itemId)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

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

    public function bulkReview(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->withErrors(['You can only review items after confirming delivery.']);
        }

        $data = $request->validate([
            'reviews' => ['required', 'array'],
            'reviews.*.rating' => ['nullable', 'integer', 'between:1,5'],
            'reviews.*.comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviewCount = 0;

        foreach ($data['reviews'] as $itemId => $reviewData) {
            // Skip if no rating provided (optional reviews)
            if (!isset($reviewData['rating']) || empty($reviewData['rating'])) {
                continue;
            }

            $item = $order->items()->where('id', $itemId)->first();

            if (!$item || $item->review) {
                continue; // Skip if item doesn't exist or already reviewed
            }

            $item->review()->create([
                'user_id' => Auth::id(),
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'] ?? null,
            ]);

            $reviewCount++;
        }

        if ($reviewCount > 0) {
            return back()->with('success', "Thank you! {$reviewCount} review(s) have been submitted.");
        }

        return back()->with('info', 'No reviews were submitted. Please provide at least one rating.');
    }

    public function cancel(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $order = Order::find($data['order_id']);

        if (!$order || $order->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['success' => false, 'message' => 'This order cannot be cancelled'], 400);
        }

        // Update order status to cancelled
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $data['reason'],
        ]);

        // Create an order update for tracking
        $order->updates()->create([
            'order_id' => $order->id,
            'status' => 'cancelled',
            'note' => 'Order cancelled by buyer. Reason: ' . $data['reason'],
        ]);

        // Send cancellation email
        try {
            $order->load('user', 'items');
            Mail::to($order->user->email)->send(new OrderCancelledMail($order));
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation email: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully']);
    }

}
