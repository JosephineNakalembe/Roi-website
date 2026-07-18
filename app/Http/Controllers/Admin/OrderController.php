<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\OrderUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category'); // new, pending, shipped, delivered

        $orders = Order::with('user', 'items', 'updates');

        switch ($category) {
            case 'new':
                $orders->where('status', 'pending')
                    ->whereDoesntHave('updates', function ($q) {
                        $q->whereIn('status', ['shipped', 'delivered']);
                    });
                break;
            case 'pending':
                $orders->where('status', 'pending');
                break;
            case 'shipped':
                $orders->where('status', 'shipped');
                break;
            case 'delivered':
                $orders->where('status', 'delivered');
                break;
        }

        $orders = $orders->latest()->paginate(20);

        // Counts for dashboard cards
        $newOrdersCount = Order::where('status', 'pending')
            ->whereDoesntHave('updates', function ($q) {
                $q->whereIn('status', ['shipped', 'delivered']);
            })->count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $shippedOrdersCount = Order::where('status', 'shipped')->count();
        $deliveredOrdersCount = Order::where('status', 'delivered')->count();

        return view('admin.orders.index', compact('orders', 'category', 'newOrdersCount', 'pendingOrdersCount', 'shippedOrdersCount', 'deliveredOrdersCount'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user', 'address', 'paymentMethod', 'updates');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,shipped,delivered'],
            'note' => ['nullable', 'string'],
        ]);

        $updates = ['status' => $data['status']];

        // Set delivered_at when order is marked as delivered
        if ($data['status'] === 'delivered' && !$order->delivered_at) {
            $updates['delivered_at'] = now();
        }

        $order->update($updates);

        OrderUpdate::create([
            'order_id' => $order->id,
            'status' => $data['status'],
            'note' => $data['note'] ?? 'Order status updated to ' . ucfirst($data['status']) . '.',
        ]);

        // Send status notification email to customer
        $emailMap = [
            'shipped' => OrderShippedMail::class,
            'delivered' => OrderDeliveredMail::class,
        ];
        if (isset($emailMap[$data['status']])) {
            try {
                $order->load('user', 'items');
                Mail::to($order->user->email)->send(new $emailMap[$data['status']]($order));
            } catch (\Exception $e) {
                Log::error('Failed to send order email (' . $data['status'] . '): ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Order status updated and tracking history saved.');
    }
}
