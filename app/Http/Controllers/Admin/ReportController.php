<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        // Base query for orders
        $ordersQuery = Order::whereIn('status', ['pending', 'shipped', 'delivered']);

        if ($dateFrom) {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }

        // Total sales
        $totalSales = (float) $ordersQuery->sum('total');
        $orderCount = $ordersQuery->count();

        // Get all products with their sold items via OrderItem
        $products = Product::with('category')->get();

        $productReports = collect();

        foreach ($products as $product) {
            $itemsQuery = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereIn('status', ['pending', 'shipped', 'delivered']);
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
                });

            $soldQty = (int) $itemsQuery->sum('quantity');
            $revenue = (float) $itemsQuery->sum('total_price');

            if ($soldQty <= 0) continue;

            $costPrice = (float) ($product->cost_price ?: 0);
            $totalCost = $costPrice * $soldQty;
            $profit = $revenue - $totalCost;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;

            $productReports->push((object) [
                'name' => $product->name,
                'category' => $product->category?->name ?? 'N/A',
                'qty_sold' => $soldQty,
                'revenue' => $revenue,
                'cost_price' => $costPrice,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'margin' => $margin,
            ]);
        }

        $productReports = $productReports->values();

        // Totals
        $totalRevenue = $productReports->sum('revenue');
        $totalCost = $productReports->sum('total_cost');
        $totalProfit = $productReports->sum('profit');
        $overallMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;
        $totalItemsSold = $productReports->sum('qty_sold');

        return view('admin.reports.index', compact(
            'productReports',
            'totalSales',
            'orderCount',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'overallMargin',
            'totalItemsSold',
            'dateFrom',
            'dateTo'
        ));
    }
}
