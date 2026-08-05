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
                        <div style="position:relative;padding-left:24px;margin-top:16px;">
                            @foreach($order->updates as $i => $update)
                                @php
                                    $isFirst = $i === 0;
                                    $isLast = $i === count($order->updates) - 1;
                                    $upStatusColor = $update->status === 'delivered' ? '#059669' : ($update->status === 'shipped' ? '#2563eb' : '#d1d5db');
                                @endphp
                                <div style="position:relative;padding-bottom:{{ $isLast ? '0' : '20px'}};padding-left:16px;border-left:{{ $isLast ? '2px solid transparent' : '2px solid #e5e7eb'}};">
                                    <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:{{ $upStatusColor }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $upStatusColor }};"></div>
                                    <div style="background:{{ $isFirst ? '#f0fdf4' : '#f9fafb'}};padding:12px;border-radius:12px;border:1px solid {{ $isFirst ? '#bbf7d0' : '#e5e7eb'}};">
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                            <strong style="color:{{ $upStatusColor }};">
                                                {{ $update->status === 'shipped' ? '🚚 Shipped' : ($update->status === 'delivered' ? '✅ Delivered' : ucfirst($update->status)) }}
                                            </strong>
                                            <span style="font-size:0.9rem;color:#9ca3af;">{{ $update->created_at->format('M d, H:i') }}</span>
                                        </div>
                                        @if($update->note)
                                            <p style="margin:6px 0 0;font-size:1rem;color:#374151;">{{ $update->note }}</p>
                                        @endif
                                        <p style="margin:4px 0 0;font-size:0.85rem;color:#9ca3af;">by {{ $update->status === 'delivered' && str_contains($update->note ?? '', 'Buyer confirmed') ? 'Buyer' : 'Admin' }}</p>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Initial Order Placed -->
                            <div style="position:relative;padding-left:16px;">
                                <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:#6b7280;border:2px solid #fff;box-shadow:0 0 0 2px #6b7280;"></div>
                                <div style="background:#f9fafb;padding:12px;border-radius:12px;border:1px solid #e5e7eb;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                        <strong style="color:#6b7280;">📦 Order Placed</strong>
                                        <span style="font-size:0.9rem;color:#9ca3af;">{{ $order->created_at->format('M d, H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="padding-top:16px;border-top:1px solid #e5e7eb;">
                        <h3>Tracking Timeline</h3>
                        <div style="position:relative;padding-left:24px;margin-top:16px;">
                            <div style="position:relative;padding-left:16px;">
                                <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:#6b7280;border:2px solid #fff;box-shadow:0 0 0 2px #6b7280;"></div>
                                <div style="background:#f9fafb;padding:12px;border-radius:12px;border:1px solid #e5e7eb;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                        <strong style="color:#6b7280;">📦 Order Placed</strong>
                                        <span style="font-size:0.9rem;color:#9ca3af;">{{ $order->created_at->format('M d, H:i') }}</span>
                                    </div>
                                </div>
                            </div>
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
                                <p style="margin:2px 0 0;font-size:0.85rem;color:#dc2626;font-weight:600;">Item no longer sold</p>
                            @endif
                            @if($colorDisplayName || $item->size)
                                <p style="font-size:0.9rem;color:#6b7280;margin:2px 0 0;">
                                    @if($colorDisplayName)<span>Color: {{ $colorDisplayName }}</span>@endif
                                    @if($item->size)<span> | Size: {{ $item->size }}</span>@endif
                                </p>
                            @endif
                            @if($item->cancelled_at)
                                <p style="margin:2px 0 0;font-size:0.85rem;color:#dc2626;font-weight:600;">
                                    Cancelled — {{ $item->cancellation_reason }}
                                </p>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <strong>UGX{{ number_format($item->total_price, 2) }}</strong>
                            @if(!$item->cancelled_at)
                                <form method="POST" action="{{ route('admin.orders.items.cancel', [$order, $item]) }}" onsubmit="return confirm('Cancel this item as Out of Stock?');" style="margin-top:4px;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="padding:2px 8px;font-size:0.85rem;background:#dc2626;color:#fff;border:none;border-radius:6px;cursor:pointer;">Cancel Item</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection