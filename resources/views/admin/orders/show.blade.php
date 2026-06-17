@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Order {{ $order->order_number }}</h1>
        <div style="display:grid;gap:18px;">
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <strong>Customer</strong>
                <p>{{ $order->user->name }} • {{ $order->user->email }}</p>
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
                                            <span style="font-size:0.8rem;color:#9ca3af;">{{ $update->created_at->format('M d, H:i') }}</span>
                                        </div>
                                        @if($update->note)
                                            <p style="margin:6px 0 0;font-size:0.9rem;color:#374151;">{{ $update->note }}</p>
                                        @endif
                                        <p style="margin:4px 0 0;font-size:0.75rem;color:#9ca3af;">by {{ $update->status === 'delivered' && str_contains($update->note ?? '', 'Buyer confirmed') ? 'Buyer' : 'Admin' }}</p>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Initial Order Placed -->
                            <div style="position:relative;padding-left:16px;">
                                <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:#6b7280;border:2px solid #fff;box-shadow:0 0 0 2px #6b7280;"></div>
                                <div style="background:#f9fafb;padding:12px;border-radius:12px;border:1px solid #e5e7eb;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                        <strong style="color:#6b7280;">📦 Order Placed</strong>
                                        <span style="font-size:0.8rem;color:#9ca3af;">{{ $order->created_at->format('M d, H:i') }}</span>
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
                                        <span style="font-size:0.8rem;color:#9ca3af;">{{ $order->created_at->format('M d, H:i') }}</span>
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
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #e5e7eb;">
                        <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                        <strong>UGX{{ number_format($item->total_price, 2) }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection