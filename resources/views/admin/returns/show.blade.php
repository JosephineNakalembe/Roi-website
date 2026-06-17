@extends('layouts.app')

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <div>
                <h1>Return {{ $orderReturn->return_number }}</h1>
                <p class="text-muted">
                    Order {{ $orderReturn->order->order_number }} • 
                    @if($orderReturn->status === 'pending')
                        <span class="badge badge-amber">Pending</span>
                    @elseif($orderReturn->status === 'approved')
                        <span class="badge badge-blue">Approved</span>
                    @elseif($orderReturn->status === 'rejected')
                        <span class="badge badge-red">Rejected</span>
                    @elseif($orderReturn->status === 'refunded')
                        <span class="badge badge-green">Refunded</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.returns.index') }}" class="btn btn-secondary">Back to Returns</a>
        </div>

        <div style="display:grid;gap:18px;">
            <!-- Customer Info -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Customer</h2>
                <p><strong>Name:</strong> {{ $orderReturn->user->name }}</p>
                <p><strong>Email:</strong> {{ $orderReturn->user->email }}</p>
            </div>

            <!-- Reason & Notes -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Return Details</h2>
                <p><strong>Reason:</strong> {{ $orderReturn->reason }}</p>
                <p><strong>Buyer's Explanation:</strong></p>
                <div style="padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;">
                    {{ $orderReturn->notes }}
                </div>
            </div>

            <!-- Items Being Returned -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Items Being Returned</h2>
                <div style="display:grid;gap:8px;">
                    @foreach($orderReturn->items as $returnItem)
                        @php $item = $returnItem->orderItem; @endphp
                        <div style="padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;">
                            <strong>{{ $item->product_name }}</strong>
                            <p style="margin:2px 0 0;font-size:0.85rem;color:#6b7280;">
                                Quantity: {{ $item->quantity }} • UGX{{ number_format($item->total_price, 2) }}
                                @if($item->color || $item->size)
                                    • {{ $item->color ? "Color: {$item->color}" : '' }}{{ $item->size ? " Size: {$item->size}" : '' }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Images -->
            @if($orderReturn->images)
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Attached Images</h2>
                    <div style="display:grid;gap:8px;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
                        @foreach(explode(',', $orderReturn->images) as $image)
                            <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                                <img src="{{ asset('storage/' . $image) }}" style="width:100%;height:150px;object-fit:cover;display:block;">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Refund Details -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Refund Details</h2>
                <p><strong>Network:</strong> {{ $orderReturn->refund_network }}</p>
                <p><strong>Name:</strong> {{ $orderReturn->refund_name }}</p>
                <p><strong>Number:</strong> {{ $orderReturn->refund_number }}</p>
            </div>

            <!-- Pickup Details -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>Pickup Details</h2>
                <p><strong>Area:</strong> {{ $orderReturn->pickup_area }}</p>
                <p><strong>Address:</strong> {{ $orderReturn->pickup_address }}</p>
                <p><strong>Contact:</strong> {{ $orderReturn->pickup_contact }}</p>
                <p><strong>Pickup Fee:</strong> UGX{{ number_format($orderReturn->pickup_fee, 0) }} (buyer pays)</p>
            </div>

            <!-- Status Updates Timeline -->
            @if($orderReturn->statusUpdates->isNotEmpty())
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Status Timeline</h2>
                    <div style="position:relative;padding-left:24px;margin-top:12px;">
                        @foreach($orderReturn->statusUpdates as $i => $update)
                            @php
                                $isFirst = $i === 0;
                                $isLast = $i === count($orderReturn->statusUpdates) - 1;
                                $color = match($update->status) {
                                    'refunded' => '#059669',
                                    'approved' => '#2563eb',
                                    'rejected' => '#dc2626',
                                    default => '#d1d5db'
                                };
                            @endphp
                            <div style="position:relative;padding-bottom:{{ $isLast ? '0' : '20px'}};padding-left:16px;border-left:{{ $isLast ? '2px solid transparent' : '2px solid #e5e7eb'}};">
                                <div style="position:absolute;left:-8px;top:4px;width:14px;height:14px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $color }};"></div>
                                <div style="background:{{ $isFirst ? '#f0fdf4' : '#f9fafb'}};padding:12px;border-radius:12px;border:1px solid {{ $isFirst ? '#bbf7d0' : '#e5e7eb'}};">
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                        <strong style="color:{{ $color }};">
                                            {{ ucfirst($update->status) }}
                                        </strong>
                                        <span style="font-size:0.8rem;color:#9ca3af;">{{ $update->created_at->format('M d, H:i') }}</span>
                                    </div>
                                    @if($update->note)
                                        <p style="margin:6px 0 0;font-size:0.9rem;color:#374151;">{{ $update->note }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Admin Notes -->
            @if($orderReturn->admin_notes)
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Admin Notes</h2>
                    <div style="padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;white-space:pre-line;">
                        {{ $orderReturn->admin_notes }}
                    </div>
                </div>
            @endif

            <!-- Admin Action Form -->
            @if($orderReturn->status !== 'rejected' && $orderReturn->status !== 'refunded')
                <div style="padding:16px;background:#f9fafb;border-radius:14px;border:2px solid #2563eb;">
                    <h2>Update Return Status</h2>
                    <form method="POST" action="{{ route('admin.returns.update', $orderReturn) }}" style="display:grid;gap:12px;">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label>New Status</label>
                            <select class="input" name="status" required>
                                <option value="">Select status...</option>
                                @if($orderReturn->status === 'pending')
                                    <option value="approved">Approve - Inspect items and process</option>
                                    <option value="rejected">Reject</option>
                                @elseif($orderReturn->status === 'approved')
                                    <option value="refunded">Refunded - Money sent to buyer</option>
                                    <option value="rejected">Reject</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label>Admin Notes (optional)</label>
                            <textarea class="input" name="admin_notes" rows="3" placeholder="Add notes about this return..."></textarea>
                        </div>
                        <button class="btn" type="submit">Update Status</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection