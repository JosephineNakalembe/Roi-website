<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderUpdate;
use Illuminate\Http\Request;

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

        $order->update(['status' => $data['status']]);

        OrderUpdate::create([
            'order_id' => $order->id,
            'status' => $data['status'],
            'note' => $data['note'] ?? 'Order status updated to ' . ucfirst($data['status']) . '.',
        ]);

        return back()->with('success', 'Order status updated and tracking history saved.');
    }
}
