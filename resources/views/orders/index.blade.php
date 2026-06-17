@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>My Orders</h1>

        @if($orders->isEmpty())
            <p>You have not placed any orders yet.</p>
        @else
            @php
                $activeOrders = $orders->filter(function($order) {
                    // Active: not delivered, or delivered but has unreviewed items
                    if ($order->status !== 'delivered') return true;
                    return $order->items->contains(fn($item) => !$item->review);
                });
                $pastOrders = $orders->filter(function($order) {
                    // Past: delivered AND all items have been reviewed
                    return $order->status === 'delivered' && !$order->items->contains(fn($item) => !$item->review);
                });
            @endphp

            @if($activeOrders->isNotEmpty())
                <h2 style="margin-bottom:16px;">Active Orders</h2>
                <div style="display:grid;gap:16px;margin-bottom:32px;">
                    @foreach($activeOrders as $order)
                        @php
                            $hasUnreviewedItems = $order->status === 'delivered' && $order->items->contains(fn($item) => !$item->review);
                            $latestUpdate = $order->updates->first();
                        @endphp
                        <div onclick="openOrderModal({{ $order->id }})" style="border:1px solid #e5e7eb;padding:16px;border-radius:14px;background:#fff;cursor:pointer;transition:box-shadow 0.15s;border-left:4px solid {{ $hasUnreviewedItems ? '#dc2626' : ($order->status === 'shipped' ? '#2563eb' : '#d1d5db') }};" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow=''">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                                <div>
                                    <strong>{{ $order->order_number }}</strong>
                                    <p class="text-muted" style="margin:2px 0 0;">{{ $order->placed_at->format('F j, Y') }}</p>
                                </div>
                                <div style="text-align:right;">
                                    <p style="margin:0;">
                                        @if($order->status === 'delivered')
                                            <span style="color:#059669;font-weight:600;">Delivered</span>
                                        @elseif($order->status === 'shipped')
                                            <span style="color:#2563eb;font-weight:600;">Shipped</span>
                                        @else
                                            <span style="color:#6b7280;">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </p>
                                    <p style="margin:2px 0 0;font-weight:700;">UGX{{ number_format($order->total, 2) }}</p>
                                </div>
                            </div>

                            <!-- Latest Update -->
                            @if($latestUpdate)
                                <div style="margin-top:10px;padding:10px 14px;background:#f9fafb;border-radius:10px;border-left:3px solid {{ $order->status === 'delivered' ? '#059669' : ($order->status === 'shipped' ? '#2563eb' : '#d1d5db') }};">
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                        <span style="font-size:0.85rem;font-weight:600;">
                                            @if($latestUpdate->status === 'shipped')
                                                <span style="color:#2563eb;">Shipped</span>
                                            @elseif($latestUpdate->status === 'delivered')
                                                <span style="color:#059669;">Delivered</span>
                                            @else
                                                {{ ucfirst($latestUpdate->status) }}
                                            @endif
                                        </span>
                                        <span style="font-size:0.75rem;color:#9ca3af;">{{ $latestUpdate->created_at->format('M d, H:i') }}</span>
                                    </div>
                                    @if($latestUpdate->note)
                                        <p style="margin:4px 0 0;font-size:0.85rem;color:#6b7280;">{{ $latestUpdate->note }}</p>
                                    @endif
                                </div>
                            @endif

                            <div style="margin-top:10px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                                <div>
                                    @if($hasUnreviewedItems)
                                        <span style="font-size:0.8rem;color:#dc2626;">Items pending review</span>
                                    @elseif($order->status === 'shipped')
                                        <span style="font-size:0.8rem;color:#2563eb;">Click to confirm receipt</span>
                                    @endif
                                </div>
                                <span style="font-size:0.8rem;color:#9ca3af;">Tap for details &rarr;</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($pastOrders->isNotEmpty())
                <h2 style="margin-bottom:16px;color:#6b7280;">Past Orders</h2>
                <div style="display:grid;gap:16px;">
                    @foreach($pastOrders as $order)
                        @php
                            $latestUpdate = $order->updates->first();
                        @endphp
                        <div onclick="openOrderModal({{ $order->id }})" style="border:1px solid #e5e7eb;padding:16px;border-radius:14px;background:#f9fafb;cursor:pointer;transition:box-shadow 0.15s;opacity:0.85;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';this.style.opacity='1'" onmouseout="this.style.boxShadow='';this.style.opacity='0.85'">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                                <div>
                                    <strong style="color:#6b7280;">{{ $order->order_number }}</strong>
                                    <p class="text-muted" style="margin:2px 0 0;">{{ $order->placed_at->format('F j, Y') }}</p>
                                </div>
                                <div style="text-align:right;">
                                    <p style="margin:0;">
                                        <span style="color:#059669;font-weight:600;">Delivered</span>
                                    </p>
                                    <p style="margin:2px 0 0;font-weight:700;color:#6b7280;">UGX{{ number_format($order->total, 2) }}</p>
                                </div>
                            </div>

                            <div style="margin-top:10px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                                <div>
                                    <span style="font-size:0.8rem;color:#6b7280;">✓ All items reviewed</span>
                                </div>
                                <span style="font-size:0.8rem;color:#9ca3af;">Tap for details &rarr;</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div style="margin-top:20px;">{{ $orders->withQueryString()->links() }}</div>
        @endif
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;overflow-y:auto;padding:20px;">
        <div class="card" style="max-width:700px;width:100%;background:#fff;margin:auto;max-height:90vh;overflow-y:auto;">
            <div id="orderModalContent">
                <p style="text-align:center;padding:30px;color:#6b7280;">Loading...</p>
            </div>
        </div>
    </div>

    <script>
        const ordersData = @json($orders->items());

        function openOrderModal(orderId) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderModalContent');
            const order = ordersData.find(o => o.id === orderId);

            if (!order) {
                content.innerHTML = '<p style="text-align:center;padding:30px;color:#dc2626;">Order not found.</p>';
                modal.style.display = 'flex';
                return;
            }

            const statusColor = order.status === 'delivered' ? '#059669' : (order.status === 'shipped' ? '#2563eb' : '#6b7280');
            const statusLabel = order.status === 'delivered' ? 'Delivered' : (order.status === 'shipped' ? 'Shipped' : order.status.charAt(0).toUpperCase() + order.status.slice(1));

            let updatesHtml = '';
            if (order.updates && order.updates.length > 0) {
                const sortedUpdates = order.updates;
                updatesHtml = `
                    <div style="position:relative;padding-left:24px;">
                        ${sortedUpdates.map((update, i) => {
                            const isFirst = i === 0;
                            const isLast = i === sortedUpdates.length - 1;
                            const upStatusColor = update.status === 'delivered' ? '#059669' : (update.status === 'shipped' ? '#2563eb' : '#d1d5db');
                            return `
                                <div style="position:relative;padding-bottom:${isLast ? '0' : '20px'};padding-left:16px;border-left:${isLast ? '2px solid transparent' : '2px solid #e5e7eb'};">
                                    <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:${upStatusColor};border:2px solid #fff;box-shadow:0 0 0 2px ${upStatusColor};"></div>
                                    <div style="background:${isFirst ? '#f0fdf4' : '#f9fafb'};padding:12px;border-radius:12px;border:1px solid ${isFirst ? '#bbf7d0' : '#e5e7eb'};">
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                            <strong style="color:${upStatusColor};">
                                                ${update.status === 'shipped' ? '🚚 Shipped' : (update.status === 'delivered' ? '✅ Delivered' : update.status.charAt(0).toUpperCase() + update.status.slice(1))}
                                            </strong>
                                            <span style="font-size:0.8rem;color:#9ca3af;">${new Date(update.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                        ${update.note ? `<p style="margin:6px 0 0;font-size:0.9rem;color:#374151;">${update.note}</p>` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                        <!-- Initial Order Placed -->
                        <div style="position:relative;padding-left:16px;">
                            <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:#6b7280;border:2px solid #fff;box-shadow:0 0 0 2px #6b7280;"></div>
                            <div style="background:#f9fafb;padding:12px;border-radius:12px;border:1px solid #e5e7eb;">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                    <strong style="color:#6b7280;">📦 Order Placed</strong>
                                    <span style="font-size:0.8rem;color:#9ca3af;">${new Date(order.placed_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                updatesHtml = '<p style="color:#9ca3af;text-align:center;padding:20px;">No tracking updates available yet.</p>';
            }

            // Check if there are unreviewed items using the review relation
            let unreviewedItems = 0;
            if (order.items) {
                order.items.forEach(item => { if (!item.review) unreviewedItems++; });
            }

            content.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
                    <div>
                        <h2 style="margin:0;">${order.order_number}</h2>
                        <p style="margin:4px 0 0;color:#6b7280;">${new Date(order.placed_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                    </div>
                    <button onclick="closeOrderModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;">&times;</button>
                </div>

                <div style="margin-bottom:16px;padding:14px;background:#f9fafb;border-radius:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <p style="margin:0;font-size:0.85rem;color:#6b7280;">Status</p>
                        <p style="margin:2px 0 0;font-weight:700;color:${statusColor};">${statusLabel}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="margin:0;font-size:0.85rem;color:#6b7280;">Total</p>
                        <p style="margin:2px 0 0;font-weight:700;">UGX${Math.round(order.total).toLocaleString('en-US')}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="margin:0;font-size:0.85rem;color:#6b7280;">Items</p>
                        <p style="margin:2px 0 0;font-weight:700;">${order.items ? order.items.length : 0}</p>
                    </div>
                </div>

                <h3>Tracking Updates</h3>
                ${updatesHtml}

                ${unreviewedItems > 0 ? `
                    <div style="margin-top:16px;padding:12px;background:#fef2f2;border-radius:12px;border:1px solid #fecaca;">
                        <p style="margin:0;font-size:0.85rem;color:#dc2626;">${unreviewedItems} item(s) pending your review.</p>
                    </div>
                ` : ''}

                <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ url('/orders') }}/${order.id}" class="btn btn-secondary" style="padding:8px 16px;font-size:0.9rem;">Full Details</a>
                    <button onclick="closeOrderModal()" class="btn" style="background:#6b7280;padding:8px 16px;font-size:0.9rem;">Close</button>
                </div>
            `;

            modal.style.display = 'flex';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) closeOrderModal();
        });
    </script>
@endsection