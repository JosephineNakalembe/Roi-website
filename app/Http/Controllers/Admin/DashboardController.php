<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::count();
        $pending = Order::where('status', 'pending')->count();
        $shipped = Order::where('status', 'shipped')->count();
        $delivered = Order::where('status', 'delivered')->count();
        $products = Product::count();
        $users = User::count();
        $messages = CustomerMessage::count();
        $openMessages = CustomerMessage::where('status', 'open')->count();

        return view('admin.dashboard', compact('orders', 'pending', 'shipped', 'delivered', 'products', 'users', 'messages', 'openMessages'));
    }
}
