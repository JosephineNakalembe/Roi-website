<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expenditure;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.reports.index', $this->buildData($request));
    }

    /**
     * Download any section of the report as a CSV for offline record keeping.
     */
    public function export(Request $request, string $type)
    {
        $data = $this->buildData($request);

        $rows = match ($type) {
            'daily' => $this->dailyCsvRows($data),
            'products' => $this->productCsvRows($data),
            'stock' => $this->stockCsvRows($data),
            'ledger' => $this->ledgerCsvRows($data),
            default => abort(404),
        };

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'roi-' . $type . '-report-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildData(Request $request): array
    {
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');
        $lowStockThreshold = max(0, (int) $request->input('low_stock', 5));

        // Base query for orders
        $ordersQuery = Order::whereIn('status', ['pending', 'shipped', 'delivered']);

        if ($dateFrom) {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }

        // Total sales (order totals are already reduced when the admin cancels
        // an item, so cancelled value must not be subtracted a second time).
        $totalSalesRaw = (float) $ordersQuery->sum('total');
        $orderCount = $ordersQuery->count();

        // Refunded returns give the money back to the customer, so that item
        // value comes off the sales total and out of the product breakdown.
        $refundedReturnIds = OrderReturn::where('status', 'refunded')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->whereIn('status', ['pending', 'shipped', 'delivered']);
                if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
            })
            ->pluck('id');

        $refundedItemIds = OrderReturnItem::whereIn('order_return_id', $refundedReturnIds)
            ->pluck('order_item_id')
            ->unique()
            ->values();

        $refundedTotal = (float) OrderItem::whereIn('id', $refundedItemIds)->sum('total_price');

        $totalSales = $totalSalesRaw - $refundedTotal;

        // Get all products with their sold items via OrderItem
        $products = Product::with('category', 'stockBatches')->get();

        // ---------------------------------------------------------------
        // Stock on hand — how much worth is in stock, split by whether the
        // stock was obtained on credit (owe the supplier) or on debit
        // (already paid). This is a "right now" figure, independent of the
        // date filter above.
        // ---------------------------------------------------------------
        $stockReports = $products
            ->filter(fn ($product) => (int) $product->stock > 0)
            ->map(function ($product) {
                $split = $product->stockWorthSplit();

                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => (int) $product->stock,
                    'cost_price' => (float) ($product->cost_price ?: 0),
                    'worth' => $split['total'],
                    'credit' => $split['credit'],
                    'debit' => $split['debit'],
                    'type' => $product->stock_purchase_type,
                ];
            })
            ->sortBy('name')
            ->values();

        $stockWorth = (float) $stockReports->sum('worth');
        $stockCreditWorth = (float) $stockReports->sum('credit');
        $stockDebitWorth = (float) $stockReports->sum('debit');
        $stockUnits = (int) $stockReports->sum('stock');

        $productReports = collect();

        foreach ($products as $product) {
            $itemsQuery = OrderItem::where('product_id', $product->id)
                ->whereNull('cancelled_at') // do not count admin-cancelled items
                ->whereNotIn('id', $refundedItemIds->all()) // do not count refunded items
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
                // Sold items are automatically recorded as purchased on debit:
                // to deliver them the admin first had to buy them from the supplier.
                'purchase_type' => 'debit',
            ]);
        }

        $productReports = $productReports->values();

        // Totals
        $totalRevenue = $productReports->sum('revenue');
        $totalCost = $productReports->sum('total_cost');
        $totalProfit = $productReports->sum('profit');
        $overallMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;
        $totalItemsSold = $productReports->sum('qty_sold');

        // Value of items sold in this period — automatically counted as
        // purchased on debit because the sale funds the purchase from the supplier.
        $soldOnDebit = (float) $totalCost;

        // ---------------------------------------------------------------
        // Sales by day (item revenue, net of cancellations and refunds)
        // ---------------------------------------------------------------
        $dailySales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereNull('order_items.cancelled_at')
            ->whereNotIn('order_items.id', $refundedItemIds->all())
            ->whereIn('orders.status', ['pending', 'shipped', 'delivered'])
            ->when($dateFrom, fn ($q) => $q->whereDate('orders.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('orders.created_at', '<=', $dateTo))
            ->selectRaw("date(orders.created_at) as day, count(distinct orders.id) as orders, sum(order_items.quantity) as items, sum(order_items.total_price) as revenue, sum(order_items.quantity * COALESCE(products.cost_price, 0)) as cost")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                $revenue = (float) $row->revenue;
                $cost = (float) $row->cost;

                return (object) [
                    'day' => $row->day,
                    'orders' => (int) $row->orders,
                    'items' => (int) $row->items,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                ];
            });

        // Expenditures recorded for the same period + true net profit
        $expendituresTotal = (float) Expenditure::query()
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->sum('amount');

        $netProfit = $totalProfit - $expendituresTotal;

        // Products running low — reorder before they run out and stall sales
        $lowStockProducts = Product::with('stockBatches')
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->get()
            ->map(fn ($product) => (object) [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $product->name,
                'stock' => (int) $product->stock,
                'supplier' => $product->supplier,
                'cost_price' => (float) ($product->cost_price ?: 0),
                'type' => $product->stock_purchase_type,
            ]);

        // Recent stock-in history (restocks with their credit/debit type)
        $stockLedger = StockBatch::with('product:id,name,product_id')
            ->latest('id')
            ->limit(50)
            ->get();

        return [
            'productReports' => $productReports,
            'stockReports' => $stockReports,
            'stockWorth' => $stockWorth,
            'stockCreditWorth' => $stockCreditWorth,
            'stockDebitWorth' => $stockDebitWorth,
            'stockUnits' => $stockUnits,
            'soldOnDebit' => $soldOnDebit,
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'overallMargin' => $overallMargin,
            'totalItemsSold' => $totalItemsSold,
            'refundedTotal' => $refundedTotal,
            'dailySales' => $dailySales,
            'expendituresTotal' => $expendituresTotal,
            'netProfit' => $netProfit,
            'lowStockProducts' => $lowStockProducts,
            'lowStockThreshold' => $lowStockThreshold,
            'stockLedger' => $stockLedger,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }

    private function dailyCsvRows(array $data): array
    {
        $rows = [['Date', 'Orders', 'Items Sold', 'Revenue', 'Cost', 'Profit']];
        foreach ($data['dailySales'] as $day) {
            $rows[] = [$day->day, $day->orders, $day->items, round($day->revenue, 2), round($day->cost, 2), round($day->profit, 2)];
        }
        $rows[] = [
            'Totals',
            $data['dailySales']->sum('orders'),
            $data['dailySales']->sum('items'),
            round((float) $data['dailySales']->sum('revenue'), 2),
            round((float) $data['dailySales']->sum('cost'), 2),
            round((float) $data['dailySales']->sum('profit'), 2),
        ];

        return $rows;
    }

    private function productCsvRows(array $data): array
    {
        $rows = [['Product', 'Category', 'Qty Sold', 'Purchase Type', 'Revenue', 'Cost Price', 'Total Cost', 'Profit', 'Margin %']];
        foreach ($data['productReports'] as $report) {
            $rows[] = [$report->name, $report->category, $report->qty_sold, 'debit (auto)', $report->revenue, $report->cost_price, $report->total_cost, $report->profit, $report->margin];
        }
        $rows[] = ['Totals', '', $data['totalItemsSold'], 'debit (auto)', $data['totalRevenue'], '', $data['totalCost'], $data['totalProfit'], $data['overallMargin']];

        return $rows;
    }

    private function stockCsvRows(array $data): array
    {
        $labels = ['credit' => 'On Credit', 'debit' => 'On Debit', 'mixed' => 'Mixed'];
        $rows = [['Product', 'Units In Stock', 'Cost Per Unit', 'Stock Worth', 'On Credit', 'On Debit', 'Purchase Type']];
        foreach ($data['stockReports'] as $stock) {
            $rows[] = [$stock->name, $stock->stock, $stock->cost_price, round($stock->worth, 2), round($stock->credit, 2), round($stock->debit, 2), $labels[$stock->type] ?? 'On Debit'];
        }
        $rows[] = ['Totals', $data['stockUnits'], '', round($data['stockWorth'], 2), round($data['stockCreditWorth'], 2), round($data['stockDebitWorth'], 2), ''];

        return $rows;
    }

    private function ledgerCsvRows(array $data): array
    {
        $rows = [['Date', 'Product', 'Quantity Added', 'Still In Stock', 'Unit Cost', 'Batch Value', 'Purchase Type']];
        foreach ($data['stockLedger'] as $entry) {
            $rows[] = [
                $entry->created_at?->format('Y-m-d H:i'),
                $entry->product?->name ?? 'Deleted product',
                $entry->quantity,
                $entry->quantity_remaining,
                $entry->unit_cost,
                round((int) $entry->quantity * (float) $entry->unit_cost, 2),
                $entry->payment_type,
            ];
        }

        return $rows;
    }
}
