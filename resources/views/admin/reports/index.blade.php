@extends('layouts.app')

@section('content')
<div class="sticky-header">
    <div class="header-content">
        @include('partials.back-button', ['fallback' => route('admin.dashboard')])
        <h1 class="mb-0">Product Reports</h1>
    </div>
</div>
<div class="card" style="max-width:1200px;margin:0 auto;">
    <p class="text-muted">Profit and sales breakdown — cost prices are admin-only</p>

    <!-- Filter by date -->
    <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:20px;padding:16px;background:#f9fafb;border-radius:12px;">
        <div>
            <label style="font-size:0.85rem;">From</label>
            <input type="date" name="from" class="input" value="{{ $dateFrom ?? '' }}" style="padding:6px 10px;">
        </div>
        <div>
            <label style="font-size:0.85rem;">To</label>
            <input type="date" name="to" class="input" value="{{ $dateTo ?? '' }}" style="padding:6px 10px;">
        </div>
        <div>
            <label style="font-size:0.85rem;">Low stock ≤</label>
            <input type="number" name="low_stock" class="input" min="0" value="{{ $lowStockThreshold }}" style="padding:6px 10px;width:80px;">
        </div>
        <button class="btn" type="submit">Filter</button>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <!-- CSV exports for offline record keeping -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:20px;">
        <span class="text-muted" style="font-size:0.9rem;">Export CSV:</span>
        @foreach ([
            'daily' => 'Sales by day',
            'products' => 'Product sales',
            'stock' => 'Stock on hand',
            'ledger' => 'Restock history',
        ] as $exportType => $exportLabel)
            <a class="btn btn-secondary" style="padding:6px 12px;font-size:0.9rem;"
               href="{{ route('admin.reports.export', array_merge(['type' => $exportType], request()->only(['from', 'to', 'low_stock']))) }}">
                {{ $exportLabel }}
            </a>
        @endforeach
    </div>

    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($totalSales, 0) }}</div>
            <div class="stat-label">Total Sales</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($totalProfit, 0) }}</div>
            <div class="stat-label">Total Profit</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $overallMargin }}%</div>
            <div class="stat-label">Profit Margin</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $orderCount }}</div>
            <div class="stat-label">Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalItemsSold }}</div>
            <div class="stat-label">Items Sold</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($totalCost, 0) }}</div>
            <div class="stat-label">Total Cost</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($expendituresTotal, 0) }}</div>
            <div class="stat-label">Expenditures</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:{{ $netProfit >= 0 ? '#2e7d32' : '#c62828' }};">UGX{{ number_format($netProfit, 0) }}</div>
            <div class="stat-label">Net Profit (after expenses)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($refundedTotal, 0) }}</div>
            <div class="stat-label">Refunded Returns (deducted)</div>
        </div>
    </div>

    <!-- Sales by Day -->
    <div style="margin-bottom:4px;">
        <h2 style="margin:0;font-size:1.15rem;">Sales by Day</h2>
        <p class="text-muted" style="font-size:0.92rem;margin:4px 0 12px;">Item sales per day for the selected period — net of cancellations and refunds.</p>
    </div>
    @if($dailySales->isEmpty())
        <p style="text-align:center;padding:24px;color:#6b7280;">No sales in this period.</p>
    @else
        <div style="overflow-x:auto;margin-bottom:24px;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th style="text-align:center;">Orders</th>
                        <th style="text-align:center;">Items</th>
                        <th style="text-align:right;">Revenue</th>
                        <th style="text-align:right;">Cost</th>
                        <th style="text-align:right;">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailySales as $day)
                        <tr>
                            <td style="font-weight:600;">{{ \Illuminate\Support\Carbon::parse($day->day)->format('D, d M Y') }}</td>
                            <td style="text-align:center;">{{ $day->orders }}</td>
                            <td style="text-align:center;">{{ $day->items }}</td>
                            <td style="text-align:right;">UGX{{ number_format($day->revenue, 0) }}</td>
                            <td style="text-align:right;">UGX{{ number_format($day->cost, 0) }}</td>
                            <td style="text-align:right;font-weight:600;color:{{ $day->profit >= 0 ? '#2e7d32' : '#c62828' }};">UGX{{ number_format($day->profit, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8f9fa;font-weight:700;">
                        <td>Totals</td>
                        <td style="text-align:center;">{{ $dailySales->sum('orders') }}</td>
                        <td style="text-align:center;">{{ $dailySales->sum('items') }}</td>
                        <td style="text-align:right;">UGX{{ number_format($dailySales->sum('revenue'), 0) }}</td>
                        <td style="text-align:right;">UGX{{ number_format($dailySales->sum('cost'), 0) }}</td>
                        <td style="text-align:right;">UGX{{ number_format($dailySales->sum('profit'), 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- Stock on Hand: Credit vs Debit -->
    <div style="margin-bottom:4px;">
        <h2 style="margin:0;font-size:1.15rem;">Stock on Hand — Credit vs Debit</h2>
        <p class="text-muted" style="font-size:0.92rem;margin:4px 0 12px;">
            Worth of stock currently in store. <strong style="color:#b45309;">On credit</strong> = items gotten from the supplier but not yet paid for;
            <strong style="color:#2e7d32;">on debit</strong> = already paid for. When an item sells it automatically counts as purchased on debit —
            the admin must first buy it to deliver it. This is a current figure and is not affected by the date filter above.
        </p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px;">
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($stockWorth, 0) }}</div>
            <div class="stat-label">Stock Worth ({{ $stockUnits }} units)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#b45309;">UGX{{ number_format($stockCreditWorth, 0) }}</div>
            <div class="stat-label">On Credit (owed to suppliers)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#2e7d32;">UGX{{ number_format($stockDebitWorth, 0) }}</div>
            <div class="stat-label">On Debit (already paid)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">UGX{{ number_format($soldOnDebit, 0) }}</div>
            <div class="stat-label">Sold on Debit (this period)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#dc2626;">{{ $lowStockProducts->count() }}</div>
            <div class="stat-label">Low Stock Items (≤ {{ $lowStockThreshold }})</div>
        </div>
    </div>

    @if($stockReports->isEmpty())
        <p style="text-align:center;padding:24px;color:#6b7280;">No stock on hand right now.</p>
    @else
        <div style="overflow-x:auto;margin-bottom:24px;">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:center;">In Stock</th>
                        <th style="text-align:right;">Cost/Unit</th>
                        <th style="text-align:right;">Stock Worth</th>
                        <th style="text-align:right;">On Credit</th>
                        <th style="text-align:right;">On Debit</th>
                        <th style="text-align:center;">Type</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockReports as $stockRow)
                        <tr>
                            <td style="font-weight:600;">{{ $stockRow->name }}</td>
                            <td style="text-align:center;">{{ $stockRow->stock }}</td>
                            <td style="text-align:right;">UGX{{ number_format($stockRow->cost_price, 0) }}</td>
                            <td style="text-align:right;">UGX{{ number_format($stockRow->worth, 0) }}</td>
                            <td style="text-align:right;color:#b45309;">UGX{{ number_format($stockRow->credit, 0) }}</td>
                            <td style="text-align:right;color:#2e7d32;">UGX{{ number_format($stockRow->debit, 0) }}</td>
                            <td style="text-align:center;">
                                @if($stockRow->type === 'credit')
                                    <span class="badge badge-amber">On Credit</span>
                                @elseif($stockRow->type === 'mixed')
                                    <span class="badge badge-blue">Mixed</span>
                                @else
                                    <span class="badge badge-green">On Debit</span>
                                @endif
                            </td>
                            <td style="text-align:center;white-space:nowrap;">
                                @if($stockRow->type !== 'debit')
                                    <form method="POST" action="{{ route('admin.products.stock-type', $stockRow->id) }}" style="display:inline;margin:0;" onsubmit="return confirm('Mark all unsold stock of this product as bought ON DEBIT (paid)?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="payment_type" value="debit">
                                        <button type="submit" class="btn btn-secondary" style="padding:4px 10px;font-size:0.85rem;">Mark Debit</button>
                                    </form>
                                @endif
                                @if($stockRow->type !== 'credit')
                                    <form method="POST" action="{{ route('admin.products.stock-type', $stockRow->id) }}" style="display:inline;margin:0;" onsubmit="return confirm('Mark all unsold stock of this product as bought ON CREDIT (still owe the supplier)?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="payment_type" value="credit">
                                        <button type="submit" class="btn btn-secondary" style="padding:4px 10px;font-size:0.85rem;">Mark Credit</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8f9fa;font-weight:700;">
                        <td>Totals</td>
                        <td style="text-align:center;">{{ $stockUnits }}</td>
                        <td style="text-align:right;">—</td>
                        <td style="text-align:right;">UGX{{ number_format($stockWorth, 0) }}</td>
                        <td style="text-align:right;">UGX{{ number_format($stockCreditWorth, 0) }}</td>
                        <td style="text-align:right;">UGX{{ number_format($stockDebitWorth, 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- Low stock / reorder list -->
    <div style="margin-bottom:4px;">
        <h3 style="margin:0;font-size:1.05rem;">Reorder Soon — Low Stock (≤ {{ $lowStockThreshold }})</h3>
        <p class="text-muted" style="font-size:0.9rem;margin:4px 0 12px;">Restock these before they run out and hold up sales.</p>
    </div>
    @if($lowStockProducts->isEmpty())
        <p style="padding:4px 0 12px;color:#2e7d32;">All products are above the low-stock level.</p>
    @else
        <div style="overflow-x:auto;margin-bottom:24px;">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:center;">Left</th>
                        <th>Supplier</th>
                        <th style="text-align:right;">Cost/Unit</th>
                        <th style="text-align:center;">Type</th>
                        <th style="text-align:center;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $low)
                        <tr>
                            <td style="font-weight:600;">{{ $low->name }} <span class="text-muted" style="font-weight:400;">({{ $low->product_id }})</span></td>
                            <td style="text-align:center;color:#dc2626;font-weight:600;">{{ $low->stock }}</td>
                            <td>{{ $low->supplier ?: '—' }}</td>
                            <td style="text-align:right;">UGX{{ number_format($low->cost_price, 0) }}</td>
                            <td style="text-align:center;">
                                @if($low->stock <= 0)
                                    <span class="badge badge-red">Out of Stock</span>
                                @elseif($low->type === 'credit')
                                    <span class="badge badge-amber">On Credit</span>
                                @elseif($low->type === 'mixed')
                                    <span class="badge badge-blue">Mixed</span>
                                @else
                                    <span class="badge badge-green">On Debit</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a class="btn btn-secondary" style="padding:4px 10px;font-size:0.85rem;background:#059669;color:#fff;border:none;" href="{{ route('admin.products.index', ['search' => $low->product_id]) }}">Restock</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:8px 0 20px;">

    <p class="text-muted" style="font-size:0.92rem;margin-bottom:12px;">
        Sales breakdown — sold items are automatically recorded as <strong>purchased on debit</strong>
        because to deliver an item the admin must first buy it from the supplier.
    </p>

    @if($productReports->isEmpty())
        <p style="text-align:center;padding:40px;color:#6b7280;">No sales data yet. Products with cost prices will appear here once sold.</p>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th style="text-align:center;">Sold</th>
                        <th style="text-align:center;">Purchase</th>
                        <th style="text-align:right;">Revenue</th>
                        <th style="text-align:right;">Cost Price</th>
                        <th style="text-align:right;">Total Cost</th>
                        <th style="text-align:right;">Profit</th>
                        <th style="text-align:center;">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productReports as $report)
                        <tr>
                            <td style="font-weight:600;">{{ $report->name }}</td>
                            <td style="color:#6c757d;">{{ $report->category }}</td>
                            <td style="text-align:center;">{{ $report->qty_sold }}</td>
                            <td style="text-align:center;">
                                <span class="badge badge-green" title="Sold items are automatically counted as purchased on debit">Debit</span>
                            </td>
                            <td style="text-align:right;">UGX{{ number_format($report->revenue, 0) }}</td>
                            <td style="text-align:right;">UGX{{ number_format($report->cost_price, 0) }}</td>
                            <td style="text-align:right;">UGX{{ number_format($report->total_cost, 0) }}</td>
                            <td style="text-align:right;font-weight:600;color:{{ $report->profit >= 0 ? '#2e7d32' : '#c62828' }};">
                                UGX{{ number_format($report->profit, 0) }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $report->margin >= 0 ? 'badge-green' : 'badge-red' }}">
                                    {{ $report->margin }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8f9fa;font-weight:700;">
                        <td colspan="2">Totals</td>
                        <td style="text-align:center;">{{ $totalItemsSold }}</td>
                        <td style="text-align:center;"><span class="badge badge-green">Debit</span></td>
                        <td style="text-align:right;">UGX{{ number_format($totalRevenue, 0) }}</td>
                        <td style="text-align:right;">—</td>
                        <td style="text-align:right;">UGX{{ number_format($totalCost, 0) }}</td>
                        <td style="text-align:right;color:{{ $totalProfit >= 0 ? '#2e7d32' : '#c62828' }};">UGX{{ number_format($totalProfit, 0) }}</td>
                        <td style="text-align:center;">
                            <span class="badge {{ $overallMargin >= 0 ? 'badge-green' : 'badge-red' }}">{{ $overallMargin }}%</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- Restock history / stock-in ledger -->
    <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0 20px;">
    <div style="margin-bottom:4px;">
        <h2 style="margin:0;font-size:1.15rem;">Stock In — Restock History</h2>
        <p class="text-muted" style="font-size:0.92rem;margin:4px 0 12px;">
            Latest 50 stock deliveries with the credit/debit type recorded at restock.
            "Still in stock" drops as units sell (sold units count as purchased on debit).
        </p>
    </div>
    @if($stockLedger->isEmpty())
        <p style="text-align:center;padding:24px;color:#6b7280;">No stock has been recorded yet.</p>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th style="text-align:center;">Qty Added</th>
                        <th style="text-align:center;">Still in Stock</th>
                        <th style="text-align:right;">Unit Cost</th>
                        <th style="text-align:right;">Batch Value</th>
                        <th style="text-align:center;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockLedger as $entry)
                        <tr>
                            <td>{{ $entry->created_at->format('d M Y, H:i') }}</td>
                            <td style="font-weight:600;">{{ $entry->product?->name ?? 'Deleted product' }}</td>
                            <td style="text-align:center;">{{ $entry->quantity }}</td>
                            <td style="text-align:center;">{{ $entry->quantity_remaining }} / {{ $entry->quantity }}</td>
                            <td style="text-align:right;">UGX{{ number_format($entry->unit_cost, 0) }}</td>
                            <td style="text-align:right;">UGX{{ number_format($entry->quantity * $entry->unit_cost, 0) }}</td>
                            <td style="text-align:center;">
                                @if($entry->payment_type === 'credit')
                                    <span class="badge badge-amber">On Credit</span>
                                @else
                                    <span class="badge badge-green">On Debit</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection