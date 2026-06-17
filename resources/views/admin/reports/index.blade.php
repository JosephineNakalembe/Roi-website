@extends('layouts.app')

@section('content')
<div class="card" style="max-width:1200px;margin:0 auto;">
    <h1>Product Reports</h1>
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
        <button class="btn" type="submit">Filter</button>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Clear</a>
    </form>

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
    </div>

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
</div>
@endsection