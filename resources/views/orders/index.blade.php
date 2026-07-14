@extends('layouts.app')

@section('content')
    <style>
        @media (max-width: 768px) {
            /* Consistent font size for entire page */
            .card, .order-card, .card h2, .card strong, .card p, .card span, .card a, .card button {
                font-size: 0.75rem !important;
            }
            .sticky-header h1 {
                font-size: 0.75rem !important;
            }
            /* Reduce spacing between order cards */
            .card > div[style*="display:grid"] {
                gap: 8px !important;
            }
            .order-card {
                padding: 8px !important;
            }
            .order-card .pending-review {
                font-size: 0.75rem !important;
            }
            .order-card .order-action-btn {
                padding: 1px 5px !important;
                font-size: 0.6rem !important;
                height: 20px !important;
                min-height: 20px !important;
                line-height: 1 !important;
                border-radius: 4px !important;
            }
            .order-card .button-container {
                flex-wrap: nowrap !important;
                gap: 4px !important;
            }
            .order-card .button-container a,
            .order-card .button-container button,
            .order-card .button-container span {
                font-size: 0.75rem !important;
                flex-shrink: 0 !important;
                text-align: center !important;
            }
            /* Cancel modal mobile responsive */
            #cancelModal .card {
                max-width: 90% !important;
                padding: 16px !important;
            }
            #cancelModal h2 {
                font-size: 0.85rem !important;
            }
            #cancelModal .textarea {
                font-size: 0.75rem !important;
            }
            #cancelModal .btn {
                padding: 6px 12px !important;
                font-size: 0.75rem !important;
            }
        }
    </style>
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">My Orders</h1>
        </div>
    </div>
    <div class="card">

        @if($orders->isEmpty())
            <p>You have not placed any orders yet.</p>
        @else
            @php
                $activeOrders = $orders->filter(function($order) {
                    // Active: not delivered/cancelled, or delivered but has unreviewed items
                    if ($order->status === 'cancelled') return false;
                    if ($order->status !== 'delivered') return true;
                    return $order->items->contains(fn($item) => !$item->review);
                });
                $pastOrders = $orders->filter(function($order) {
                    // Past: delivered AND all items have been reviewed
                    return $order->status === 'delivered' && !$order->items->contains(fn($item) => !$item->review);
                });
                $cancelledOrders = $orders->filter(function($order) {
                    // Cancelled: status is cancelled
                    return $order->status === 'cancelled';
                });
            @endphp

            @if($activeOrders->isNotEmpty())
                <h2 style="margin-bottom:16px;">Active Orders</h2>
                <div style="display:grid;gap:16px;margin-bottom:32px;">
                    @foreach($activeOrders as $order)
                        @php
                            $hasUnreviewedItems = $order->status === 'delivered' && $order->items->contains(fn($item) => !$item->review);
                            $latestUpdate = $order->updates->first();
                            $canCancel = $order->status === 'pending' || $order->status === 'processing';
                        @endphp
                        <div class="order-card" onclick="openOrderModal({{ $order->id }})" style="border:1px solid #e5e7eb;padding:16px;border-radius:14px;background:#fff;cursor:pointer;transition:box-shadow 0.15s;border-left:4px solid {{ $hasUnreviewedItems ? '#dc2626' : ($order->status === 'shipped' ? '#2563eb' : ($canCancel ? '#f97316' : '#d1d5db')) }};" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow=''">
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

            <div class="button-container" style="margin-top:8px;display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;overflow-x:auto;">
                @if($hasUnreviewedItems)
                    <span class="pending-review" style="font-size:0.65rem;color:#dc2626;white-space:nowrap;flex-shrink:0;">Items pending review</span>
                @elseif($order->status === 'shipped')
                    <span class="order-action-btn" style="font-size:0.65rem;color:#2563eb;white-space:nowrap;flex-shrink:0;padding:1px 5px;">Confirm Receipt</span>
                @endif
                @if($canCancel)
                    <button onclick="event.stopPropagation();showCancelModal({{ $order->id }})" class="btn order-action-btn" style="background:#dc2626;padding:3px 8px;font-size:0.65rem;white-space:nowrap;">Cancel</button>
                @endif
                <button onclick="event.stopPropagation();openOrderModal({{ $order->id }})" class="btn order-action-btn" style="padding:3px 8px;font-size:0.65rem;white-space:nowrap;">Track</button>
                @if($order->status === 'delivered' && $order->delivered_at && !$order->delivered_at->addDays(7)->isPast())
                    <a href="{{ route('orders.return.create', $order) }}" class="btn order-action-btn" style="background:#f97316;padding:3px 8px;font-size:0.65rem;white-space:nowrap;">Return</a>
                @endif
                <a href="{{ route('orders.show', $order) }}" onclick="event.stopPropagation();" class="btn order-action-btn" style="background:#2563eb;padding:3px 8px;font-size:0.65rem;white-space:nowrap;text-decoration:none;color:#fff !important;">View Details</a>
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
                        <div class="order-card" onclick="openOrderModal({{ $order->id }})" style="border:1px solid #e5e7eb;padding:16px;border-radius:14px;background:#f9fafb;cursor:pointer;transition:box-shadow 0.15s;opacity:0.85;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';this.style.opacity='1'" onmouseout="this.style.boxShadow='';this.style.opacity='0.85'">
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

                            <div style="margin-top:8px;display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;overflow-x:auto;">
                                <span style="font-size:0.65rem;color:#6b7280;white-space:nowrap;flex-shrink:0;">✓ All items reviewed</span>
                                <a href="{{ route('orders.show', $order) }}" onclick="event.stopPropagation();" class="order-action-btn" style="font-size:0.65rem;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#fff !important;background:#2563eb;padding:1px 5px;">View Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($cancelledOrders->isNotEmpty())
                <h2 style="margin-bottom:16px;color:#dc2626;">Cancelled Orders</h2>
                <div style="display:grid;gap:16px;">
                    @foreach($cancelledOrders as $order)
                        @php
                            $latestUpdate = $order->updates->first();
                        @endphp
                        <div class="order-card" onclick="openOrderModal({{ $order->id }})" style="border:1px solid #e5e7eb;padding:16px;border-radius:14px;background:#fef2f2;cursor:pointer;transition:box-shadow 0.15s;opacity:0.85;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';this.style.opacity='1'" onmouseout="this.style.boxShadow='';this.style.opacity='0.85'">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                                <div>
                                    <strong style="color:#dc2626;">{{ $order->order_number }}</strong>
                                    <p class="text-muted" style="margin:2px 0 0;">{{ $order->placed_at->format('F j, Y') }}</p>
                                    @if($order->cancelled_at)
                                        <p style="margin:2px 0 0;font-size:0.65rem;color:#991b1b;">Cancelled on {{ $order->cancelled_at->format('M d, Y') }}</p>
                                    @endif
                                </div>
                                <div style="text-align:right;">
                                    <p style="margin:0;">
                                        <span style="color:#dc2626;font-weight:600;">Cancelled</span>
                                    </p>
                                    <p style="margin:2px 0 0;font-weight:700;color:#991b1b;">UGX{{ number_format($order->total, 2) }}</p>
                                </div>
                            </div>

                            @if($order->cancellation_reason)
                                <div style="margin-top:8px;padding:8px 12px;background:#fee2e2;border-radius:8px;">
                                    <p style="margin:0;font-size:0.7rem;color:#991b1b;"><strong>Reason:</strong> {{ $order->cancellation_reason }}</p>
                                </div>
                            @endif

                            <div style="margin-top:8px;display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;overflow-x:auto;">
                                <a href="{{ route('orders.show', $order) }}" onclick="event.stopPropagation();" class="order-action-btn" style="font-size:0.65rem;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#fff !important;background:#6b7280;padding:1px 5px;">View Details</a>
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

    <!-- Cancel Order Modal -->
    <div id="cancelModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1001;align-items:center;justify-content:center;overflow-y:auto;padding:20px;">
        <div class="card" style="max-width:500px;width:100%;background:#fff;margin:auto;">
            <div id="cancelModalContent">
                <h2 style="margin:0 0 16px;">Cancel Order</h2>
                <p style="margin:0 0 20px;color:#6b7280;">Are you sure you want to cancel this order?</p>
                <form id="cancelForm" method="POST" style="display:grid;gap:16px;">
                    @csrf
                    <input type="hidden" name="order_id" id="cancelOrderId">
                    <div class="form-group">
                        <label class="form-label">Reason for cancellation</label>
                        <textarea class="textarea" name="reason" rows="3" placeholder="Please provide a reason for cancelling this order..." required></textarea>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <button type="button" onclick="hideCancelModal()" class="btn btn-secondary">Go Back</button>
                        <button type="submit" class="btn" style="background:#dc2626;">Confirm Cancel</button>
                    </div>
                </form>
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
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary" style="padding:8px 16px;font-size:0.9rem;">Full Details</a>
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

        // Cancel Modal Functions
        function showCancelModal(orderId) {
            const modal = document.getElementById('cancelModal');
            const orderIdInput = document.getElementById('cancelOrderId');
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
            orderIdInput.value = orderId;
            modal.style.display = 'flex';
        }

        function hideCancelModal() {
            const modal = document.getElementById('cancelModal');
            modal.style.display = 'none';
        }

        // Close cancel modal when clicking outside
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) hideCancelModal();
        });

        // Handle cancel form submission
        document.getElementById('cancelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('{{ route('orders.cancel') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name="_token"]')?.value,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideCancelModal();
                    location.reload();
                } else {
                    alert(data.message || 'Failed to cancel order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the order');
            });
        });
    </script>
@endsection