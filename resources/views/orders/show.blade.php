@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Order {{ $order->order_number }}</h1>
        </div>
    </div>
    <div class="card">
        <p class="text-muted">
            Status: 
            @if($order->status === 'delivered')
                <span style="color:#059669;font-weight:600;">Delivered</span>
            @elseif($order->status === 'shipped')
                <span style="color:#2563eb;font-weight:600;">Shipped</span>
            @else
                <span>{{ ucfirst($order->status) }}</span>
            @endif
        </p>

        <div style="display:grid;gap:12px;margin-top:16px;">
            <!-- Shipping Details -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Shipping Details</h2>
                <p><strong>Name:</strong> {{ $order->shipping_name ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $order->shipping_phone ?? 'N/A' }}</p>
                <p><strong>Delivery Area:</strong> {{ $order->delivery_area ?? 'N/A' }}</p>
                @if($order->address)
                    <p><strong>Address:</strong> {{ $order->address->line1 ?? '' }}, {{ $order->address->city ?? '' }}</p>
                @endif
            </div>

            <!-- Tracking Updates -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Tracking</h2>
                @if($order->updates->isEmpty())
                    <p class="text-muted">No tracking updates yet. Check again later.</p>
                @else
                    <div style="display:grid;gap:12px;">
                        @foreach($order->updates as $update)
                            <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                                <strong>
                                    @if($update->status === 'shipped')
                                        <span style="color:#2563eb;">Shipped</span>
                                    @elseif($update->status === 'delivered')
                                        <span style="color:#059669;">Delivered</span>
                                    @else
                                        {{ ucfirst($update->status) }}
                                    @endif
                                </strong>
                                <p class="text-muted" style="margin:4px 0 0;">{{ $update->created_at->format('F j, Y H:i') }}</p>
                                @if($update->note)
                                    <p style="margin:8px 0 0;">{{ $update->note }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Items -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Items</h2>
                @if($order->status === 'delivered' && $order->items->contains(fn($item) => !$item->review))
                    <form method="POST" action="{{ route('orders.bulk-review', $order) }}" style="display:grid;gap:16px;">
                        @csrf
                        @foreach($order->items as $item)
                            <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                                <div style="min-width:220px;flex:1;">
                                    <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                                    @if($item->color || $item->size)
                                        @php
                                            $colorParts = explode(':', $item->color ?? '');
                                            $colorDisplayName = $colorParts[1] ?? $item->color ?? '';
                                        @endphp
                                        <p style="font-size:0.95rem;color:#6b7280;margin-top:2px;">
                                            @if($colorDisplayName)<span>Color: {{ $colorDisplayName }}</span>@endif
                                            @if($item->size)<span> | Size: {{ $item->size }}</span>@endif
                                        </p>
                                    @endif
                                    @if($item->review)
                                        <div style="margin-top:8px;padding:12px;background:#f3f4f6;border-radius:12px;">
                                            <p style="margin:0 0 4px;font-weight:700;">Your review</p>
                                            <p style="margin:0;font-size:1.05rem;">Rating: {{ $item->review->rating }}/5</p>
                                            @if($item->review->comment)
                                                <p style="margin:6px 0 0;font-size:1.05rem;color:#374151;">"{{ $item->review->comment }}"</p>
                                            @endif
                                        </div>
                                    @else
                                        <div style="margin-top:12px;display:grid;gap:10px;">
                                            <label style="font-weight:700;">Leave a review (optional)</label>
                                            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                                                <select class="input" name="reviews[{{ $item->id }}][rating]" style="max-width:120px;">
                                                    <option value="">Rating</option>
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                                <textarea class="input" name="reviews[{{ $item->id }}][comment]" rows="2" placeholder="Share how the product felt..." style="flex:1;"></textarea>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                                <strong>UGX{{ number_format($item->total_price, 2) }}</strong>
                            </div>
                        @endforeach
                        <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                            <button class="btn" type="submit" style="padding:12px 24px;font-size:1.1rem;">Submit All Reviews</button>
                        </div>
                    </form>
                @else
                    @foreach($order->items as $item)
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                            <div style="min-width:220px;flex:1;">
                                <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                                @if($item->color || $item->size)
                                    @php
                                        $colorParts = explode(':', $item->color ?? '');
                                        $colorDisplayName = $colorParts[1] ?? $item->color ?? '';
                                    @endphp
                                    <p style="font-size:0.95rem;color:#6b7280;margin-top:2px;">
                                        @if($colorDisplayName)<span>Color: {{ $colorDisplayName }}</span>@endif
                                        @if($item->size)<span> | Size: {{ $item->size }}</span>@endif
                                    </p>
                                @endif
                                @if($item->review)
                                    <div style="margin-top:8px;padding:12px;background:#f3f4f6;border-radius:12px;">
                                        <p style="margin:0 0 4px;font-weight:700;">Your review</p>
                                        <p style="margin:0;font-size:1.05rem;">Rating: {{ $item->review->rating }}/5</p>
                                        @if($item->review->comment)
                                            <p style="margin:6px 0 0;font-size:1.05rem;color:#374151;">"{{ $item->review->comment }}"</p>
                                        @endif
                                    </div>
                                @endif

                            </div>
                            <strong>UGX{{ number_format($item->total_price, 2) }}</strong>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Totals -->
            <div class="totals-container" style="display:flex;justify-content:space-between;align-items:center;padding:16px;background:#f9fafb;border-radius:14px;flex-wrap:wrap;gap:12px;">
                <div>
                    <p class="text-muted">Subtotal</p>
                    <p class="text-muted">Shipping</p>
                    <p class="text-muted">Total</p>
                </div>
                <div style="text-align:right;">
                    <p>UGX{{ number_format($order->subtotal, 2) }}</p>
                    <p>UGX{{ number_format($order->shipping, 2) }}</p>
                    <p style="font-weight:700;">UGX{{ number_format($order->total, 2) }}</p>
                </div>
            </div>

            @if($order->status === 'shipped')
                <div style="display:flex;justify-content:flex-end;">
                    <form method="POST" action="{{ route('orders.confirm-received', $order) }}" onsubmit="return confirm('Have you received all items in this order?');">
                        @csrf
                        <button class="btn" style="background:#059669;padding:12px 24px;font-size:1.1rem;">I Have Received My Items</button>
                    </form>
                </div>
            @endif

            @if($order->status === 'delivered')
                @if($order->delivered_at && !$order->delivered_at->addDays(7)->isPast())
                    <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary" style="padding:12px 24px;font-size:1.1rem;">View My Returns</a>
                        <a href="{{ route('orders.return.create', $order) }}" class="btn" style="background:#f97316;padding:12px 24px;font-size:1.1rem;">Request Return</a>
                    </div>
                @else
                    <div style="padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;margin-top:12px;">
                        <p style="margin:0;color:#991b1b;font-size:1rem;">
                            ⏰ The <strong>7-day return period</strong> for this order has expired.
                        </p>
                    </div>
                @endif
            @endif

            <!-- Return Requests -->
            @if($order->returns->isNotEmpty())
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Return Requests</h2>
                    <div style="display:grid;gap:12px;">
                        @foreach($order->returns as $return)
                            <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                    <strong>{{ $return->return_number }}</strong>
                                    <span>
                                        @if($return->status === 'pending')
                                            <span class="badge badge-amber">Pending</span>
                                        @elseif($return->status === 'approved')
                                            <span class="badge badge-blue">Approved</span>
                                        @elseif($return->status === 'rejected')
                                            <span class="badge badge-red">Rejected</span>
                                        @elseif($return->status === 'refunded')
                                            <span class="badge badge-green">Refunded</span>
                                        @endif
                                    </span>
                                </div>
                                <p style="margin:4px 0 0;font-size:0.95rem;color:#6b7280;">
                                    Reason: {{ $return->reason }} • {{ $return->created_at->format('M d, Y') }}
                                </p>
                                @if($return->statusUpdates->isNotEmpty())
                                    <div style="margin-top:8px;padding-left:12px;border-left:2px solid #e5e7eb;">
                                        @foreach($return->statusUpdates as $update)
                                            <div style="font-size:0.9rem;color:#6b7280;margin-bottom:4px;">
                                                <span style="font-weight:600;">{{ ucfirst($update->status) }}</span> — {{ $update->note }}
                                                <span style="color:#9ca3af;font-size:0.85rem;">{{ $update->created_at->format('M d, H:i') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    <style>
        @media (max-width: 768px) {
            .totals-container {
                display: grid !important;
                grid-template-columns: 1fr auto !important;
                gap: 8px 16px !important;
            }
            .totals-container > div:first-child p,
            .totals-container > div:last-child p {
                margin: 0 !important;
                min-height: 1.5rem !important;
            }
        }
    </style>
@endsection
