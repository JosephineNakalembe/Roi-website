@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content header-content-between">
            <div class="header-title-row">
                @include('partials.back-button', ['fallback' => route('admin.orders.index')])
                <h1 class="mb-0">Order {{ $order->order_number }}</h1>
            </div>
            <button type="button" class="btn" style="background:#dc2626;color:#fff;" onclick="document.getElementById('deleteOrderModal').style.display='flex';">
                Delete Order
            </button>
        </div>
    </div>
    <div class="card">
        <div style="display:grid;gap:18px;">
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <strong>Customer</strong>
                <p>@if($order->user){{ $order->user->name }} • {{ $order->user->email }}@else<span style="color:#9ca3af;">Deleted account</span>@endif</p>
            </div>
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <strong>Shipping Details</strong>
                <p><strong>Name:</strong> {{ $order->shipping_name ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $order->shipping_phone ?? 'N/A' }}</p>
                <p><strong>Delivery Area:</strong> {{ $order->delivery_area ?? 'N/A' }}</p>
                @if($order->address)
                    <p><strong>Address:</strong> {{ $order->address->line1 ?? '' }}, {{ $order->address->city ?? '' }}</p>
                @endif
            </div>
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <strong>Payment</strong>
                <p>Cash on Delivery (COD)</p>
            </div>

            <!-- Order Update Form + Timeline -->
            <div style="display:grid;gap:12px;padding:16px;border:1px solid #e5e7eb;border-radius:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                    <div>
                        <strong>Order total</strong>
                        <p>UGX{{ number_format($order->total, 2) }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" style="display:grid;gap:12px;min-width:220px;">
                        @csrf
                        @method('PATCH')
                        <select class="input" name="status" style="min-width:160px;">
                            <option value="pending"{{ $order->status === 'pending' ? ' selected' : '' }}>Pending</option>
                            <option value="shipped"{{ $order->status === 'shipped' ? ' selected' : '' }}>Shipped</option>
                            <option value="delivered"{{ $order->status === 'delivered' ? ' selected' : '' }}>Delivered</option>
                        </select>
                        <textarea class="input" name="note" rows="3" placeholder="Add delivery details or tracking notes..."></textarea>
                        <button class="btn" type="submit">Save update</button>
                    </form>
                </div>

                <!-- Timeline Updates -->
                @if($order->updates->isNotEmpty())
                    <div style="padding-top:16px;border-top:1px solid #e5e7eb;">
                        <h3>Tracking Timeline</h3>
                        <div style="margin-top:16px;">
                            @include('orders.partials.tracking-timeline', ['order' => $order, 'showActor' => true])
                        </div>
                    </div>
                @else
                    <div style="padding-top:16px;border-top:1px solid #e5e7eb;">
                        <h3>Tracking Timeline</h3>
                        <div style="margin-top:16px;">
                            @include('orders.partials.tracking-timeline', ['order' => $order, 'showActor' => true])
                        </div>
                        <p style="color:#9ca3af;margin-top:12px;">No status updates yet. Add one above.</p>
                    </div>
                @endif
            </div>

            <div>
                <h2>Items</h2>
                @foreach($order->items as $item)
                    @php
                        $colorParts = explode(':', $item->color ?? '');
                        $colorDisplayName = $colorParts[1] ?? $item->color ?? '';
                        $imgUrl = $item->product && $item->product->primaryImage
                            ? media_url($item->product->primaryImage->path)
                            : 'https://via.placeholder.com/80x80?text=No+Image';
                        $productUrl = $item->product ? route('shop.show', $item->product->slug) : null;
                    @endphp
                    <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #e5e7eb;{{ $item->cancelled_at ? 'opacity:0.6;' : '' }}">
                        <div style="flex-shrink:0;width:60px;height:60px;border-radius:6px;overflow:hidden;background:#f3f4f6;">
                            @if($productUrl)
                                <a href="{{ $productUrl }}" target="_blank">
                                    <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" style="width:100%;height:100%;object-fit:cover;">
                                </a>
                            @else
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#e5e7eb;color:#9ca3af;font-size:0.65rem;text-align:center;padding:2px;">
                                    No Image
                                </div>
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            @if($productUrl)
                                <a href="{{ $productUrl }}" target="_blank" style="font-weight:600;text-decoration:none;color:inherit;">
                                    {{ $item->product_name }} × {{ $item->quantity }}
                                </a>
                            @else
                                <span style="font-weight:600;">{{ $item->product_name }} × {{ $item->quantity }}</span>
                                <p style="margin:2px 0 0;font-size:0.85rem;color:#6b7280;font-weight:600;">Item no longer sold</p>
                            @endif
                            @if($colorDisplayName || $item->size)
                                <p style="font-size:0.9rem;color:#6b7280;margin:2px 0 0;">
                                    @if($colorDisplayName)<span>Color: {{ $colorDisplayName }}</span>@endif
                                    @if($item->size)<span> | Size: {{ $item->size }}</span>@endif
                                </p>
                            @endif
                            @if($item->cancelled_at)
                                <p style="margin:2px 0 0;font-size:0.85rem;color:#1a1a2e;font-weight:600;">
                                    Cancelled — {{ $item->cancellation_reason }}
                                </p>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <strong>UGX{{ number_format($item->total_price, 2) }}</strong>
                            @if(!$item->cancelled_at)
                                <form method="POST" action="{{ route('admin.orders.items.cancel', [$order, $item]) }}" onsubmit="return confirm('Cancel this item as Out of Stock?');" style="margin-top:4px;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="padding:2px 8px;font-size:0.85rem;background:#6b7280;color:#fff;border:none;border-radius:6px;cursor:pointer;">Cancel Item</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Delete Order Confirmation Modal -->
    <div id="deleteOrderModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(26,26,46,0.4);backdrop-filter:blur(4px);align-items:center;justify-content:center;z-index:1000;">
        <div style="background:#fff;border-radius:14px;padding:24px;max-width:400px;width:90%;box-shadow:0 10px 40px rgba(26,26,46,0.15);border:1px solid #e8e4df;margin:auto;">
            <h3 style="margin:0 0 8px;font-size:1.2rem;color:#1a1a2e;">Delete Order {{ $order->order_number }}</h3>
            <p style="margin:0 0 20px;font-size:1rem;color:#6c757d;line-height:1.5;">
                Are you sure you want to permanently delete this order from the records? Its sales will also be removed from the reports. This action cannot be undone.
            </p>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteOrderModal').style.display='none';">Cancel</button>
                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" style="margin:0;" onsubmit="return confirm('Confirm: permanently delete this order and remove its sales from reports?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="background:#dc2626;color:#fff;">Delete Order</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('deleteOrderModal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    </script>
@endsection