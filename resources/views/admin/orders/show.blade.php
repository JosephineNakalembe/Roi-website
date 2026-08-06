@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.orders.index')])
            <h1 class="mb-0">Order {{ $order->order_number }}</h1>
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
@endsection